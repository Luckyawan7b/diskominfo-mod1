<?php

namespace Tests\Feature\Mpn;

use App\Models\MpnAlihPengetahuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

class AlihPengetahuanFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    public function test_metode_lainnya_required_if()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\AlihPengetahuan\AlihPengetahuanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('metode_lainnya', true)
            ->set('keterangan_lainnya', '') // Kosong, padahal metode_lainnya true
            ->call('saveAlihPengetahuan')
            ->assertHasErrors(['keterangan_lainnya']);
    }

    public function test_keterangan_lainnya_hanya_tersimpan_jika_metode_lainnya_true()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\AlihPengetahuan\AlihPengetahuanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('tanggal_kegiatan', '2026-03-01')
            ->set('metode_lainnya', false)
            ->set('keterangan_lainnya', 'Keterangan Test')
            ->set('penerima_pengetahuan', 'Sasaran')
            ->set('hasil_evaluasi', '10')
            ->call('saveAlihPengetahuan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mpn_alih_pengetahuan', [
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'keterangan_lainnya' => null // Harus null karena metode_lainnya false
        ]);
    }

    public function test_admin_mendapat_403()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->admin)
            ->test(\App\Livewire\Mpn\AlihPengetahuan\AlihPengetahuanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan, 'pengumpulan' => $data->pengumpulan])
            ->set('tanggal_kegiatan', '2026-03-01')
            ->call('saveAlihPengetahuan')
            ->assertForbidden();
    }
}
