<?php

namespace Tests\Feature;

use App\Livewire\Konteks\KonteksForm;
use App\Models\Layanan;
use App\Models\MrKonteks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * KonteksTest
 *
 * Teknik: Path Coverage (create/update), Boundary Value Analysis (selera_risiko),
 * Branch Coverage (admin abort 403).
 * Target: ≥ 75%
 */
class KonteksTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buatKonteks(): array
    {
        $operator = \App\Models\User::factory()->operator()->create(['nama_dinas' => 'Dinas Test']);
        $layanan  = Layanan::factory()->ownedBy($operator)->create(['status_layanan' => 'berjalan']);
        $konteks  = MrKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => 'Dinas Test',
            'nama_upr'        => 'UPR Test',
            'tahun_penilaian' => date('Y'),
            'created_by'      => $operator->id,
        ]);

        return compact('operator', 'layanan', 'konteks');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Path: simpan konteks dengan data valid
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Path: operator simpan konteks — data valid → berhasil.
     */
    public function test_operator_dapat_simpan_konteks(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(KonteksForm::class, ['konteks' => $konteks])
            ->set('nama_instansi', 'Dinas Komunikasi dan Informatika')
            ->set('nama_upr', 'UPR SPBE Kominfo')
            ->set('tugas_upr', 'Melaksanakan administrasi SPBE')
            ->set('fungsi_upr', 'Pengelolaan sistem informasi')
            ->set('selera_risiko', 12)
            ->call('save')
            ->assertHasNoErrors();

        $konteks->refresh();
        $this->assertEquals('UPR SPBE Kominfo', $konteks->nama_upr);
        $this->assertSame(12, $konteks->selera_risiko);
    }

    /**
     * Boundary: selera_risiko = 1 (batas bawah valid) → berhasil.
     */
    public function test_selera_risiko_batas_bawah_valid(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(KonteksForm::class, ['konteks' => $konteks])
            ->set('nama_instansi', 'Dinas Test')
            ->set('nama_upr', 'UPR Test')
            ->set('selera_risiko', 1) // batas bawah
            ->call('save')
            ->assertHasNoErrors();
    }

    /**
     * Boundary: selera_risiko = 25 (batas atas valid) → berhasil.
     */
    public function test_selera_risiko_batas_atas_valid(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(KonteksForm::class, ['konteks' => $konteks])
            ->set('nama_instansi', 'Dinas Test')
            ->set('nama_upr', 'UPR Test')
            ->set('selera_risiko', 25) // batas atas
            ->call('save')
            ->assertHasNoErrors();
    }

    /**
     * Boundary: selera_risiko = 0 (di bawah batas bawah) → validation error.
     */
    public function test_selera_risiko_di_bawah_batas_invalid(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(KonteksForm::class, ['konteks' => $konteks])
            ->set('nama_instansi', 'Dinas Test')
            ->set('nama_upr', 'UPR Test')
            ->set('selera_risiko', 0) // invalid: di bawah min
            ->call('save')
            ->assertHasErrors(['selera_risiko']);
    }

    /**
     * Boundary: selera_risiko = 26 (di atas batas atas) → validation error.
     */
    public function test_selera_risiko_di_atas_batas_invalid(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(KonteksForm::class, ['konteks' => $konteks])
            ->set('nama_instansi', 'Dinas Test')
            ->set('nama_upr', 'UPR Test')
            ->set('selera_risiko', 26) // invalid: di atas max
            ->call('save')
            ->assertHasErrors(['selera_risiko']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Branch: admin tidak bisa save
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch: admin coba save konteks → abort 403.
     */
    public function test_admin_tidak_bisa_save_konteks(): void
    {
        ['konteks' => $konteks] = $this->buatKonteks();
        $admin = \App\Models\User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(KonteksForm::class, ['konteks' => $konteks])
            ->set('nama_instansi', 'Ubah Ilegal oleh Admin')
            ->call('save')
            ->assertStatus(403);
    }
}
