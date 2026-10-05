<?php

namespace Tests\Feature\Mpn;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

class PengetahuanIndexTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    public function test_menampilkan_daftar_pengetahuan()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanIndex::class, ['konteks' => $data->konteks])
            ->assertSee($data->pengetahuan->nama_pengetahuan);
    }

    public function test_hapus_pengetahuan_sukses()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanIndex::class, ['konteks' => $data->konteks])
            ->call('deletePengetahuan', $data->pengetahuan->id)
            ->assertHasNoErrors();

        // MpnPengetahuan uses SoftDeletes
        $this->assertSoftDeleted('mpn_pengetahuan', [
            'id' => $data->pengetahuan->id,
        ]);
    }

    public function test_admin_mendapat_403_saat_hapus()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->admin)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanIndex::class, ['konteks' => $data->konteks])
            ->call('deletePengetahuan', $data->pengetahuan->id)
            ->assertForbidden();
    }
}
