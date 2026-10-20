<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\AnalisisOrganisasi\AnalisisOrganisasiForm;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\RefAreaDampakOrg;
use App\Models\RefUkuranKompleksitas;
use Database\Seeders\RefAreaDampakOrgSeeder;
use Database\Seeders\RefUkuranKompleksitasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 4: F2B Analisis Dampak Organisasi.
 */
class AnalisisOrganisasiFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RefAreaDampakOrgSeeder::class,
            RefUkuranKompleksitasSeeder::class,
        ]);
    }

    private function perubahanOrganisasi(MprKonteks $konteks, $operator): MprPerubahan
    {
        return MprPerubahan::factory()->lingkupOrganisasi()->create([
            'mpr_konteks_id' => $konteks->id,
            'created_by'     => $operator->id,
        ]);
    }

    private function komponen($user, MprKonteks $konteks, MprPerubahan $perubahan)
    {
        return Livewire::actingAs($user)->test(AnalisisOrganisasiForm::class, [
            'konteks'   => $konteks,
            'perubahan' => $perubahan,
        ]);
    }

    public function test_admin_tidak_bisa_menyimpan_403(): void
    {
        $data = $this->siapkanDataMpr();
        $perubahan = $this->perubahanOrganisasi($data->konteks, $data->operator);

        $this->komponen($data->admin, $data->konteks, $perubahan)
            ->call('save')
            ->assertForbidden();

        $this->assertNull($perubahan->fresh()->analisisOrganisasi);
    }

    public function test_admin_melihat_form_read_only(): void
    {
        $data = $this->siapkanDataMpr();
        $perubahan = $this->perubahanOrganisasi($data->konteks, $data->operator);

        $this->actingAs($data->admin)
            ->get(route('mpr.analisis-organisasi.form', [$data->konteks, $perubahan]))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Simpan Analisis Organisasi');
    }

    public function test_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $perubahan = $this->perubahanOrganisasi($data->konteks, $data->operator);
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.analisis-organisasi.form', [$data->konteks, $perubahan]))
            ->assertForbidden();
    }

    public function test_perubahan_milik_konteks_lain_404(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();
        $konteksLain = MprKonteks::create([
            'layanan_id'      => $lain->layanan->id,
            'nama_instansi'   => 'Lain',
            'tahun_penilaian' => 2026,
            'created_by'      => $lain->operator->id,
        ]);
        $asing = $this->perubahanOrganisasi($konteksLain, $lain->operator);

        $this->actingAs($data->operator)
            ->get(route('mpr.analisis-organisasi.form', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_halaman_tidak_wajib_ditampilkan_disabled_dan_menolak_simpan(): void
    {
        $data = $this->siapkanDataMpr(); // Default: Normal + Teknis -> 2B tidak wajib

        $this->actingAs($data->operator)
            ->get(route('mpr.analisis-organisasi.form', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('tidak wajib')
            ->assertDontSee('Simpan Analisis Organisasi');

        $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->assertViewHas('wajib', false)
            ->assertViewHas('isEditable', false)
            ->call('save')
            ->assertForbidden();
    }

    public function test_simpan_empat_area_indikator_dan_kesimpulan_otomatis(): void
    {
        $data = $this->siapkanDataMpr();
        $perubahan = $this->perubahanOrganisasi($data->konteks, $data->operator);

        $areas = RefAreaDampakOrg::with('indikator')->orderBy('urutan')->get();
        $rendah = RefUkuranKompleksitas::where('nama', 'Rendah')->firstOrFail();
        $sedang = RefUkuranKompleksitas::where('nama', 'Sedang')->firstOrFail();
        $tinggi = RefUkuranKompleksitas::where('nama', 'Tinggi')->firstOrFail();

        // Kombinasi: Tinggi, Rendah, Sedang, Tinggi -> Kesimpulan Tinggi
        $pilihanUkuran = [$tinggi, $rendah, $sedang, $tinggi];

        $c = $this->komponen($data->operator, $data->konteks, $perubahan);

        foreach ($areas as $idx => $area) {
            $indPertama = $area->indikator->first();
            $c->set("areas.{$area->id}.ref_ukuran_kompleksitas_id", $pilihanUkuran[$idx]->id)
              ->set("areas.{$area->id}.indikator_ids", [$indPertama->id]);
        }

        // Coba suntik indikator milik area ke-2 ke dalam area ke-1 (harus difilter/dibuang)
        $indArea1 = $areas[0]->indikator->first()->id;
        $indArea2 = $areas[1]->indikator->first()->id;
        $c->set("areas.{$areas[0]->id}.indikator_ids", [$indArea1, $indArea2]);

        $c->call('save')
            ->assertHasNoErrors()
            ->assertSet('kesimpulanNama', 'Tinggi')
            ->assertSet('rekomendasi', $tinggi->definisi);

        $analisis = $perubahan->fresh()->analisisOrganisasi;
        $this->assertNotNull($analisis);
        $this->assertSame($tinggi->id, $analisis->ref_ukuran_kompleksitas_id);
        $this->assertSame($tinggi->definisi, $analisis->rekomendasi);
        $this->assertSame(4, $analisis->areas()->count());

        // Pastikan indikator area ke-1 hanya menyimpan indikator miliknya sendiri
        $orgArea1 = $analisis->areas()->where('ref_area_dampak_org_id', $areas[0]->id)->firstOrFail();
        $this->assertSame([$indArea1], $orgArea1->indikator()->pluck('ref_indikator_area_dampak.id')->all());
    }
}
