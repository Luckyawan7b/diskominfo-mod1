<?php

namespace Tests\Feature;

use App\Livewire\Risiko\RisikoIndex;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * RisikoDeleteTest
 *
 * Verifikasi:
 * 1. Hapus 1 risiko = soft delete baris itu saja (konteks & baris lain aman)
 * 2. Re-ranking otomatis setelah hapus (via MrRisikoObserver)
 * 3. Admin tidak boleh hapus (abort 403)
 * 4. Operator tidak bisa hapus risiko milik operator/konteks lain (IDOR protection)
 */
class RisikoDeleteTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buatKonteksDenganRisiko(int $jumlahRisiko = 3): array
    {
        $operator = \App\Models\User::factory()->operator()->create([
            'nama_dinas' => 'Dinas Test',
            'alias'      => 'dynastest',
        ]);
        $layanan = Layanan::factory()->ownedBy($operator)->create(['status_layanan' => 'berjalan']);
        $konteks = MrKonteks::create([
            'layanan_id'         => $layanan->id,
            'nama_instansi'      => 'Dinas Test',
            'nama_upr'           => 'UPR Test',
            'tahun_penilaian'    => date('Y'),
            'created_by'         => $operator->id,
            'tahun_pelaksanaan'  => (int) date('Y') - 1,
        ]);

        $risikos = [];
        for ($i = 1; $i <= $jumlahRisiko; $i++) {
            $risikos[] = MrRisiko::create([
                'mr_konteks_id'    => $konteks->id,
                'kode_risiko'      => 'R-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'peristiwa_risiko' => "Peristiwa risiko ke-{$i}",
                'level_kemungkinan'=> $i,
                'level_dampak'     => $i,
                'created_by'       => $operator->id,
            ]);
        }

        return compact('operator', 'layanan', 'konteks', 'risikos');
    }

    // ── Test 1: Soft delete hanya 1 baris, konteks & baris lain tetap ─────────

    public function test_hapus_satu_risiko_tidak_menghapus_konteks_dan_risiko_lain(): void
    {
        ['operator' => $operator, 'konteks' => $konteks, 'risikos' => $risikos] = $this->buatKonteksDenganRisiko(3);

        $risikoTarget = $risikos[0];

        Livewire::actingAs($operator)
            ->test(RisikoIndex::class, ['konteks' => $konteks])
            ->call('deleteRisiko', $risikoTarget->id)
            ->assertHasNoErrors();

        // Risiko yang dihapus harus soft-deleted
        $this->assertSoftDeleted('mr_risiko', ['id' => $risikoTarget->id]);

        // Konteks TIDAK ikut terhapus
        $this->assertDatabaseHas('mr_konteks', ['id' => $konteks->id, 'deleted_at' => null]);

        // Risiko lain dalam konteks yang sama TIDAK terhapus
        $this->assertDatabaseHas('mr_risiko', ['id' => $risikos[1]->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('mr_risiko', ['id' => $risikos[2]->id, 'deleted_at' => null]);
    }

    // ── Test 2: Re-ranking otomatis setelah hapus ─────────────────────────────

    public function test_prioritas_risiko_direrank_setelah_hapus(): void
    {
        ['operator' => $operator, 'konteks' => $konteks, 'risikos' => $risikos] = $this->buatKonteksDenganRisiko(3);

        // Hapus risiko pertama
        Livewire::actingAs($operator)
            ->test(RisikoIndex::class, ['konteks' => $konteks])
            ->call('deleteRisiko', $risikos[0]->id);

        // Risiko yang tersisa harus punya prioritas 1 dan 2 (bukan 2 dan 3)
        $remaining = MrRisiko::where('mr_konteks_id', $konteks->id)
            ->whereNull('deleted_at')
            ->orderBy('prioritas_risiko')
            ->pluck('prioritas_risiko')
            ->toArray();

        $this->assertSame([1, 2], $remaining, 'Prioritas harus di-rerank setelah hapus (mulai dari 1)');
    }

    // ── Test 3: Admin tidak boleh hapus risiko (abort 403) ────────────────────

    public function test_admin_tidak_bisa_menghapus_risiko(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();
        ['konteks' => $konteks, 'risikos' => $risikos] = $this->buatKonteksDenganRisiko(1);

        Livewire::actingAs($admin)
            ->test(RisikoIndex::class, ['konteks' => $konteks])
            ->call('deleteRisiko', $risikos[0]->id)
            ->assertStatus(403);

        // Risiko harus tetap ada (tidak terhapus)
        $this->assertDatabaseHas('mr_risiko', ['id' => $risikos[0]->id, 'deleted_at' => null]);
    }

    // ── Test 4: IDOR protection — operator tidak bisa hapus risiko milik orang lain ───

    public function test_operator_tidak_bisa_hapus_risiko_milik_konteks_lain(): void
    {
        // Buat 2 operator berbeda dengan konteks berbeda
        ['operator' => $operatorA, 'konteks' => $konteksA] = $this->buatKonteksDenganRisiko(1);
        ['operator' => $operatorB, 'risikos' => $risikoB] = $this->buatKonteksDenganRisiko(1);

        // Operator A mencoba hapus risiko milik operator B menggunakan konteks milik A
        // findOrFail harus gagal (model not found) karena scope ke konteks A
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($operatorA)
            ->test(RisikoIndex::class, ['konteks' => $konteksA])
            ->call('deleteRisiko', $risikoB[0]->id);

        // Risiko milik B harus tetap ada
        $this->assertDatabaseHas('mr_risiko', ['id' => $risikoB[0]->id, 'deleted_at' => null]);
    }
}
