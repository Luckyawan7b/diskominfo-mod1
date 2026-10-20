<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\AnalisisRisiko\AnalisisRisikoForm;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 4: F2A Analisis Risiko Perubahan.
 */
class AnalisisRisikoFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    private function komponen($user, MprKonteks $konteks, MprPerubahan $perubahan)
    {
        return Livewire::actingAs($user)->test(AnalisisRisikoForm::class, [
            'konteks'   => $konteks,
            'perubahan' => $perubahan,
        ]);
    }

    public function test_admin_tidak_bisa_menulis_403(): void
    {
        $data = $this->siapkanDataMpr(); // Normal + Teknis (wajib 2A)

        $this->komponen($data->admin, $data->konteks, $data->perubahan)
            ->call('addBlock')
            ->assertForbidden();

        $risiko = MprAnalisisRisiko::factory()->withLevels(3, 3)->create([
            'mpr_perubahan_id' => $data->perubahan->id,
        ]);

        $this->komponen($data->admin, $data->konteks, $data->perubahan)
            ->call('saveBlock', 0)
            ->assertForbidden();

        $this->komponen($data->admin, $data->konteks, $data->perubahan)
            ->call('removeBlock', 0)
            ->assertForbidden();

        $this->assertNull($risiko->fresh()->deleted_at);
    }

    public function test_admin_melihat_form_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.analisis-risiko.form', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Tambah Risiko');
    }

    public function test_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.analisis-risiko.form', [$data->konteks, $data->perubahan]))
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
            ->get(route('mpr.analisis-risiko.form', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_halaman_tidak_wajib_ditampilkan_disabled_dan_menolak_simpan(): void
    {
        $data = $this->siapkanDataMpr();
        $perubahanStd = MprPerubahan::factory()->klasifikasiStandard()->create([
            'mpr_konteks_id' => $data->konteks->id,
            'created_by'     => $data->operator->id,
        ]);

        $this->actingAs($data->operator)
            ->get(route('mpr.analisis-risiko.form', [$data->konteks, $perubahanStd]))
            ->assertOk()
            ->assertSee('tidak wajib')
            ->assertDontSee('Tambah Risiko');

        $this->komponen($data->operator, $data->konteks, $perubahanStd)
            ->assertViewHas('wajib', false)
            ->assertViewHas('isEditable', false)
            ->call('addBlock')
            ->assertForbidden();
    }

    public function test_validasi_wajib_dan_batas_level(): void
    {
        $data = $this->siapkanDataMpr();

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('addBlock')
            ->call('saveBlock', 0)
            ->assertHasErrors([
                'blocks.0.kode_risiko' => 'required',
                'blocks.0.risiko'      => 'required',
            ]);

        $c->set('blocks.0.kode_risiko', 'R-PR-001')
            ->set('blocks.0.risiko', 'Server down saat migrasi')
            ->set('blocks.0.level_kemungkinan', 6)
            ->set('blocks.0.level_dampak', 0)
            ->call('saveBlock', 0)
            ->assertHasErrors([
                'blocks.0.level_kemungkinan' => 'max',
                'blocks.0.level_dampak'      => 'min',
            ]);
    }

    public function test_simpan_risiko_baru_dan_skala_3_level_mpr(): void
    {
        $data = $this->siapkanDataMpr();

        // Besaran 5 (5x1): di MR adalah Sedang, tetapi di MPR (labelMpr) adalah Rendah!
        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('addBlock')
            ->set('blocks.0.kode_risiko', 'R-PR-001')
            ->set('blocks.0.sumber_risiko', 'baru')
            ->set('blocks.0.risiko', 'Gangguan sementara')
            ->set('blocks.0.penyebab', 'Restart layanan')
            ->set('blocks.0.level_kemungkinan', 5)
            ->set('blocks.0.level_dampak', 1)
            ->set('blocks.0.perencanaan_keberlangsungan', 'Siapkan halaman maintenance')
            ->assertViewHas('hasilPerBlok', fn (array $h) => $h[0]['besaran'] === 5 && $h[0]['label'] === 'Rendah')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $tersimpan = MprAnalisisRisiko::where('mpr_perubahan_id', $data->perubahan->id)->firstOrFail();
        $this->assertSame('R-PR-001', $tersimpan->kode_risiko);
        $this->assertSame(5, $tersimpan->besaran_risiko);
        $this->assertSame($data->layanan->id, $tersimpan->layanan_id);
        $this->assertSame('Siapkan halaman maintenance', $tersimpan->perencanaan_keberlangsungan);

        // Besaran 20 (5x4): di MR adalah Sangat Tinggi, tetapi di MPR adalah Tinggi (3 level).
        $c->set('blocks.0.level_dampak', 4)
            ->assertViewHas('hasilPerBlok', fn (array $h) => $h[0]['besaran'] === 20 && $h[0]['label'] === 'Tinggi')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $this->assertSame(20, $tersimpan->fresh()->besaran_risiko);
    }

    public function test_kode_risiko_bentrok_dengan_mr_atau_mpr_lain_ditolak_tetapi_edit_diri_sendiri_lolos(): void
    {
        $data = $this->siapkanDataMpr();

        $mrKonteks = MrKonteks::create([
            'layanan_id'      => $data->layanan->id,
            'nama_instansi'   => 'Dinas',
            'nama_upr'        => 'UPR',
            'tahun_penilaian' => 2026,
            'created_by'      => $data->operator->id,
        ]);
        MrRisiko::create([
            'mr_konteks_id'    => $mrKonteks->id,
            'kode_risiko'      => 'MR-001',
            'peristiwa_risiko' => 'Risiko di MR',
        ]);

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('addBlock')
            ->set('blocks.0.kode_risiko', 'MR-001')
            ->set('blocks.0.risiko', 'Risiko bentrok')
            ->call('saveBlock', 0)
            ->assertHasErrors(['blocks.0.kode_risiko']);

        // Simpan dengan kode unik
        $c->set('blocks.0.kode_risiko', 'R-PR-010')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        // Edit blok yang sama dengan kode yang sama harus lolos (ignoreId bekerja)
        $c->set('blocks.0.risiko', 'Risiko diperbarui')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();
    }

    public function test_cabang_sumber_risiko_dari_mr_prefill_dan_validasi_lintas_layanan(): void
    {
        $data = $this->siapkanDataMpr();

        $mrKonteks = MrKonteks::create([
            'layanan_id'      => $data->layanan->id,
            'nama_instansi'   => 'Dinas',
            'nama_upr'        => 'UPR',
            'tahun_penilaian' => 2026,
            'created_by'      => $data->operator->id,
        ]);
        $mrRisiko = MrRisiko::create([
            'mr_konteks_id'     => $mrKonteks->id,
            'kode_risiko'       => 'MR-100',
            'peristiwa_risiko'  => 'Kegagalan integrasi API',
            'penyebab'          => 'Perubahan skema endpoint',
            'level_kemungkinan' => 4,
            'level_dampak'      => 3,
        ]);

        // Layanan lain (tidak boleh bisa dipilih)
        $lain = $this->buatOperatorLainMpr();
        $mrKonteksLain = MrKonteks::create([
            'layanan_id'      => $lain->layanan->id,
            'nama_instansi'   => 'Lain',
            'nama_upr'        => 'UPR',
            'tahun_penilaian' => 2026,
            'created_by'      => $lain->operator->id,
        ]);
        $mrAsing = MrRisiko::create([
            'mr_konteks_id'    => $mrKonteksLain->id,
            'kode_risiko'      => 'MR-999',
            'peristiwa_risiko' => 'Risiko asing',
        ]);

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('addBlock')
            ->set('blocks.0.kode_risiko', 'R-PR-100')
            ->set('blocks.0.sumber_risiko', 'dari_mr');

        // Wajib pilih mr_risiko_id bila sumber dari_mr
        $c->set('blocks.0.risiko', 'Manual')->call('saveBlock', 0)
            ->assertHasErrors(['blocks.0.mr_risiko_id']);

        // Memilih mrRisiko milik layanan lain ditolak
        $c->set('blocks.0.mr_risiko_id', $mrAsing->id)->call('saveBlock', 0)
            ->assertHasErrors(['blocks.0.mr_risiko_id']);

        // Memilih mrRisiko milik layanan sendiri mem-prefill otomatis dan berhasil disimpan
        $c->set('blocks.0.mr_risiko_id', $mrRisiko->id)
            ->assertSet('blocks.0.risiko', 'Kegagalan integrasi API')
            ->assertSet('blocks.0.penyebab', 'Perubahan skema endpoint')
            ->assertSet('blocks.0.level_kemungkinan', 4)
            ->assertSet('blocks.0.level_dampak', 3)
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $tersimpan = MprAnalisisRisiko::where('kode_risiko', 'R-PR-100')->firstOrFail();
        $this->assertSame('dari_mr', $tersimpan->sumber_risiko);
        $this->assertSame($mrRisiko->id, $tersimpan->mr_risiko_id);
        $this->assertSame(12, $tersimpan->besaran_risiko);
    }

    public function test_hapus_risiko_memakai_soft_delete_dan_bisa_direstore(): void
    {
        $data = $this->siapkanDataMpr();
        $risiko = MprAnalisisRisiko::factory()->withLevels(2, 3)->create([
            'mpr_perubahan_id' => $data->perubahan->id,
        ]);

        $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('removeBlock', 0);

        $this->assertSoftDeleted('mpr_analisis_risiko', [
            'id'         => $risiko->id,
            'deleted_by' => $data->operator->id,
        ]);

        $risiko->refresh()->restore();
        $this->assertNull($risiko->fresh()->deleted_at);
    }

    public function test_tidak_bisa_memanipulasi_id_blok_perubahan_lain(): void
    {
        $data = $this->siapkanDataMpr();
        $perubahanLain = MprPerubahan::factory()->create([
            'mpr_konteks_id' => $data->konteks->id,
            'created_by'     => $data->operator->id,
        ]);
        $risikoAsing = MprAnalisisRisiko::factory()->create([
            'mpr_perubahan_id' => $perubahanLain->id,
            'kode_risiko'      => 'R-ASING',
            'risiko'           => 'Asli',
        ]);

        $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('addBlock')
            ->set('blocks.0.id', $risikoAsing->id)
            ->set('blocks.0.kode_risiko', 'R-RETAS')
            ->set('blocks.0.risiko', 'Diretas')
            ->call('saveBlock', 0)
            ->call('removeBlock', 0);

        $risikoAsing->refresh();
        $this->assertSame('Asli', $risikoAsing->risiko);
        $this->assertNull($risikoAsing->deleted_at);
    }
}
