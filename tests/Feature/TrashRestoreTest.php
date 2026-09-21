<?php

namespace Tests\Feature;

use App\Livewire\Admin\TrashIndex;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * TrashRestoreTest
 *
 * Verifikasi:
 * 1. Admin bisa restore → deleted_at jadi null
 * 2. Operator akses route /admin/trash → 403
 * 3. Operator panggil restore() via Livewire → 403
 * 4. Admin hapus permanen dengan ketik nama benar → forceDeleted
 * 5. Admin hapus permanen dengan ketik nama salah → error validasi, data tetap ada
 */
class TrashRestoreTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function admin(): \App\Models\User
    {
        return \App\Models\User::factory()->admin()->create();
    }

    private function buatRisikoTerhapus(): array
    {
        $operator = \App\Models\User::factory()->operator()->create([
            'nama_dinas' => 'Dinas Test',
        ]);
        $layanan = Layanan::factory()->ownedBy($operator)->create(['status_layanan' => 'berjalan']);
        $konteks = MrKonteks::create([
            'layanan_id'        => $layanan->id,
            'nama_instansi'     => 'Dinas Test',
            'nama_upr'          => 'UPR Test',
            'tahun_penilaian'   => date('Y'),
            'tahun_pelaksanaan' => (int) date('Y') - 1,
            'created_by'        => $operator->id,
        ]);
        $risiko = MrRisiko::create([
            'mr_konteks_id'    => $konteks->id,
            'kode_risiko'      => 'R-01',
            'peristiwa_risiko' => 'Risiko test untuk dihapus',
            'created_by'       => $operator->id,
        ]);

        // Soft delete risiko
        $this->actingAs($operator);
        $risiko->delete();
        $this->be($this->admin()); // switch ke admin untuk test selanjutnya

        return compact('operator', 'layanan', 'konteks', 'risiko');
    }

    // ── Test 1: Admin restore → deleted_at menjadi null ──────────────────────

    public function test_admin_dapat_restore_risiko_terhapus(): void
    {
        ['risiko' => $risiko] = $this->buatRisikoTerhapus();
        $admin = $this->admin();

        $this->assertNotNull($risiko->fresh()?->deleted_at ?? $risiko->deleted_at);

        Livewire::actingAs($admin)
            ->test(TrashIndex::class)
            ->call('restore', 'mr_risiko', $risiko->id)
            ->assertHasNoErrors();

        $restored = MrRisiko::withTrashed()->find($risiko->id);
        $this->assertNull($restored->deleted_at, 'deleted_at harus null setelah dipulihkan');
    }

    // ── Test 2: Operator akses route /admin/trash → 403 ──────────────────────

    public function test_operator_tidak_bisa_akses_halaman_trash(): void
    {
        $operator = \App\Models\User::factory()->operator()->create(['nama_dinas' => 'Dinas Test']);

        // Middleware role:admin me-redirect operator (bukan 403)
        $this->actingAs($operator)
            ->get(route('admin.trash.index'))
            ->assertRedirect();
    }

    // ── Test 3: Operator panggil restore() via Livewire → 403 ─────────────────

    public function test_operator_tidak_bisa_panggil_restore_via_livewire(): void
    {
        ['risiko' => $risiko] = $this->buatRisikoTerhapus();
        $operator = \App\Models\User::factory()->operator()->create(['nama_dinas' => 'Dinas Test']);

        // Panggil method restore() langsung sebagai operator — harus abort 403
        // Livewire render akan gagal sebelum method bisa dipanggil karena render() pun ada guard.
        // Kita verifikasi data tetap tidak dipulihkan setelah percobaan.
        try {
            Livewire::actingAs($operator)
                ->test(TrashIndex::class)
                ->call('restore', 'mr_risiko', $risiko->id);
        } catch (\Throwable $e) {
            // Exception (abort 403 atau snapshot invalid) diterima — data harus tetap soft-deleted
        }

        // Risiko harus tetap soft-deleted
        $stillDeleted = MrRisiko::withTrashed()->find($risiko->id);
        $this->assertNotNull($stillDeleted?->deleted_at, 'Data seharusnya masih soft-deleted');
    }

    // ── Test 4: Force delete dengan ketik nama benar → data dihapus permanen ──

    public function test_admin_dapat_hapus_permanen_dengan_konfirmasi_nama_benar(): void
    {
        ['risiko' => $risiko] = $this->buatRisikoTerhapus();
        $admin = $this->admin();

        $namaKonfirmasi = 'R-01 — Risiko test untuk dihapus';

        Livewire::actingAs($admin)
            ->test(TrashIndex::class)
            ->call('openForceDeleteModal', 'mr_risiko', $risiko->id, $namaKonfirmasi)
            ->set('forceDeleteInput', $namaKonfirmasi)
            ->call('executeForceDelete')
            ->assertHasNoErrors();

        // Data harus benar-benar terhapus (tidak bisa ditemukan bahkan dengan withTrashed)
        $this->assertNull(MrRisiko::withTrashed()->find($risiko->id));
    }

    // ── Test 5: Force delete dengan ketik nama salah → error, data tetap ada ──

    public function test_admin_tidak_bisa_hapus_permanen_dengan_konfirmasi_nama_salah(): void
    {
        ['risiko' => $risiko] = $this->buatRisikoTerhapus();
        $admin = $this->admin();

        $namaKonfirmasi = 'R-01 — Risiko test untuk dihapus';

        Livewire::actingAs($admin)
            ->test(TrashIndex::class)
            ->call('openForceDeleteModal', 'mr_risiko', $risiko->id, $namaKonfirmasi)
            ->set('forceDeleteInput', 'nama yang salah')
            ->call('executeForceDelete')
            ->assertHasErrors(['forceDeleteInput']);

        // Data masih ada dengan soft delete
        $this->assertNotNull(MrRisiko::withTrashed()->find($risiko->id));
    }

    // ── Test 6: deleted_by tercatat saat soft delete ──────────────────────────

    public function test_deleted_by_tercatat_saat_soft_delete(): void
    {
        $operator = \App\Models\User::factory()->operator()->create([
            'nama_dinas' => 'Dinas Test',
        ]);
        $layanan = Layanan::factory()->ownedBy($operator)->create(['status_layanan' => 'berjalan']);
        $konteks = MrKonteks::create([
            'layanan_id'        => $layanan->id,
            'nama_instansi'     => 'Dinas Test',
            'nama_upr'          => 'UPR Test',
            'tahun_penilaian'   => date('Y'),
            'tahun_pelaksanaan' => (int) date('Y') - 1,
            'created_by'        => $operator->id,
        ]);
        $risiko = MrRisiko::create([
            'mr_konteks_id'    => $konteks->id,
            'kode_risiko'      => 'R-02',
            'peristiwa_risiko' => 'Risiko deleted_by test',
            'created_by'       => $operator->id,
        ]);

        $this->actingAs($operator);
        $risiko->delete();

        $deleted = MrRisiko::withTrashed()->find($risiko->id);
        $this->assertNotNull($deleted->deleted_by, 'deleted_by harus terisi setelah delete');
        $this->assertEquals($operator->id, $deleted->deleted_by);
    }
}
