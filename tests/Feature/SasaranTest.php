<?php

namespace Tests\Feature;

use App\Livewire\Sasaran\SasaranForm;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrIndikatorKinerja;
use App\Models\MrSasaranUpr;
use App\Models\RefSasaranNasional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * SasaranTest
 *
 * Teknik: Statement Coverage (addBlock, removeBlock), Branch Coverage (saveBlock firstOrCreate).
 * Target: ≥ 75%
 */
class SasaranTest extends TestCase
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

        return compact('operator', 'konteks');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Statement: addBlock() buat MrSasaranUpr + 1 indikator kosong
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Statement: addBlock() membuat 1 MrSasaranUpr + 1 MrIndikatorKinerja kosong.
     */
    public function test_add_block_buat_sasaran_upr_dan_indikator_kosong(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(SasaranForm::class, ['konteks' => $konteks])
            ->call('addBlock')
            ->assertHasNoErrors();

        $this->assertCount(1, MrSasaranUpr::where('mr_konteks_id', $konteks->id)->get());
        $this->assertCount(1, MrIndikatorKinerja::all());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Branch: saveBlock() dengan sasaran_nasional baru → firstOrCreate buat record baru
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch (true - create): saveBlock() dengan sasaran_nasional baru
     * → RefSasaranNasional baru dibuat.
     */
    public function test_save_block_dengan_sasaran_nasional_baru_membuat_ref_baru(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(SasaranForm::class, ['konteks' => $konteks])
            ->call('addBlock')
            ->set('blocks.0.sasaran_nasional', 'Sasaran Pembangunan Nasional Baru')
            ->set('blocks.0.sasaran_upr', 'Peningkatan Kualitas Pelayanan Publik')
            ->set('blocks.0.indikator.0.indikator_kinerja', 'Persentase kepuasan pengguna')
            ->set('blocks.0.indikator.0.target_kinerja', '85%')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ref_sasaran_nasional', [
            'teks_sasaran' => 'Sasaran Pembangunan Nasional Baru',
        ]);

        $ref = RefSasaranNasional::where('teks_sasaran', 'Sasaran Pembangunan Nasional Baru')->first();
        $this->assertDatabaseHas('mr_sasaran_upr', [
            'mr_konteks_id'           => $konteks->id,
            'sasaran_upr'             => 'Peningkatan Kualitas Pelayanan Publik',
            'ref_sasaran_nasional_id' => $ref->id,
        ]);
    }

    /**
     * Branch (false - reuse): saveBlock() dengan sasaran_nasional yang sudah ada
     * → firstOrCreate reuse record lama, tidak membuat duplikat.
     */
    public function test_save_block_dengan_sasaran_nasional_sudah_ada_reuse_ref(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        // Buat ref yang sudah ada terlebih dahulu
        $existingRef = RefSasaranNasional::create([
            'teks_sasaran' => 'Sasaran Nasional Yang Sudah Ada',
        ]);

        $component = Livewire::test(SasaranForm::class, ['konteks' => $konteks])
            ->call('addBlock')
            ->set('blocks.0.sasaran_nasional', 'Sasaran Nasional Yang Sudah Ada') // sama persis
            ->set('blocks.0.sasaran_upr', 'UPR dengan ref lama')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        // Harus tetap hanya 1 RefSasaranNasional
        $this->assertSame(1, RefSasaranNasional::count());
        $this->assertDatabaseHas('mr_sasaran_upr', [
            'mr_konteks_id'           => $konteks->id,
            'ref_sasaran_nasional_id' => $existingRef->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Statement: removeBlock() → soft delete
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Statement: removeBlock() → MrSasaranUpr soft deleted.
     */
    public function test_remove_block_soft_delete_sasaran(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(SasaranForm::class, ['konteks' => $konteks])
            ->call('addBlock')
            ->set('blocks.0.sasaran_upr', 'Sasaran yang akan dihapus')
            ->call('saveBlock', 0)
            ->call('removeBlock', 0)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('mr_sasaran_upr', [
            'mr_konteks_id' => $konteks->id,
            'sasaran_upr'   => 'Sasaran yang akan dihapus',
        ]);
    }
}
