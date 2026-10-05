<?php

namespace Tests\Feature\Mpn;

use App\Models\MpnPengetahuan;
use App\Models\RefIndikatorPemdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

class PengetahuanFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    public function test_mount_dan_validasi_wajib()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks])
            ->call('save')
            ->assertHasErrors(['nama_sub_fitur', 'nama_pengetahuan', 'ref_aspek_pemdi_id', 'ref_indikator_pemdi_id']);
    }

    public function test_dependensi_referensi_aspek_indikator()
    {
        $data = $this->siapkanDataMpn();
        
        // Buat indikator tambahan untuk dipastikan terload
        RefIndikatorPemdi::create(['ref_aspek_pemdi_id' => $data->aspek->id, 'nama' => 'Indikator Baru']);

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks])
            ->set('ref_aspek_pemdi_id', $data->aspek->id)
            ->assertSet('ref_indikator_pemdi_id', null)
            ->assertCount('indikatorList', 2);
    }

    public function test_apakah_terdokumentasi_true_mereset_rencana()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks])
            ->set('apakah_terdokumentasi', false)
            ->set('target_tahun_ini', true)
            ->set('activeTab', 2)
            ->set('apakah_terdokumentasi', true) // Memanggil updatedApakahTerdokumentasi
            ->assertSet('target_tahun_ini', false)
            ->assertSet('activeTab', 1); // Reset tab
    }

    public function test_switch_tab_ditahan_jika_terdokumentasi()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks])
            ->set('apakah_terdokumentasi', true)
            ->call('switchTab', 2)
            ->assertSet('activeTab', 1); // Tetap di tab 1
    }

    public function test_simpan_baru_apakah_terdokumentasi_true()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks])
            ->set('nama_sub_fitur', 'Sub Baru')
            ->set('layanan_prioritas', true)
            ->set('nama_pengetahuan', 'Pengetahuan Baru')
            ->set('sudah_terdokumentasi', 'Belum')
            ->set('ref_aspek_pemdi_id', $data->aspek->id)
            ->set('ref_indikator_pemdi_id', $data->indikator->id)
            ->set('apakah_terdokumentasi', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mpn_pengetahuan', [
            'nama_pengetahuan' => 'Pengetahuan Baru',
            'apakah_terdokumentasi' => 1
        ]);
        
        $pengetahuan = MpnPengetahuan::where('nama_pengetahuan', 'Pengetahuan Baru')->first();
        $this->assertDatabaseMissing('mpn_rencana_dokumentasi', ['mpn_pengetahuan_id' => $pengetahuan->id]);
    }

    public function test_simpan_baru_apakah_terdokumentasi_false_observer_triggers()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks])
            ->set('nama_sub_fitur', 'Sub Rencana')
            ->set('nama_pengetahuan', 'Pengetahuan Rencana')
            ->set('ref_aspek_pemdi_id', $data->aspek->id)
            ->set('ref_indikator_pemdi_id', $data->indikator->id)
            ->set('apakah_terdokumentasi', false)
            ->set('target_tahun_ini', true)
            ->set('pemilik_pengetahuan', 'Pemilik Test')
            ->call('save')
            ->assertHasNoErrors();

        $pengetahuan = MpnPengetahuan::where('nama_pengetahuan', 'Pengetahuan Rencana')->first();
        
        // Memastikan observer MpnPengetahuanObserver dipicu dan row dibuat
        $this->assertDatabaseHas('mpn_rencana_dokumentasi', [
            'mpn_pengetahuan_id' => $pengetahuan->id,
            'target_tahun_ini' => 1,
            'pemilik_pengetahuan' => 'Pemilik Test'
        ]);
    }

    public function test_update_pengetahuan_sukses()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->operator)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks, 'pengetahuan' => $data->pengetahuan])
            ->set('nama_pengetahuan', 'Updated Pengetahuan')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mpn_pengetahuan', [
            'id' => $data->pengetahuan->id,
            'nama_pengetahuan' => 'Updated Pengetahuan'
        ]);
    }

    public function test_admin_mendapat_403()
    {
        $data = $this->siapkanDataMpn();

        Livewire::actingAs($data->admin)
            ->test(\App\Livewire\Mpn\Pengetahuan\PengetahuanForm::class, ['konteks' => $data->konteks])
            ->set('nama_pengetahuan', 'Admin test')
            ->call('save')
            ->assertForbidden();
    }
}
