<?php

namespace Tests\Feature;

use App\Livewire\Pemantauan\PemantauanForm;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * PemantauanTest
 *
 * Modul sekunder — simpan 1 record pemantauan, branch admin abort.
 * Target: ≥ 50%
 */
class PemantauanTest extends TestCase
{
    use RefreshDatabase;

    private function buatSetup(): array
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
        $risiko = MrRisiko::create([
            'mr_konteks_id'   => $konteks->id,
            'kode_risiko'     => 'TST-R-01',
            'peristiwa_risiko'=> 'Risiko pemantauan test',
            'created_by'      => $operator->id,
        ]);

        return compact('operator', 'konteks', 'risiko');
    }

    /**
     * Statement: savePemantauan() menyimpan 1 record pemantauan.
     */
    public function test_simpan_satu_record_pemantauan(): void
    {
        ['operator' => $operator, 'konteks' => $konteks, 'risiko' => $risiko] = $this->buatSetup();
        $this->actingAs($operator);

        Livewire::test(PemantauanForm::class, ['konteks' => $konteks])
            ->set('selectedRisikoId', $risiko->id)
            ->set('periode', 'semester_1')
            ->set('tahun', date('Y'))
            ->set('hasil_pelaksanaan', 'Monitoring berjalan sesuai rencana')
            ->call('savePemantauan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mr_pemantauan_risiko', [
            'mr_risiko_id'      => $risiko->id,
            'periode'           => 'semester_1',
            'hasil_pelaksanaan' => 'Monitoring berjalan sesuai rencana',
        ]);
    }

    /**
     * Branch: admin tidak bisa savePemantauan → abort 403.
     */
    public function test_admin_tidak_bisa_save_pemantauan(): void
    {
        ['konteks' => $konteks, 'risiko' => $risiko] = $this->buatSetup();
        $admin = \App\Models\User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(PemantauanForm::class, ['konteks' => $konteks])
            ->set('selectedRisikoId', $risiko->id)
            ->set('hasil_pelaksanaan', 'Ubah ilegal')
            ->call('savePemantauan')
            ->assertStatus(403);
    }
}
