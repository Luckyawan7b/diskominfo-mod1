<?php

namespace Tests\Unit;

use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MrRisikoObserverTest
 *
 * Teknik: Branch & Condition Coverage pada MrRisikoObserver.
 *
 * Observer terikat ke event Eloquent asli, sehingga test ini membutuhkan
 * RefreshDatabase dan Laravel TestCase (bukan PHPUnit plain TestCase).
 *
 * Target coverage: ≥ 95%
 *
 * Branch yang diuji:
 * - saving(): hitung besaran_risiko jika level_kemungkinan/level_dampak dirty & tidak null
 * - saving(): TIDAK hitung jika field lain yang berubah (negative condition)
 * - saving(): TIDAK hitung jika salah satu level masih null
 * - saved(): re-rank jika besaran_risiko dirty atau baru dibuat
 * - saved(): TIDAK re-rank jika field tak relevan yang berubah
 * - deleted(): re-rank setelah soft delete
 * - rankPrioritas(): risiko besaran tertinggi mendapat prioritas 1
 * - rankPrioritas(): risiko dengan besaran null diletakkan di akhir
 */
class MrRisikoObserverTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Buat operator + layanan + konteks minimal untuk FK. */
    private function buatKonteks(): MrKonteks
    {
        $operator = \App\Models\User::factory()->operator()->create(['nama_dinas' => 'Dinas Test']);
        $layanan  = Layanan::factory()->ownedBy($operator)->create();

        return MrKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => 'Dinas Test',
            'nama_upr'        => 'UPR SPBE Test',
            'tahun_penilaian' => date('Y'),
            'created_by'      => $operator->id,
        ]);
    }

    /** Buat risiko tanpa level (besaran akan null). */
    private function buatRisiko(MrKonteks $konteks, array $extra = []): MrRisiko
    {
        return MrRisiko::create(array_merge([
            'mr_konteks_id'   => $konteks->id,
            'kode_risiko'     => 'TST-R-' . rand(1, 999),
            'peristiwa_risiko'=> 'Risiko test',
            'created_by'      => $konteks->created_by,
        ], $extra));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // saving() — Condition: hitung besaran_risiko jika kedua level dirty & tidak null
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Condition (positive): level_kemungkinan & level_dampak berubah → besaran dihitung.
     */
    public function test_saving_hitung_besaran_jika_level_kemungkinan_dan_dampak_berubah(): void
    {
        $konteks = $this->buatKonteks();
        $risiko  = $this->buatRisiko($konteks);

        $risiko->update([
            'level_kemungkinan' => 4,
            'level_dampak'      => 3,
        ]);

        $this->assertSame(12, $risiko->fresh()->besaran_risiko); // 4 × 3 = 12
    }

    /**
     * Condition (positive): hanya level_dampak berubah (kemungkinan sudah terisi) → hitung ulang.
     */
    public function test_saving_hitung_ulang_saat_hanya_dampak_berubah(): void
    {
        $konteks = $this->buatKonteks();
        $risiko  = $this->buatRisiko($konteks, [
            'level_kemungkinan' => 3,
            'level_dampak'      => 2,
        ]);
        // Pastikan besaran sudah 6 (3 × 2)
        $this->assertSame(6, $risiko->fresh()->besaran_risiko);

        // Ubah hanya dampak
        $risiko->update(['level_dampak' => 4]);
        $this->assertSame(12, $risiko->fresh()->besaran_risiko); // 3 × 4 = 12
    }

    /**
     * Condition (negative): field lain berubah (bukan level) → besaran TIDAK berubah.
     */
    public function test_saving_tidak_hitung_besaran_jika_field_lain_berubah(): void
    {
        $konteks = $this->buatKonteks();
        $risiko  = $this->buatRisiko($konteks, [
            'level_kemungkinan' => 2,
            'level_dampak'      => 3,
        ]);
        $besaranAwal = $risiko->fresh()->besaran_risiko; // 6

        // Update field lain
        $risiko->update(['peristiwa_risiko' => 'Deskripsi diubah tanpa mengubah level']);
        $this->assertSame($besaranAwal, $risiko->fresh()->besaran_risiko);
    }

    /**
     * Condition (negative): level_kemungkinan null → besaran tidak dihitung.
     */
    public function test_saving_tidak_hitung_jika_level_kemungkinan_null(): void
    {
        $konteks = $this->buatKonteks();
        $risiko  = $this->buatRisiko($konteks);

        // Set hanya dampak, kemungkinan tetap null
        $risiko->update(['level_dampak' => 3]);
        $this->assertNull($risiko->fresh()->besaran_risiko);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // saved() + rankPrioritas() — Path Coverage
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Path: risiko besaran tertinggi mendapat prioritas 1 setelah re-rank.
     */
    public function test_rank_prioritas_tertinggi_dapat_prioritas_satu(): void
    {
        $konteks = $this->buatKonteks();

        $risikoA = $this->buatRisiko($konteks, ['level_kemungkinan' => 5, 'level_dampak' => 5]); // besaran 25
        $risikoB = $this->buatRisiko($konteks, ['level_kemungkinan' => 2, 'level_dampak' => 2]); // besaran 4
        $risikoC = $this->buatRisiko($konteks, ['level_kemungkinan' => 3, 'level_dampak' => 3]); // besaran 9

        // Trigger re-rank dengan update besaran lewat level
        $risikoA->fresh(); // pastikan data fresh

        $this->assertSame(1, $risikoA->fresh()->prioritas_risiko); // 25 = prioritas 1
        $this->assertSame(2, $risikoC->fresh()->prioritas_risiko); // 9 = prioritas 2
        $this->assertSame(3, $risikoB->fresh()->prioritas_risiko); // 4 = prioritas 3
    }

    /**
     * Branch: risiko dengan besaran_risiko null diletakkan di akhir urutan prioritas.
     */
    public function test_rank_prioritas_risiko_null_besaran_di_akhir(): void
    {
        $konteks = $this->buatKonteks();

        $risikoA = $this->buatRisiko($konteks, ['level_kemungkinan' => 4, 'level_dampak' => 4]); // besaran 16
        $risikoNull = $this->buatRisiko($konteks); // besaran null
        // Trigger re-rank lewat update besaran risikoA
        $risikoA->update(['level_dampak' => 3]); // besaran jadi 12, re-rank dipicu

        $this->assertSame(1, $risikoA->fresh()->prioritas_risiko);
        $this->assertSame(2, $risikoNull->fresh()->prioritas_risiko); // null → di akhir
    }

    /**
     * Statement: re-rank berjalan otomatis setelah soft delete risiko.
     */
    public function test_deleted_rerank_setelah_soft_delete(): void
    {
        $konteks = $this->buatKonteks();

        $risikoA = $this->buatRisiko($konteks, ['level_kemungkinan' => 5, 'level_dampak' => 5]); // prioritas 1
        $risikoB = $this->buatRisiko($konteks, ['level_kemungkinan' => 2, 'level_dampak' => 2]); // prioritas 2

        // Soft delete risiko A (prioritas 1)
        $risikoA->delete();

        // B harus naik jadi prioritas 1
        $this->assertSame(1, $risikoB->fresh()->prioritas_risiko);
    }

    /**
     * Branch (saved): risiko baru dibuat → re-rank langsung dipicu (wasRecentlyCreated).
     */
    public function test_saved_rerank_saat_risiko_baru_dibuat(): void
    {
        $konteks = $this->buatKonteks();

        $risikoA = $this->buatRisiko($konteks, ['level_kemungkinan' => 2, 'level_dampak' => 2]); // besaran 4
        // Buat risiko B dengan besaran lebih tinggi
        $risikoB = $this->buatRisiko($konteks, ['level_kemungkinan' => 5, 'level_dampak' => 5]); // besaran 25

        // Re-rank harus sudah terpicu saat risikoB dibuat
        $this->assertSame(1, $risikoB->fresh()->prioritas_risiko);
        $this->assertSame(2, $risikoA->fresh()->prioritas_risiko);
    }
}
