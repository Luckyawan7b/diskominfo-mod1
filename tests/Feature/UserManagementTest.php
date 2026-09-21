<?php

namespace Tests\Feature;

use App\Livewire\Admin\User\UserIndex;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * UserManagementTest
 *
 * Verifikasi kolom baru (nama_penanggung_jawab, no_hp) tersimpan,
 * validasi required untuk operator, dan format no_hp divalidasi.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function operatorRoleId(): int
    {
        return Role::where('name', 'operator')->firstOrFail()->id;
    }

    private function adminRoleId(): int
    {
        return Role::where('name', 'admin')->firstOrFail()->id;
    }

    // ── Test 1: Kolom baru tersimpan untuk user operator ─────────────────────

    public function test_nama_penanggung_jawab_dan_no_hp_tersimpan_saat_create_operator(): void
    {
        $admin       = $this->admin();
        $operatorRid = $this->operatorRoleId();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Operator Baru')
            ->set('nama_penanggung_jawab', 'Dr. Budi Santoso')
            ->set('email', 'operator.baru@test.test')
            ->set('no_hp', '081234567890')
            ->set('password', 'password')
            ->set('role_id', $operatorRid)
            ->set('nama_dinas', 'Dinas Kesehatan')
            ->set('alias', 'Dinkes')
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'operator.baru@test.test')->firstOrFail();
        $this->assertSame('Dr. Budi Santoso', $user->nama_penanggung_jawab);
        $this->assertSame('081234567890', $user->no_hp);
    }

    // ── Test 2: nama_penanggung_jawab wajib diisi untuk role operator ─────────

    public function test_nama_penanggung_jawab_required_untuk_role_operator(): void
    {
        $admin       = $this->admin();
        $operatorRid = $this->operatorRoleId();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Operator Tanpa PJ')
            ->set('nama_penanggung_jawab', '')   // sengaja dikosongkan
            ->set('email', 'no.pj@test.test')
            ->set('password', 'password')
            ->set('role_id', $operatorRid)
            ->set('nama_dinas', 'Dinas Test')
            ->call('save')
            ->assertHasErrors(['nama_penanggung_jawab']);
    }

    // ── Test 3: nama_penanggung_jawab nullable untuk role admin ───────────────

    public function test_nama_penanggung_jawab_nullable_untuk_role_admin(): void
    {
        $admin    = $this->admin();
        $adminRid = $this->adminRoleId();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Admin Baru')
            ->set('nama_penanggung_jawab', '')   // boleh kosong untuk admin
            ->set('email', 'admin.baru@test.test')
            ->set('password', 'password')
            ->set('role_id', $adminRid)
            ->call('save')
            ->assertHasNoErrors(['nama_penanggung_jawab']);

        $user = User::where('email', 'admin.baru@test.test')->firstOrFail();
        $this->assertNull($user->nama_penanggung_jawab);
    }

    // ── Test 4: Format no_hp divalidasi (format Indonesia) ───────────────────

    public function test_format_no_hp_tidak_valid_menghasilkan_error(): void
    {
        $admin       = $this->admin();
        $operatorRid = $this->operatorRoleId();

        $formatTidakValid = ['12345678', '021-1234567', 'abc', '+1-800-555-1234'];

        foreach ($formatTidakValid as $hp) {
            Livewire::actingAs($admin)
                ->test(UserIndex::class)
                ->call('openCreateModal')
                ->set('name', 'Operator HP Salah')
                ->set('nama_penanggung_jawab', 'Penanggung Jawab')
                ->set('email', 'hp.salah@test.test')
                ->set('password', 'password')
                ->set('role_id', $operatorRid)
                ->set('nama_dinas', 'Dinas Test')
                ->set('no_hp', $hp)
                ->call('save')
                ->assertHasErrors(['no_hp']);
        }
    }

    // ── Test 5: Format no_hp yang valid diterima ──────────────────────────────

    public function test_format_no_hp_valid_diterima(): void
    {
        $admin       = $this->admin();
        $operatorRid = $this->operatorRoleId();

        $formatValid = ['08123456789', '628123456789', '+628123456789'];

        foreach ($formatValid as $hp) {
            Livewire::actingAs($admin)
                ->test(UserIndex::class)
                ->call('openCreateModal')
                ->set('name', 'Operator HP Benar')
                ->set('nama_penanggung_jawab', 'Penanggung Jawab')
                ->set('email', 'hp.benar.' . md5($hp) . '@test.test')
                ->set('password', 'password')
                ->set('role_id', $operatorRid)
                ->set('nama_dinas', 'Dinas Test')
                ->set('no_hp', $hp)
                ->call('save')
                ->assertHasNoErrors(['no_hp']);
        }
    }

    // ── Test 6: no_hp boleh kosong/null ──────────────────────────────────────

    public function test_no_hp_boleh_kosong(): void
    {
        $admin       = $this->admin();
        $operatorRid = $this->operatorRoleId();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Operator Tanpa HP')
            ->set('nama_penanggung_jawab', 'Penanggung Jawab')
            ->set('email', 'tanpa.hp@test.test')
            ->set('password', 'password')
            ->set('role_id', $operatorRid)
            ->set('nama_dinas', 'Dinas Test')
            ->set('no_hp', '')
            ->call('save')
            ->assertHasNoErrors(['no_hp']);

        $user = User::where('email', 'tanpa.hp@test.test')->firstOrFail();
        $this->assertNull($user->no_hp);
    }

    // ── Test 7: Edit user — data baru terbaca di modal edit ──────────────────

    public function test_edit_modal_membaca_kolom_baru_dengan_benar(): void
    {
        $admin       = $this->admin();
        $operatorRid = $this->operatorRoleId();

        $operator = User::factory()->operator()->create([
            'nama_penanggung_jawab' => 'Existing PJ',
            'no_hp'                 => '081111222333',
            'nama_dinas'            => 'Dinas Test',
        ]);

        $component = Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('openEditModal', $operator->id);

        $component->assertSet('nama_penanggung_jawab', 'Existing PJ');
        $component->assertSet('no_hp', '081111222333');
    }
}
