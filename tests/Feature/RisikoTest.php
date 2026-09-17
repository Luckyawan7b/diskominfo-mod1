<?php

namespace Tests\Feature;

use App\Livewire\Risiko\RisikoForm;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * RisikoTest
 *
 * Teknik: Branch Coverage (tiap validasi & kondisi), Path Coverage (create vs update).
 * Target: ≥ 75% coverage pada RisikoForm
 */
class RisikoTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buatKonteks(): array
    {
        $operator = \App\Models\User::factory()->operator()->create([
            'nama_dinas' => 'Dinas Test',
            'alias'      => 'dinastest',
        ]);
        $layanan = Layanan::factory()->ownedBy($operator)->create(['status_layanan' => 'berjalan']);
        $konteks = MrKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => 'Dinas Test',
            'nama_upr'        => 'UPR SPBE Test',
            'tahun_penilaian' => date('Y'),
            'created_by'      => $operator->id,
        ]);

        return compact('operator', 'layanan', 'konteks');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Path: create (isNew = true) — simpan risiko baru
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Path (create): operator simpan risiko baru dengan semua field valid → berhasil.
     */
    public function test_operator_dapat_simpan_risiko_baru(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();

        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'TST-R-01')
            ->set('peristiwa_risiko', 'Server mengalami downtime')
            ->set('penyebab', 'Infrastruktur tidak memadai')
            ->set('dampak', 'Pelayanan terganggu')
            ->set('kategori_risiko', 'Risiko Operasional')
            ->set('level_kemungkinan', 4)
            ->set('level_dampak', 3)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mr_risiko', [
            'mr_konteks_id'   => $konteks->id,
            'kode_risiko'     => 'TST-R-01',
            'peristiwa_risiko'=> 'Server mengalami downtime',
        ]);
    }

    /**
     * Path (create): besaran risiko dihitung otomatis oleh observer saat save.
     */
    public function test_besaran_risiko_dihitung_otomatis_saat_save(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'TST-R-01')
            ->set('peristiwa_risiko', 'Risiko test besaran')
            ->set('level_kemungkinan', 4)
            ->set('level_dampak', 3)
            ->call('save')
            ->assertHasNoErrors();

        $risiko = MrRisiko::where('kode_risiko', 'TST-R-01')->first();
        $this->assertSame(12, $risiko->besaran_risiko); // 4 × 3 = 12
    }

    /**
     * Path (update): operator update risiko existing → berhasil.
     */
    public function test_operator_dapat_update_risiko_existing(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        $risiko = MrRisiko::create([
            'mr_konteks_id'   => $konteks->id,
            'kode_risiko'     => 'TST-R-01',
            'peristiwa_risiko'=> 'Risiko awal',
            'created_by'      => $operator->id,
        ]);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => $risiko])
            ->set('peristiwa_risiko', 'Risiko yang sudah diupdate')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mr_risiko', [
            'id'              => $risiko->id,
            'peristiwa_risiko'=> 'Risiko yang sudah diupdate',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Condition: validasi field wajib
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Condition: kode_risiko kosong → validation error.
     */
    public function test_validasi_kode_risiko_kosong(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', '')
            ->set('peristiwa_risiko', 'Risiko test')
            ->call('save')
            ->assertHasErrors(['kode_risiko' => 'required']);
    }

    /**
     * Condition: peristiwa_risiko kosong → validation error.
     */
    public function test_validasi_peristiwa_risiko_kosong(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'TST-R-01')
            ->set('peristiwa_risiko', '')
            ->call('save')
            ->assertHasErrors(['peristiwa_risiko' => 'required']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Branch: admin tidak bisa save (abort 403)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch: admin coba call save() → abort 403.
     */
    public function test_admin_tidak_bisa_save_risiko(): void
    {
        ['konteks' => $konteks] = $this->buatKonteks();
        $admin = \App\Models\User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->call('save')
            ->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Statement: updatedMrSasaranUprId() reset indikator_kinerja_snapshot
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Statement: mengubah mr_sasaran_upr_id → indikator_kinerja_snapshot di-reset ke null.
     */
    public function test_updated_mr_sasaran_upr_id_reset_indikator_snapshot(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('indikator_kinerja_snapshot', 'Snapshot lama')
            ->set('mr_sasaran_upr_id', 99)
            ->assertSet('indikator_kinerja_snapshot', null);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Condition: perlakuan hanya disimpan jika keputusan_perlakuan terisi
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Condition (positive): keputusan_perlakuan terisi → record perlakuan dibuat.
     */
    public function test_perlakuan_tersimpan_jika_keputusan_terisi(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'TST-R-01')
            ->set('peristiwa_risiko', 'Risiko dengan perlakuan')
            ->set('keputusan_perlakuan', 'Mengurangi risiko')
            ->set('deskripsi_detail_perlakuan', 'Beli UPS backup')
            ->call('save')
            ->assertHasNoErrors();

        $risiko = MrRisiko::where('kode_risiko', 'TST-R-01')->first();
        $this->assertNotNull($risiko->perlakuan);
        $this->assertEquals('Mengurangi risiko', $risiko->perlakuan->keputusan_perlakuan);
    }

    /**
     * Condition (negative): keputusan_perlakuan kosong → record perlakuan TIDAK dibuat.
     */
    public function test_perlakuan_tidak_dibuat_jika_keputusan_kosong(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'TST-R-01')
            ->set('peristiwa_risiko', 'Risiko tanpa perlakuan')
            ->set('keputusan_perlakuan', '')
            ->call('save')
            ->assertHasNoErrors();

        $risiko = MrRisiko::where('kode_risiko', 'TST-R-01')->first();
        $this->assertNull($risiko->perlakuan);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Condition: residual hanya dihitung jika kedua level terisi
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Condition (positive): kedua level residual terisi → besaran_risiko residual dihitung.
     */
    public function test_residual_dihitung_jika_kedua_level_terisi(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'TST-R-01')
            ->set('peristiwa_risiko', 'Risiko dengan residual')
            ->set('level_kemungkinan_residual', 2)
            ->set('level_dampak_residual', 2)
            ->set('keterangan_residual', 'Setelah mitigasi')
            ->call('save')
            ->assertHasNoErrors();

        $risiko = MrRisiko::where('kode_risiko', 'TST-R-01')->first();
        $this->assertNotNull($risiko->residual);
        $this->assertSame(4, $risiko->residual->besaran_risiko); // 2 × 2 = 4
    }

    /**
     * Condition (negative): hanya satu level residual → besaran_risiko residual null.
     */
    public function test_residual_besaran_null_jika_satu_level_kosong(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'TST-R-01')
            ->set('peristiwa_risiko', 'Risiko residual parsial')
            ->set('level_kemungkinan_residual', 2)
            ->set('level_dampak_residual', null) // kosong
            ->set('keterangan_residual', 'Catatan')
            ->call('save')
            ->assertHasNoErrors();

        $risiko = MrRisiko::where('kode_risiko', 'TST-R-01')->first();
        $this->assertNotNull($risiko->residual);
        $this->assertNull($risiko->residual->besaran_risiko);
    }
}
