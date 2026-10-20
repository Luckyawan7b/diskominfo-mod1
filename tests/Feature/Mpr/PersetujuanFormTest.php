<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\Persetujuan\PersetujuanForm;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 4: Persetujuan Perubahan per tahap.
 */
class PersetujuanFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    private function komponen($user, MprKonteks $konteks, MprPerubahan $perubahan)
    {
        return Livewire::actingAs($user)->test(PersetujuanForm::class, [
            'konteks'   => $konteks,
            'perubahan' => $perubahan,
        ]);
    }

    public function test_admin_tidak_bisa_menyimpan_403(): void
    {
        $data = $this->siapkanDataMpr();

        $this->komponen($data->admin, $data->konteks, $data->perubahan)
            ->set('items.pelaksanaan.status', 'Disetujui')
            ->call('save')
            ->assertForbidden();

        $this->assertSame(0, $data->perubahan->persetujuan()->count());
    }

    public function test_admin_melihat_form_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.persetujuan.form', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Simpan Persetujuan');
    }

    public function test_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.persetujuan.form', [$data->konteks, $data->perubahan]))
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
        $asing = MprPerubahan::factory()->create([
            'mpr_konteks_id' => $konteksLain->id,
            'created_by'     => $lain->operator->id,
        ]);

        $this->actingAs($data->operator)
            ->get(route('mpr.persetujuan.form', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_tahap_relevan_mengikuti_resolver_klasifikasi_dan_lingkup(): void
    {
        $data = $this->siapkanDataMpr(); // Normal + Teknis

        // Normal + Teknis: analisis_teknis=true, analisis_organisasi=false, pelaksanaan=true
        $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->assertViewHas('tahapRelevan', [
                'analisis_teknis'     => true,
                'analisis_organisasi' => false,
                'pelaksanaan'         => true,
            ]);

        // Normal + Organisasi: analisis_teknis=false, analisis_organisasi=true, pelaksanaan=true
        $pOrg = MprPerubahan::factory()->lingkupOrganisasi()->create([
            'mpr_konteks_id' => $data->konteks->id,
            'created_by'     => $data->operator->id,
        ]);
        $this->komponen($data->operator, $data->konteks, $pOrg)
            ->assertViewHas('tahapRelevan', [
                'analisis_teknis'     => false,
                'analisis_organisasi' => true,
                'pelaksanaan'         => true,
            ]);

        // Standard: ketiganya tidak wajib, simpan ditolak 403
        $pStd = MprPerubahan::factory()->klasifikasiStandard()->create([
            'mpr_konteks_id' => $data->konteks->id,
            'created_by'     => $data->operator->id,
        ]);
        $this->komponen($data->operator, $data->konteks, $pStd)
            ->assertViewHas('wajib', false)
            ->assertViewHas('isEditable', false)
            ->call('save')
            ->assertForbidden();
    }

    public function test_simpan_hanya_menyimpan_tahap_relevan_yang_diisi(): void
    {
        $data = $this->siapkanDataMpr(); // Normal + Teknis (analisis_organisasi tidak relevan)

        $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->set('items.analisis_teknis.status', 'Disetujui')
            ->set('items.analisis_teknis.pic_penyetuju', 'Kabid TI')
            ->set('items.analisis_teknis.tanggal', '2026-05-10')
            ->set('items.analisis_teknis.catatan', 'Lanjut dengan backup penuh')
            // Coba isi tahap yang tidak relevan (harus diabaikan)
            ->set('items.analisis_organisasi.status', 'Disetujui')
            ->set('items.analisis_organisasi.pic_penyetuju', 'Sekretaris')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, $data->perubahan->persetujuan()->count());
        $row = $data->perubahan->persetujuan()->where('tahap', 'analisis_teknis')->firstOrFail();
        $this->assertSame('Disetujui', $row->status);
        $this->assertSame('Kabid TI', $row->pic_penyetuju);
        $this->assertSame('2026-05-10', $row->tanggal->format('Y-m-d'));
        $this->assertSame('Lanjut dengan backup penuh', $row->catatan);
        $this->assertFalse($data->perubahan->persetujuan()->where('tahap', 'analisis_organisasi')->exists());
    }

    public function test_validasi_status_dan_tanggal(): void
    {
        $data = $this->siapkanDataMpr();

        $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->set('items.pelaksanaan.status', 'StatusPalsu')
            ->set('items.pelaksanaan.tanggal', 'bukan-tanggal')
            ->call('save')
            ->assertHasErrors([
                'items.pelaksanaan.status',
                'items.pelaksanaan.tanggal',
            ]);
    }
}
