<?php

namespace Tests\Unit\Mpn;

use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\MpnPemanfaatan;

class MpnPemanfaatanObserverTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    /**
     * Teknik: Branch Coverage
     * Target Coverage: 100%
     */

    public function test_rating_pengetahuan_satu_rating()
    {
        $data = $this->siapkanDataMpn();
        
        MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-01',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 4,
        ]);

        $this->assertEquals(4, $data->pengumpulan->fresh()->rating_pengetahuan);
    }

    public function test_rating_pengetahuan_beberapa_rating_dan_boundary()
    {
        $data = $this->siapkanDataMpn();
        
        // Boundary bawah (1)
        MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-01',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 1,
        ]);

        // Boundary atas (5)
        MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-02',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 5,
        ]);

        // Avg = (1+5) / 2 = 3
        $this->assertEquals(3, $data->pengumpulan->fresh()->rating_pengetahuan);
    }

    public function test_rating_pengetahuan_null_diabaikan()
    {
        $data = $this->siapkanDataMpn();
        
        MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-01',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 4,
        ]);

        // Create dengan null (jika db mengizinkan null, anggap tidak dihitung oleh avg mysql)
        // Meski tidak wajib, pastikan logic database menghandle ini
        MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-02',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => null,
        ]);

        // Avg tetap 4
        $this->assertEquals(4, $data->pengumpulan->fresh()->rating_pengetahuan);
    }

    public function test_rating_pengetahuan_dihitung_ulang_setelah_delete()
    {
        $data = $this->siapkanDataMpn();
        
        $p1 = MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-01',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 2,
        ]);

        $p2 = MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-02',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 4,
        ]);

        $this->assertEquals(3, $data->pengumpulan->fresh()->rating_pengetahuan);

        // Delete p2
        $p2->delete();

        // Avg sisa p1 = 2
        $this->assertEquals(2, $data->pengumpulan->fresh()->rating_pengetahuan);
    }

    public function test_rating_pengetahuan_menjadi_null_saat_semua_dihapus()
    {
        $data = $this->siapkanDataMpn();
        
        $p1 = MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-01',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 3,
        ]);

        $p1->delete();

        $this->assertNull($data->pengumpulan->fresh()->rating_pengetahuan);
    }

    public function test_pengumpulan_null_karena_soft_delete_tidak_error()
    {
        $data = $this->siapkanDataMpn();
        
        $p1 = MpnPemanfaatan::create([
            'mpn_pengumpulan_id' => $data->pengumpulan->id,
            'tanggal_pemanfaatan' => '2026-02-01',
            'jenis_pengguna' => 'Internal',
            'unit_pengguna' => 'Unit',
            'tujuan_pemanfaatan' => 'Tujuan',
            'rating' => 5,
        ]);

        // Soft delete pengumpulan
        $data->pengumpulan->delete();

        // Karena model pengumpulan di-soft-delete, relation p1->pengumpulan akan menjadi null
        // Observer MpnPemanfaatan::deleted harus masuk branch false dari `if ($pengumpulan)` dan tidak error
        $p1->delete();

        $this->assertTrue(true); // Memastikan tidak ada exception yang terlempar
    }
}
