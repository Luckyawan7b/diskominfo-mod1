<?php

namespace Tests\Unit\Mpn;

use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\MpnRencanaDokumentasi;

class MpnPengetahuanObserverTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    /**
     * Teknik: Condition/MC-DC
     * Target Coverage: 100% Statement, 100% Branch
     */

    public function test_saved_false_membuat_rencana()
    {
        $data = $this->siapkanDataMpn();
        $pengetahuan = $data->pengetahuan;

        // Path: apakah_terdokumentasi = false
        $pengetahuan->apakah_terdokumentasi = false;
        $pengetahuan->save();

        $this->assertDatabaseHas('mpn_rencana_dokumentasi', [
            'mpn_pengetahuan_id' => $pengetahuan->id,
        ]);
    }

    public function test_saved_nol_membuat_rencana()
    {
        $data = $this->siapkanDataMpn();
        $pengetahuan = $data->pengetahuan;

        // Path: apakah_terdokumentasi = 0
        $pengetahuan->apakah_terdokumentasi = 0;
        $pengetahuan->save();

        $this->assertDatabaseHas('mpn_rencana_dokumentasi', [
            'mpn_pengetahuan_id' => $pengetahuan->id,
        ]);
    }

    public function test_saved_string_nol_membuat_rencana()
    {
        $data = $this->siapkanDataMpn();
        $pengetahuan = $data->pengetahuan;

        // Path: apakah_terdokumentasi = '0'
        $pengetahuan->apakah_terdokumentasi = '0';
        $pengetahuan->save();

        $this->assertDatabaseHas('mpn_rencana_dokumentasi', [
            'mpn_pengetahuan_id' => $pengetahuan->id,
        ]);
    }

    public function test_saved_true_menghapus_rencana()
    {
        $data = $this->siapkanDataMpn();
        $pengetahuan = $data->pengetahuan;

        // Buat dulu (false)
        $pengetahuan->apakah_terdokumentasi = false;
        $pengetahuan->save();
        $this->assertDatabaseCount('mpn_rencana_dokumentasi', 1);

        // Path: apakah_terdokumentasi = true
        $pengetahuan->apakah_terdokumentasi = true;
        $pengetahuan->save();

        $this->assertDatabaseMissing('mpn_rencana_dokumentasi', [
            'mpn_pengetahuan_id' => $pengetahuan->id,
        ]);
    }

    public function test_saved_null_menghapus_rencana()
    {
        $data = $this->siapkanDataMpn();
        $pengetahuan = $data->pengetahuan;

        // Buat dulu (false)
        $pengetahuan->apakah_terdokumentasi = false;
        $pengetahuan->save();

        // #[Group('perlu-konfirmasi')] Perilaku saat null adalah menghapus (dianggap terdokumentasi)
        $pengetahuan->apakah_terdokumentasi = null;
        $pengetahuan->save();

        $this->assertDatabaseMissing('mpn_rencana_dokumentasi', [
            'mpn_pengetahuan_id' => $pengetahuan->id,
        ]);
    }

    public function test_saved_idempotensi_firstOrCreate()
    {
        $data = $this->siapkanDataMpn();
        $pengetahuan = $data->pengetahuan;

        $pengetahuan->apakah_terdokumentasi = false;
        $pengetahuan->save(); // 1
        $pengetahuan->save(); // 2

        $this->assertDatabaseCount('mpn_rencana_dokumentasi', 1);
    }

    public function test_transisi_ya_tidak_ya()
    {
        $data = $this->siapkanDataMpn();
        $pengetahuan = $data->pengetahuan;

        // Ya (true)
        $pengetahuan->apakah_terdokumentasi = true;
        $pengetahuan->save();
        $this->assertDatabaseCount('mpn_rencana_dokumentasi', 0);

        // Tidak (false)
        $pengetahuan->apakah_terdokumentasi = false;
        $pengetahuan->save();
        $this->assertDatabaseCount('mpn_rencana_dokumentasi', 1);

        // Ya (true)
        $pengetahuan->apakah_terdokumentasi = true;
        $pengetahuan->save();
        $this->assertDatabaseCount('mpn_rencana_dokumentasi', 0);
    }
}
