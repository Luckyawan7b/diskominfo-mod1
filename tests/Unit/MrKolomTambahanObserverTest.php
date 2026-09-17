<?php

namespace Tests\Unit;

use App\Models\Layanan;
use App\Models\MrKolomTambahan;
use App\Models\MrKonteks;
use App\Models\MrLayananDigital;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MrKolomTambahanObserverTest
 *
 * Teknik: Branch & Condition Coverage pada MrKolomTambahanObserver.
 *
 * Observer mendengarkan event `saved` pada MrKolomTambahan:
 * - Jika layanan_prioritas TIDAK berubah (not dirty) → tidak ada aksi
 * - Jika berubah ke 'Prioritas' → auto-create MrLayananDigital (firstOrCreate)
 * - Jika berubah dari 'Prioritas' ke nilai lain → delete MrLayananDigital
 *
 * Target coverage: ≥ 95%
 */
class MrKolomTambahanObserverTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Buat operator + layanan + konteks + risiko, lalu kembalikan risiko-nya. */
    private function buatRisiko(): MrRisiko
    {
        $operator = \App\Models\User::factory()->operator()->create();
        $layanan  = Layanan::factory()->ownedBy($operator)->create();
        $konteks  = MrKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => 'Dinas Test',
            'nama_upr'        => 'UPR Test',
            'tahun_penilaian' => date('Y'),
            'created_by'      => $operator->id,
        ]);

        return MrRisiko::create([
            'mr_konteks_id'   => $konteks->id,
            'kode_risiko'     => 'KOL-R-01',
            'peristiwa_risiko'=> 'Risiko kolom tambahan test',
            'created_by'      => $operator->id,
        ]);
    }

    /** Buat MrKolomTambahan untuk risiko tanpa layanan_prioritas (null). */
    private function buatKolom(MrRisiko $risiko, ?string $layananPrioritas = null): MrKolomTambahan
    {
        return MrKolomTambahan::create([
            'mr_risiko_id'    => $risiko->id,
            'layanan_prioritas' => $layananPrioritas,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Branch: layanan_prioritas tidak berubah → tidak ada aksi
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Condition (negative): update field lain (bukan layanan_prioritas) → tidak ada MrLayananDigital dibuat.
     */
    public function test_saved_tidak_ada_aksi_jika_layanan_prioritas_tidak_berubah(): void
    {
        $risiko = $this->buatRisiko();
        $kolom  = $this->buatKolom($risiko, null);

        // Update field lain
        $kolom->update(['layanan_pendukung' => 'Sistem E-Government']);

        $this->assertDatabaseEmpty('mr_layanan_digital');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Branch: layanan_prioritas diset ke 'Prioritas' → auto-create MrLayananDigital
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch (true): layanan_prioritas = 'Prioritas' → MrLayananDigital dibuat.
     */
    public function test_saved_auto_create_layanan_digital_saat_prioritas(): void
    {
        $risiko = $this->buatRisiko();
        $kolom  = $this->buatKolom($risiko, null);

        $kolom->update(['layanan_prioritas' => 'Prioritas']);

        $this->assertDatabaseHas('mr_layanan_digital', [
            'mr_risiko_id' => $risiko->id,
        ]);
    }

    /**
     * Branch (idempotent): update ke 'Prioritas' lagi tidak buat duplikat (firstOrCreate).
     */
    public function test_saved_firstorcreate_tidak_duplikat_jika_sudah_ada(): void
    {
        $risiko = $this->buatRisiko();
        $kolom  = $this->buatKolom($risiko, null);

        // Set ke Prioritas pertama kali
        $kolom->update(['layanan_prioritas' => 'Prioritas']);
        $this->assertSame(1, MrLayananDigital::where('mr_risiko_id', $risiko->id)->count());

        // Ubah ke lain dulu, lalu balik ke Prioritas lagi
        $kolom->update(['layanan_prioritas' => 'Tematik']);
        $kolom->update(['layanan_prioritas' => 'Prioritas']);

        // Harus tetap hanya 1 record
        $this->assertSame(1, MrLayananDigital::where('mr_risiko_id', $risiko->id)->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Branch: layanan_prioritas berubah dari 'Prioritas' ke nilai lain → hapus MrLayananDigital
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch (false/else): layanan_prioritas diubah dari 'Prioritas' ke 'Tidak Prioritas'
     * → MrLayananDigital dihapus.
     */
    public function test_saved_hapus_layanan_digital_saat_diubah_dari_prioritas(): void
    {
        $risiko = $this->buatRisiko();
        $kolom  = $this->buatKolom($risiko, 'Prioritas');

        // Saat create dengan 'Prioritas', observer akan trigger → create layanan digital
        // Verify MrLayananDigital ada
        $this->assertDatabaseHas('mr_layanan_digital', ['mr_risiko_id' => $risiko->id]);

        // Ubah dari 'Prioritas' ke 'Instansional'
        $kolom->update(['layanan_prioritas' => 'Instansional']);

        $this->assertDatabaseMissing('mr_layanan_digital', ['mr_risiko_id' => $risiko->id]);
    }

    /**
     * Branch (false/else): layanan_prioritas diubah ke null dari 'Prioritas' → hapus.
     */
    public function test_saved_hapus_layanan_digital_saat_prioritas_diubah_ke_null(): void
    {
        $risiko = $this->buatRisiko();
        $kolom  = $this->buatKolom($risiko, 'Prioritas');

        $this->assertDatabaseHas('mr_layanan_digital', ['mr_risiko_id' => $risiko->id]);

        $kolom->update(['layanan_prioritas' => null]);

        $this->assertDatabaseMissing('mr_layanan_digital', ['mr_risiko_id' => $risiko->id]);
    }
}
