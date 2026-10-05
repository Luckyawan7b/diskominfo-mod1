<?php

namespace Tests\Unit\Mpn;

use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\MpnIndikatorCapaian;
use App\Models\MpnEvaluasiIndikator;
use App\Models\MpnPengumpulan;

class MpnModelTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    /**
     * Uji gap pada MpnEvaluasiIndikator
     * Target: Statement + Branch
     */
    public function test_gap_realisasi_null()
    {
        $evaluasi = new MpnEvaluasiIndikator(['realisasi' => null]);
        
        $this->assertNull($evaluasi->gap);
    }

    public function test_gap_indikatorCapaian_null()
    {
        $evaluasi = new MpnEvaluasiIndikator(['realisasi' => 10]);
        // Tanpa menyimpan, relasi indikatorCapaian adalah null

        $this->assertNull($evaluasi->gap);
    }

    public function test_gap_kondisi_to_be_null()
    {
        $data = $this->siapkanDataMpn();
        $indikator = MpnIndikatorCapaian::create([
            'mpn_konteks_id' => $data->konteks->id,
            'urutan' => 1,
            'indikator' => 'Test',
            'kondisi_to_be' => null
        ]);

        $evaluasi = MpnEvaluasiIndikator::create([
            'mpn_indikator_capaian_id' => $indikator->id,
            'realisasi' => 10
        ]);

        $this->assertNull($evaluasi->gap);
    }

    public function test_gap_valid_dengan_berbagai_hasil()
    {
        $data = $this->siapkanDataMpn();
        $indikator = MpnIndikatorCapaian::create([
            'mpn_konteks_id' => $data->konteks->id,
            'urutan' => 1,
            'indikator' => 'Test',
            'kondisi_to_be' => 10
        ]);

        // Hasil Positif
        $evaluasi = MpnEvaluasiIndikator::create([
            'mpn_indikator_capaian_id' => $indikator->id,
            'realisasi' => 15
        ]);
        $this->assertEquals(5, $evaluasi->gap);

        // Hasil Nol
        $evaluasi->update(['realisasi' => 10]);
        $this->assertEquals(0, $evaluasi->gap);

        // Hasil Negatif
        $evaluasi->update(['realisasi' => 5]);
        $this->assertEquals(-5, $evaluasi->gap);
    }

    /**
     * MpnKonteks
     */
    public function test_konteks_is_editable_by_operator_always_true()
    {
        $data = $this->siapkanDataMpn();
        $this->assertTrue($data->konteks->isEditableByOperator());
    }

    public function test_konteks_deleting_hook_with_auth()
    {
        $data = $this->siapkanDataMpn();
        
        $this->actingAs($data->operator);
        $data->konteks->delete();

        $this->assertEquals($data->operator->id, $data->konteks->fresh()->deleted_by);
    }

    public function test_konteks_deleting_hook_without_auth()
    {
        $data = $this->siapkanDataMpn();
        
        // Memastikan tidak ada user yang login
        auth()->logout();
        $data->konteks->delete();

        $this->assertNull($data->konteks->fresh()->deleted_by);
    }

    public function test_konteks_indikatorCapaian_terurut_urutan()
    {
        $data = $this->siapkanDataMpn();
        
        // Buat urutan acak
        $ind2 = MpnIndikatorCapaian::create(['mpn_konteks_id' => $data->konteks->id, 'urutan' => 2, 'indikator' => 'Dua']);
        $ind1 = MpnIndikatorCapaian::create(['mpn_konteks_id' => $data->konteks->id, 'urutan' => 1, 'indikator' => 'Satu']);
        
        $indikators = $data->konteks->indikatorCapaian;
        
        $this->assertCount(2, $indikators);
        $this->assertEquals(1, $indikators->first()->urutan);
        $this->assertEquals(2, $indikators->last()->urutan);
    }

    /**
     * MpnPengetahuan
     */
    public function test_pengetahuan_deleting_hook_with_auth()
    {
        $data = $this->siapkanDataMpn();
        
        $this->actingAs($data->operator);
        $data->pengetahuan->delete();

        $this->assertEquals($data->operator->id, $data->pengetahuan->fresh()->deleted_by);
    }

    public function test_pengetahuan_deleting_hook_without_auth()
    {
        $data = $this->siapkanDataMpn();
        
        auth()->logout();
        $data->pengetahuan->delete();

        $this->assertNull($data->pengetahuan->fresh()->deleted_by);
    }

    /**
     * Relasi Pengumpulan dan Revisi
     */
    public function test_pengumpulan_relasi_revisi()
    {
        $data = $this->siapkanDataMpn();

        $pengumpulanAwal = MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $data->pengetahuan->id,
            'id_pengetahuan' => 'MRP-TEST-2026-001',
            'nama_pengetahuan' => 'Test',
            'tanggal_pengumpulan' => '2026-01-01',
            'unit_pengumpulan' => 'Test Unit',
            'status_publikasi_simpan' => 'Draft',
            'tanggal_update_terakhir' => '2026-01-01',
        ]);

        $pengumpulanRevisi = MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $data->pengetahuan->id,
            'revisi_dari_id' => $pengumpulanAwal->id,
            'id_pengetahuan' => 'MRP-TEST-2026-001-REV1',
            'nama_pengetahuan' => 'Test Rev',
            'tanggal_pengumpulan' => '2026-01-02',
            'unit_pengumpulan' => 'Test Unit',
            'status_publikasi_simpan' => 'Draft',
            'tanggal_update_terakhir' => '2026-01-02',
        ]);

        // Tes relasi revisiDari
        $this->assertEquals($pengumpulanAwal->id, $pengumpulanRevisi->revisiDari->id);
        
        // Tes relasi revisi
        $this->assertCount(1, $pengumpulanAwal->revisi);
        $this->assertEquals($pengumpulanRevisi->id, $pengumpulanAwal->revisi->first()->id);
    }
}
