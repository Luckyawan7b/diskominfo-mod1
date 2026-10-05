<?php

namespace Tests\Feature\Mpn;

use App\Models\Layanan;
use App\Models\MpnKonteks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

/**
 * MiddlewareAccessTest untuk MPN
 *
 * Target coverage: ≥ 90%
 */
class MpnMiddlewareAccessTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    private function buatOperatorLain()
    {
        $roleOperator = \App\Models\Role::firstOrCreate(['name' => 'operator'], ['label' => 'Operator']);
        $operator = \App\Models\User::factory()->create(['role_id' => $roleOperator->id]);
        $layanan = Layanan::factory()->create(['created_by' => $operator->id]);

        return compact('operator', 'layanan');
    }

    public function test_mpn_guest_ditolak()
    {
        $this->get(route('konteks-mpn.index'))
             ->assertRedirect(route('login'));
    }

    public function test_mpn_admin_lolos_ke_route_admin()
    {
        $data = $this->siapkanDataMpn();

        $this->actingAs($data->admin)
             ->get(route('admin.review.mpn.konteks', $data->layanan))
             ->assertStatus(200);
    }

    public function test_mpn_has_layanan_operator_tanpa_layanan_diredirect()
    {
        // Setup user tanpa layanan
        $roleOperator = \App\Models\Role::firstOrCreate(['name' => 'operator'], ['label' => 'Operator']);
        $operatorTanpaLayanan = \App\Models\User::factory()->create(['role_id' => $roleOperator->id]);

        $this->actingAs($operatorTanpaLayanan)
             ->get(route('konteks-mpn.index'))
             ->assertRedirect(route('layanan.create'));
    }

    public function test_mpn_operator_dengan_layanan_lolos_ke_index()
    {
        $data = $this->siapkanDataMpn();

        $this->actingAs($data->operator)
             ->get(route('konteks-mpn.index'))
             ->assertStatus(200);
    }

    public function test_mpn_konteks_accessible_admin_akses_konteks_manapun()
    {
        $data = $this->siapkanDataMpn();

        $this->actingAs($data->admin)
             ->get(route('konteks-mpn.form', $data->konteks))
             ->assertStatus(200);
    }

    public function test_mpn_konteks_accessible_operator_akses_konteks_milik_sendiri()
    {
        $data = $this->siapkanDataMpn();

        $this->actingAs($data->operator)
             ->get(route('konteks-mpn.form', $data->konteks))
             ->assertStatus(200);
    }

    public function test_mpn_konteks_accessible_operator_ditolak_akses_konteks_lain()
    {
        $data = $this->siapkanDataMpn();
        $lain = $this->buatOperatorLain();

        // Operator lain akses konteks milik $data->operator
        $this->actingAs($lain['operator'])
             ->get(route('konteks-mpn.form', $data->konteks))
             ->assertStatus(403);
    }
}
