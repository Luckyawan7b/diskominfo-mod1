<?php

namespace Tests\Feature;

use App\Livewire\StrukturPelaksana\StrukturPelaksanaForm;
use App\Models\Layanan;
use App\Models\MrKonteks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * StrukturPelaksanaTest
 *
 * Modul sekunder — 1-2 skenario dasar, target ≥ 50%.
 */
class StrukturPelaksanaTest extends TestCase
{
    use RefreshDatabase;

    private function buatKonteks(): array
    {
        $operator = \App\Models\User::factory()->operator()->create();
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

    /**
     * Statement: save() menyimpan data struktur pelaksana.
     */
    public function test_simpan_data_struktur_pelaksana(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(StrukturPelaksanaForm::class, ['konteks' => $konteks])
            ->set('pemilik_risiko', 'Kepala Dinas')
            ->set('koordinator_risiko', 'Sekretaris Dinas')
            ->set('pengelola_risiko', 'Kabid Infrastruktur')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mr_struktur_pelaksana', [
            'mr_konteks_id'  => $konteks->id,
            'pemilik_risiko' => 'Kepala Dinas',
        ]);
    }

    /**
     * Branch: admin tidak bisa save → abort 403.
     */
    public function test_admin_tidak_bisa_save_struktur(): void
    {
        ['konteks' => $konteks] = $this->buatKonteks();
        $admin = \App\Models\User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(StrukturPelaksanaForm::class, ['konteks' => $konteks])
            ->set('pemilik_risiko', 'Ubah Ilegal')
            ->call('save')
            ->assertStatus(403);
    }
}
