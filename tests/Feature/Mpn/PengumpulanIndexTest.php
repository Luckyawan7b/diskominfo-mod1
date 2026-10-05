<?php

namespace Tests\Feature\Mpn;

use App\Models\MpnPengumpulan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

class PengumpulanIndexTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    public function test_menampilkan_daftar_pengumpulan_termasuk_revisi()
    {
        $data = $this->siapkanDataMpn();

        // Buat revisi
        $revisi = MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $data->pengetahuan->id,
            'revisi_dari_id' => $data->pengumpulan->id,
            'id_pengetahuan' => 'MRP-TEST-2026-001-REV1',
            'nama_pengetahuan' => 'Revisi 1',
            'tanggal_pengumpulan' => date('Y-m-d'),
            'unit_pengumpulan' => 'Unit Test',
            'tanggal_update_terakhir' => date('Y-m-d'),
            'status_publikasi_simpan' => 'Draft'
        ]);

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengumpulan\PengumpulanIndex::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan])
            ->assertSee($data->pengumpulan->nama_pengetahuan)
            ->assertSee('Revisi 1');
    }

    public function test_tidak_bisa_hapus_pengumpulan_jika_punya_revisi()
    {
        $data = $this->siapkanDataMpn();

        // Buat revisi
        MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $data->pengetahuan->id,
            'revisi_dari_id' => $data->pengumpulan->id,
            'id_pengetahuan' => 'MRP-TEST-2026-001-REV1',
            'nama_pengetahuan' => 'Revisi 1',
            'tanggal_pengumpulan' => date('Y-m-d'),
            'unit_pengumpulan' => 'Unit Test',
            'tanggal_update_terakhir' => date('Y-m-d'),
            'status_publikasi_simpan' => 'Draft'
        ]);

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengumpulan\PengumpulanIndex::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan])
            ->call('deletePengumpulan', $data->pengumpulan->id)
            ->assertHasNoErrors();

        // Verifikasi record masih ada di database
        $this->assertDatabaseHas('mpn_pengumpulan', [
            'id' => $data->pengumpulan->id,
            'deleted_at' => null // Belum terhapus
        ]);
    }

    public function test_hapus_pengumpulan_sukses()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengumpulan\PengumpulanIndex::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan])
            ->call('deletePengumpulan', $data->pengumpulan->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('mpn_pengumpulan', [
            'id' => $data->pengumpulan->id,
        ]);
    }

    public function test_admin_mendapat_403_saat_hapus()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->admin)
            ->test(\App\Livewire\Mpn\Pengumpulan\PengumpulanIndex::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan])
            ->call('deletePengumpulan', $data->pengumpulan->id)
            ->assertForbidden();
    }
}
