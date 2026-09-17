<?php

namespace Tests\Feature;

use App\Livewire\Risiko\PetaRisiko;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * PetaRisikoTest
 *
 * Modul sekunder — render matrix dengan data seed, branch selectCell.
 * Target: ≥ 50%
 */
class PetaRisikoTest extends TestCase
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
            'selera_risiko'   => 12,
            'created_by'      => $operator->id,
        ]);

        return compact('operator', 'konteks');
    }

    /**
     * Statement: PetaRisiko render matrix dengan data risiko yang ada.
     */
    public function test_peta_risiko_render_dengan_data(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        // Buat 2 risiko dengan level terisi
        MrRisiko::create([
            'mr_konteks_id'    => $konteks->id,
            'kode_risiko'      => 'TST-R-01',
            'peristiwa_risiko' => 'Risiko A',
            'level_kemungkinan'=> 4,
            'level_dampak'     => 3,
            'created_by'       => $operator->id,
        ]);

        MrRisiko::create([
            'mr_konteks_id'    => $konteks->id,
            'kode_risiko'      => 'TST-R-02',
            'peristiwa_risiko' => 'Risiko B',
            'level_kemungkinan'=> 2,
            'level_dampak'     => 2,
            'created_by'       => $operator->id,
        ]);

        Livewire::test(PetaRisiko::class, ['konteks' => $konteks])
            ->assertSee('Risiko A')
            ->assertSee('Risiko B');
    }

    /**
     * Branch: selectCell() toggle — pilih cell yang sama dua kali → reset ke null.
     */
    public function test_select_cell_toggle_reset_ke_null(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(PetaRisiko::class, ['konteks' => $konteks])
            ->call('selectCell', 4, 3)
            ->assertSet('selectedK', 4)
            ->assertSet('selectedD', 3)
            ->call('selectCell', 4, 3) // sama → toggle reset
            ->assertSet('selectedK', null)
            ->assertSet('selectedD', null);
    }

    /**
     * Branch: selectCell() pilih cell berbeda → update ke cell baru.
     */
    public function test_select_cell_pilih_cell_baru(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatKonteks();
        $this->actingAs($operator);

        Livewire::test(PetaRisiko::class, ['konteks' => $konteks])
            ->call('selectCell', 4, 3)
            ->call('selectCell', 2, 2) // berbeda → update
            ->assertSet('selectedK', 2)
            ->assertSet('selectedD', 2);
    }
}
