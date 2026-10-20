<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\Perubahan\PerubahanForm;
use App\Models\MprAnalisisPrioritas;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\RefKlasifikasiPerubahan;
use App\Models\RefLingkupPerubahan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 3: F1 Permohonan Perubahan.
 */
class PerubahanFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    private function ref(): object
    {
        $std = RefKlasifikasiPerubahan::firstOrCreate(['nama' => 'Standard'], ['wajib_analisis_dampak' => false, 'wajib_persetujuan' => false]);
        $nor = RefKlasifikasiPerubahan::firstOrCreate(['nama' => 'Normal'], ['wajib_analisis_dampak' => true, 'wajib_persetujuan' => true]);
        $eme = RefKlasifikasiPerubahan::firstOrCreate(['nama' => 'Emergency'], ['wajib_analisis_dampak' => true, 'wajib_persetujuan' => true]);
        $tek = RefLingkupPerubahan::firstOrCreate(['nama' => 'Teknis'], ['butuh_analisis_teknis' => true, 'butuh_analisis_organisasi' => false]);
        $org = RefLingkupPerubahan::firstOrCreate(['nama' => 'Organisasi'], ['butuh_analisis_teknis' => false, 'butuh_analisis_organisasi' => true]);
        $tno = RefLingkupPerubahan::firstOrCreate(['nama' => 'Teknis & Organisasi'], ['butuh_analisis_teknis' => true, 'butuh_analisis_organisasi' => true]);

        return (object) compact('std', 'nor', 'eme', 'tek', 'org', 'tno');
    }

    private function baru($user, $konteks)
    {
        return Livewire::actingAs($user)->test(PerubahanForm::class, ['konteks' => $konteks]);
    }

    private function edit($user, $konteks, MprPerubahan $perubahan)
    {
        return Livewire::actingAs($user)->test(PerubahanForm::class, ['konteks' => $konteks, 'perubahan' => $perubahan]);
    }

    private function isiValid($komponen, $ref, $klasifikasi = null, $lingkup = null)
    {
        return $komponen
            ->set('nama_layanan_digital', 'Portal Baru')
            ->set('detail_perubahan', 'Migrasi server')
            ->set('unit_pemohon', 'Bidang TI')
            ->set('ref_klasifikasi_perubahan_id', ($klasifikasi ?? $ref->nor)->id)
            ->set('ref_lingkup_perubahan_id', ($lingkup ?? $ref->tek)->id);
    }

    public function test_membuka_form_tidak_membuat_id(): void
    {
        $data = $this->siapkanDataMpr();
        $sebelum = MprPerubahan::withTrashed()->count();

        $this->baru($data->operator, $data->konteks)->assertSet('isNew', true);

        $this->assertSame($sebelum, MprPerubahan::withTrashed()->count());
    }

    public function test_admin_tidak_bisa_menyimpan_403(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();
        $sebelum = MprPerubahan::count();

        $this->isiValid($this->baru($data->admin, $data->konteks), $ref)->call('save')->assertForbidden();

        $this->assertSame($sebelum, MprPerubahan::count());
    }

    public function test_field_wajib_divalidasi(): void
    {
        $data = $this->siapkanDataMpr();

        $this->baru($data->operator, $data->konteks)
            ->call('save')
            ->assertHasErrors([
                'nama_layanan_digital'         => 'required',
                'detail_perubahan'             => 'required',
                'ref_klasifikasi_perubahan_id' => 'required',
                'ref_lingkup_perubahan_id'     => 'required',
            ]);
    }

    public function test_batas_panjang_divalidasi(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();

        $this->isiValid($this->baru($data->operator, $data->konteks), $ref)
            ->set('nama_layanan_digital', str_repeat('a', 256))
            ->set('detail_perubahan', str_repeat('a', 5001))
            ->call('save')
            ->assertHasErrors(['nama_layanan_digital' => 'max', 'detail_perubahan' => 'max']);
    }

    public function test_simpan_pertama_membuat_id_otomatis_dan_hasil_alur(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();

        $c = $this->isiValid($this->baru($data->operator, $data->konteks), $ref)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isNew', false);

        $baru = MprPerubahan::where('nama_layanan_digital', 'Portal Baru')->firstOrFail();

        $this->assertMatchesRegularExpression('/^MPR-[A-Z0-9]+-2026-\d{3}$/', $baru->id_perubahan);
        $this->assertSame($data->konteks->id, $baru->mpr_konteks_id);
        $this->assertSame($data->operator->id, $baru->created_by);
        $this->assertFalse($baru->is_layanan_prioritas);
        $this->assertContains('Analisis Risiko (2A)', $c->get('hasilAlur'));

        // Simpan kedua = update, bukan baris baru.
        $c->set('nama_layanan_digital', 'Portal Diubah')->call('save');
        $this->assertSame(1, MprPerubahan::where('id_perubahan', $baru->id_perubahan)->count());
        $this->assertSame('Portal Diubah', $baru->fresh()->nama_layanan_digital);
    }

    public function test_tautan_prioritas_menandai_layanan_prioritas(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();
        $prioritas = $data->konteks->analisisPrioritas()->create(['kondisi_perlu_berubah' => 'X']);

        $this->isiValid($this->baru($data->operator, $data->konteks), $ref)
            ->set('mpr_analisis_prioritas_id', $prioritas->id)
            ->call('save')
            ->assertHasNoErrors();

        $baru = MprPerubahan::where('nama_layanan_digital', 'Portal Baru')->firstOrFail();
        $this->assertTrue($baru->is_layanan_prioritas);
        $this->assertSame($prioritas->id, $baru->mpr_analisis_prioritas_id);
    }

    public function test_prioritas_milik_konteks_lain_ditolak(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();
        $lain = $this->buatOperatorLainMpr();
        $konteksLain = MprKonteks::create([
            'layanan_id' => $lain->layanan->id, 'nama_instansi' => 'Lain',
            'tahun_penilaian' => 2026, 'created_by' => $lain->operator->id,
        ]);
        $asing = $konteksLain->analisisPrioritas()->create(['kondisi_perlu_berubah' => 'X']);

        $this->isiValid($this->baru($data->operator, $data->konteks), $ref)
            ->set('mpr_analisis_prioritas_id', $asing->id)
            ->call('save')
            ->assertHasErrors(['mpr_analisis_prioritas_id']);
    }

    public function test_resolver_tiga_klasifikasi_kali_tiga_lingkup_di_ui(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();

        foreach (['std' => [false, false], 'nor' => [true, true], 'eme' => [true, true]] as $kk => [$dampak, $persetujuan]) {
            foreach (['tek' => [true, false], 'org' => [false, true], 'tno' => [true, true]] as $lk => [$teknis, $organisasi]) {
                $hasil = $this->isiValid($this->baru($data->operator, $data->konteks), $ref, $ref->$kk, $ref->$lk)
                    ->call('save')
                    ->assertHasNoErrors()
                    ->get('hasilAlur');

                $this->assertSame($dampak && $teknis, in_array('Analisis Risiko (2A)', $hasil), "$kk/$lk risiko");
                $this->assertSame($dampak && $organisasi, in_array('Analisis Organisasi (2B)', $hasil), "$kk/$lk organisasi");
                $this->assertSame($persetujuan, in_array('Persetujuan', $hasil), "$kk/$lk persetujuan");
            }
        }
    }

    public function test_ubah_klasifikasi_tanpa_data_anak_tidak_diblokir(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();

        $this->edit($data->operator, $data->konteks, $data->perubahan)
            ->set('ref_klasifikasi_perubahan_id', $ref->std->id)
            ->call('save')
            ->assertSet('perluKonfirmasi', false);

        $this->assertSame($ref->std->id, $data->perubahan->fresh()->ref_klasifikasi_perubahan_id);
    }

    public function test_ubah_klasifikasi_dengan_data_anak_diblokir_sampai_dikonfirmasi(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->ref();
        $klasifikasiAwal = $data->perubahan->ref_klasifikasi_perubahan_id;

        $risiko = MprAnalisisRisiko::factory()->withLevels(5, 3)->create(['mpr_perubahan_id' => $data->perubahan->id]);

        $c = $this->edit($data->operator, $data->konteks, $data->perubahan->fresh())
            ->set('ref_klasifikasi_perubahan_id', $ref->std->id)
            ->call('save')
            ->assertSet('perluKonfirmasi', true);

        $this->assertContains('Analisis Risiko (2A)', $c->get('formulirTerisi'));
        $this->assertSame($klasifikasiAwal, $data->perubahan->fresh()->ref_klasifikasi_perubahan_id, 'Tidak boleh tersimpan sebelum konfirmasi');

        $c->call('save', true)->assertSet('perluKonfirmasi', false);

        $this->assertSame($ref->std->id, $data->perubahan->fresh()->ref_klasifikasi_perubahan_id);
        $this->assertTrue(MprAnalisisRisiko::whereKey($risiko->id)->exists(), 'Data anak tidak boleh dihapus');
    }

    public function test_edit_tanpa_mengubah_klasifikasi_dengan_data_anak_tidak_diblokir(): void
    {
        $data = $this->siapkanDataMpr();
        MprAnalisisRisiko::factory()->withLevels(5, 3)->create(['mpr_perubahan_id' => $data->perubahan->id]);

        $this->edit($data->operator, $data->konteks, $data->perubahan->fresh())
            ->set('detail_perubahan', 'Detail baru')
            ->call('save')
            ->assertSet('perluKonfirmasi', false);

        $this->assertSame('Detail baru', $data->perubahan->fresh()->detail_perubahan);
    }

    public function test_perubahan_milik_konteks_lain_404(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();
        $konteksLain = MprKonteks::create([
            'layanan_id' => $lain->layanan->id, 'nama_instansi' => 'Lain',
            'tahun_penilaian' => 2026, 'created_by' => $lain->operator->id,
        ]);
        $asing = MprPerubahan::factory()->create(['mpr_konteks_id' => $konteksLain->id, 'created_by' => $lain->operator->id]);

        $this->actingAs($data->operator)
            ->get(route('mpr.perubahan.form', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.perubahan.create', $data->konteks))
            ->assertForbidden();
    }

    public function test_admin_melihat_form_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.perubahan.form', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Simpan Perubahan');
    }
}
