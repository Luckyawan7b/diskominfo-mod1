<?php

namespace Tests\Feature\Mpn;

use App\Models\MpnPemanfaatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

class PemanfaatanFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    public function test_validasi_wajib_dan_boundary_rating()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pemanfaatan\PemanfaatanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('tanggal_pemanfaatan', '')
            ->set('jenis_pengguna', '')
            ->set('unit_pengguna', '')
            ->set('tujuan_pemanfaatan', '')
            ->set('rating', null)
            ->call('savePemanfaatan')
            ->assertHasErrors(['tanggal_pemanfaatan', 'jenis_pengguna', 'unit_pengguna', 'tujuan_pemanfaatan']);

        // Boundary bawah luar
        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pemanfaatan\PemanfaatanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('tanggal_pemanfaatan', '2026-02-01')
            ->set('jenis_pengguna', 'Internal')
            ->set('unit_pengguna', 'Unit')
            ->set('tujuan_pemanfaatan', 'Tujuan')
            ->set('rating', 0)
            ->call('savePemanfaatan')
            ->assertHasErrors(['rating']);

        // Boundary atas luar
        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pemanfaatan\PemanfaatanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('rating', 6)
            ->call('savePemanfaatan')
            ->assertHasErrors(['rating']);
    }

    public function test_simpan_sukses_dan_rating_pengetahuan_terhitung()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pemanfaatan\PemanfaatanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('tanggal_pemanfaatan', '2026-02-01')
            ->set('jenis_pengguna', 'Internal')
            ->set('unit_pengguna', 'Unit')
            ->set('tujuan_pemanfaatan', 'Tujuan')
            ->set('rating', 5)
            ->call('savePemanfaatan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mpn_pemanfaatan', [
            'rating' => 5
        ]);

        $this->assertEquals(5, $data->pengumpulan->fresh()->rating_pengetahuan);
    }

    public function test_admin_mendapat_403()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->admin)
            ->test(\App\Livewire\Mpn\Pemanfaatan\PemanfaatanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('tanggal_pemanfaatan', '2026-02-01')
            ->set('jenis_pengguna', 'Internal')
            ->set('unit_pengguna', 'Unit')
            ->set('tujuan_pemanfaatan', 'Tujuan')
            ->set('rating', 5)
            ->call('savePemanfaatan')
            ->assertForbidden();
    }

}
