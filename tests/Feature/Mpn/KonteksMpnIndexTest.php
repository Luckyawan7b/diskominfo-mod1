<?php

namespace Tests\Feature\Mpn;

use App\Models\Layanan;
use App\Models\MpnKonteks;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

class KonteksMpnIndexTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    public function test_mount_tanpa_active_layanan()
    {
        $roleOperator = Role::firstOrCreate(['name' => 'operator'], ['label' => 'Operator']);
        $operator = User::factory()->create(['role_id' => $roleOperator->id]);

        Livewire::actingAs($operator)
            ->test(\App\Livewire\Mpn\Konteks\KonteksMpnIndex::class)
            ->assertSet('activeLayanan', null);
    }

    public function test_create_konteks_admin_mendapat_403()
    {
        $data = $this->siapkanDataMpn();
        
        session(['active_layanan_id' => $data->layanan->id]);

        // #[Group('perlu-konfirmasi')] admin view tidak punya tombol tapi method menolak 403.
        Livewire::actingAs($data->admin)
            ->test(\App\Livewire\Mpn\Konteks\KonteksMpnIndex::class, ['layanan' => $data->layanan])
            ->call('createKonteks')
            ->assertForbidden();
    }

    public function test_create_konteks_operator_duplikat_ditolak()
    {
        $data = $this->siapkanDataMpn();
        session(['active_layanan_id' => $data->layanan->id]);

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Konteks\KonteksMpnIndex::class)
            ->set('newTahun', 2026) // Tahun yang sudah ada di siapkanDataMpn
            ->call('createKonteks')
            ->assertHasErrors(['newTahun']);
    }

    public function test_create_konteks_boundary_tahun()
    {
        $data = $this->siapkanDataMpn();
        session(['active_layanan_id' => $data->layanan->id]);

        // Kurang dari 2020 (misal 2019)
        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Konteks\KonteksMpnIndex::class)
            ->set('newTahunPelaksanaan', 2019)
            ->set('newTahun', 2019)
            ->call('createKonteks')
            ->assertHasErrors(['newTahun', 'newTahunPelaksanaan']);

        // Lebih dari 2099 (misal 2100)
        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Konteks\KonteksMpnIndex::class)
            ->set('newTahunPelaksanaan', 2100)
            ->set('newTahun', 2100)
            ->call('createKonteks')
            ->assertHasErrors(['newTahun', 'newTahunPelaksanaan']);
    }

    public function test_create_konteks_sukses()
    {
        $data = $this->siapkanDataMpn();
        session(['active_layanan_id' => $data->layanan->id]);

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Konteks\KonteksMpnIndex::class)
            ->set('newTahun', 2027)
            ->set('newTahunPelaksanaan', 2027)
            ->call('createKonteks')
            ->assertRedirect(route('konteks-mpn.form', MpnKonteks::latest('id')->first()));

        $this->assertDatabaseHas('mpn_konteks', [
            'layanan_id' => $data->layanan->id,
            'tahun_penilaian' => 2027
        ]);
    }
}
