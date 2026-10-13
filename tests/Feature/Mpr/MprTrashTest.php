<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Admin\TrashIndex;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MprTrashTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_tab_mpr_menampilkan_data_terhapus_dan_restore_berhasil(): void
    {
        $risiko = MprAnalisisRisiko::factory()->create(['kode_risiko' => 'R-PR-777', 'risiko' => 'Risiko untuk trash']);
        $perubahan = $risiko->perubahan;
        $konteks = $perubahan->konteks;

        $this->actingAs($konteks->creator);
        $risiko->delete();
        $perubahan->delete();
        $konteks->delete();

        $this->actingAs($this->admin());

        Livewire::test(TrashIndex::class)
            ->set('activeTab', 'mpr_analisis_risiko')->assertSee('R-PR-777')
            ->set('activeTab', 'mpr_perubahan')->assertSee($perubahan->id_perubahan)
            ->set('activeTab', 'mpr_konteks')->assertSee((string) $konteks->tahun_penilaian)
            ->call('restore', 'mpr_perubahan', $perubahan->id);

        $this->assertNull(MprPerubahan::find($perubahan->id)->deleted_at);
        $this->assertNotNull(MprKonteks::withTrashed()->find($konteks->id)->deleted_at);
    }

    public function test_operator_tidak_bisa_restore_data_mpr(): void
    {
        $perubahan = MprPerubahan::factory()->create();
        $operator = $perubahan->creator;
        $this->actingAs($operator);
        $perubahan->delete();

        Livewire::test(TrashIndex::class)->assertForbidden();
    }

    public function test_force_delete_perubahan_menghapus_anak_lewat_cascade(): void
    {
        $risiko = MprAnalisisRisiko::factory()->create();
        $perubahan = $risiko->perubahan;
        $this->actingAs($perubahan->creator);
        $perubahan->delete();

        $this->actingAs($this->admin());
        $nama = $perubahan->id_perubahan . ' - ' . \Illuminate\Support\Str::limit($perubahan->detail_perubahan, 50);

        Livewire::test(TrashIndex::class)
            ->call('openForceDeleteModal', 'mpr_perubahan', $perubahan->id, $nama)
            ->set('forceDeleteInput', $nama)
            ->call('executeForceDelete');

        $this->assertNull(MprPerubahan::withTrashed()->find($perubahan->id));
        $this->assertSame(0, MprAnalisisRisiko::withTrashed()->where('mpr_perubahan_id', $perubahan->id)->count());
    }
}
