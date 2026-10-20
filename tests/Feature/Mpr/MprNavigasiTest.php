<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Dashboard;
use App\Livewire\Mpr\Konteks\KonteksMprIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 1: routing, akses, dan integrasi navigasi modul MPR.
 */
class MprNavigasiTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    public function test_guest_ditolak(): void
    {
        $this->get(route('konteks-mpr.index'))->assertRedirect(route('login'));
    }

    public function test_operator_tanpa_layanan_diredirect(): void
    {
        $roleOperator = \App\Models\Role::firstOrCreate(['name' => 'operator'], ['label' => 'Operator']);
        $operator = \App\Models\User::factory()->create(['role_id' => $roleOperator->id]);

        $this->actingAs($operator)
            ->get(route('konteks-mpr.index'))
            ->assertRedirect(route('layanan.create'));
    }

    public function test_operator_dapat_membuka_index(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->operator)
            ->withSession(['active_layanan_id' => $data->layanan->id])
            ->get(route('konteks-mpr.index'))
            ->assertOk()
            ->assertSee('Daftar Konteks Perubahan');
    }

    public function test_operator_dapat_membuka_hub_konteks_miliknya(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->operator)
            ->get(route('konteks-mpr.form', $data->konteks))
            ->assertOk()
            ->assertSee('Hub Konteks MPR');
    }

    public function test_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('konteks-mpr.form', $data->konteks))
            ->assertForbidden();
    }

    public function test_admin_dapat_membuka_konteks_manapun_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('konteks-mpr.form', $data->konteks))
            ->assertOk()
            ->assertSee('read-only');
    }

    public function test_admin_dapat_membuka_index_via_route_review(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('admin.review.mpr.konteks', $data->layanan))
            ->assertOk();
    }

    public function test_admin_tidak_bisa_membuka_index_tanpa_layanan(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('konteks-mpr.index'))
            ->assertForbidden();
    }

    public function test_operator_tidak_bisa_membuka_route_admin_mpr(): void
    {
        $data = $this->siapkanDataMpr();

        // RoleMiddleware mengalihkan non-admin ke daftar layanan (bukan 403).
        $this->actingAs($data->operator)
            ->get(route('admin.review.mpr.konteks', $data->layanan))
            ->assertRedirect(route('layanan.index'))
            ->assertSessionHas('error');
    }

    public function test_index_operator_hanya_menampilkan_konteks_miliknya(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();
        \App\Models\MprKonteks::create([
            'layanan_id'      => $lain->layanan->id,
            'nama_instansi'   => 'Dinas Lain Rahasia',
            'tahun_penilaian' => 2026,
            'created_by'      => $lain->operator->id,
        ]);

        Livewire::actingAs($data->operator)
            ->test(KonteksMprIndex::class)
            ->assertViewHas('konteks', fn ($k) => $k->count() === 1
                && $k->first()->id === $data->konteks->id);
    }

    public function test_dashboard_kartu_mpr_aktif_dan_terisi(): void
    {
        $data = $this->siapkanDataMpr();

        Livewire::actingAs($data->operator)
            ->test(Dashboard::class, ['layanan' => $data->layanan])
            ->assertViewHas('modules', function (array $modules) {
                $mpr = collect($modules)->firstWhere('name', 'Manajemen Perubahan');

                return $mpr['active'] === true
                    && $mpr['filled'] === true
                    && $mpr['wireAction'] === 'openModulMPR';
            });
    }

    public function test_dashboard_open_modul_mpr_set_session_dan_redirect(): void
    {
        $data = $this->siapkanDataMpr();

        Livewire::actingAs($data->operator)
            ->test(Dashboard::class, ['layanan' => $data->layanan])
            ->call('openModulMPR')
            ->assertRedirect(route('konteks-mpr.index'));

        $this->assertSame($data->layanan->id, session('active_layanan_id'));
    }

    public function test_admin_review_menampilkan_tombol_per_modul(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('admin.review.index'))
            ->assertOk()
            ->assertSee(route('admin.review.konteks', $data->layanan), false)
            ->assertSee(route('admin.review.mpn.konteks', $data->layanan), false)
            ->assertSee(route('admin.review.mpr.konteks', $data->layanan), false);
    }

    public function test_relasi_layanan_mpn_dan_mpr(): void
    {
        $data = $this->siapkanDataMpr();

        $this->assertTrue($data->layanan->mprKonteks->is($data->konteks));
        $this->assertNull($data->layanan->mpnKonteks);
    }
}
