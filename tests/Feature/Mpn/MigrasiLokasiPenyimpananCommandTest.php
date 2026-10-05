<?php

namespace Tests\Feature\Mpn;

use App\Models\MpnPengumpulan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\MembuatSkafoldMpn;

class MigrasiLokasiPenyimpananCommandTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpn;

    public function test_dry_run_menampilkan_data_tanpa_mutasi()
    {
        $data = $this->siapkanDataMpn();
        
        // Buat data dengan format lama
        MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $data->pengetahuan->id,
            'id_pengetahuan' => 'MRP-TEST-2026-001',
            'nama_pengetahuan' => 'Test',
            'tanggal_pengumpulan' => date('Y-m-d'),
            'unit_pengumpulan' => 'Unit Test',
            'tanggal_update_terakhir' => date('Y-m-d'),
            'status_publikasi_simpan' => 'Draft',
            'lokasi_penyimpanan_lain' => 'Lemari A2', // Tidak resmi
        ]);

        $this->artisan('mpn:migrasi-lokasi-penyimpanan', ['--dry-run' => true])
            ->expectsOutputToContain('Mode DRY-RUN')
            ->expectsOutputToContain('Ditemukan 1 record yang akan dimigrasi:')
            ->expectsOutputToContain('Dry-run selesai')
            ->assertExitCode(0);

        // Data tidak berubah
        $this->assertDatabaseHas('mpn_pengumpulan', [
            'lokasi_penyimpanan_lain' => 'Lemari A2',
            'keterangan_lokasi_lainnya' => null,
        ]);
    }

    public function test_migrasi_berhasil_mengubah_data_yang_tidak_resmi()
    {
        $data = $this->siapkanDataMpn();
        
        // Buat data dengan format lama
        $pengumpulan = MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $data->pengetahuan->id,
            'id_pengetahuan' => 'MRP-TEST-2026-002',
            'nama_pengetahuan' => 'Test',
            'tanggal_pengumpulan' => date('Y-m-d'),
            'unit_pengumpulan' => 'Unit Test',
            'tanggal_update_terakhir' => date('Y-m-d'),
            'status_publikasi_simpan' => 'Draft',
            'lokasi_penyimpanan_lain' => 'Ruang Arsip', // Tidak resmi
        ]);

        $this->artisan('mpn:migrasi-lokasi-penyimpanan')
            ->expectsQuestion('Lanjutkan migrasi 1 record ke format baru?', 'yes')
            ->expectsOutputToContain('Migrasi selesai: 1 record berhasil, 0 gagal.')
            ->assertExitCode(0);

        // Data berubah
        $this->assertDatabaseHas('mpn_pengumpulan', [
            'id' => $pengumpulan->id,
            'lokasi_penyimpanan_lain' => 'Lainnya',
            'keterangan_lokasi_lainnya' => 'Ruang Arsip',
        ]);
    }

    public function test_migrasi_tidak_mengubah_data_yang_sudah_resmi()
    {
        $data = $this->siapkanDataMpn();
        
        // Buat data dengan format resmi
        MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $data->pengetahuan->id,
            'id_pengetahuan' => 'MRP-TEST-2026-003',
            'nama_pengetahuan' => 'Test',
            'tanggal_pengumpulan' => date('Y-m-d'),
            'unit_pengumpulan' => 'Unit Test',
            'tanggal_update_terakhir' => date('Y-m-d'),
            'status_publikasi_simpan' => 'Draft',
            'lokasi_penyimpanan_lain' => 'SIMPAN', // Resmi
        ]);

        $this->artisan('mpn:migrasi-lokasi-penyimpanan')
            ->expectsOutput('Tidak ada data lama yang perlu dimigrasi.')
            ->assertExitCode(0);

        // Data tetap
        $this->assertDatabaseHas('mpn_pengumpulan', [
            'lokasi_penyimpanan_lain' => 'SIMPAN',
            'keterangan_lokasi_lainnya' => null,
        ]);
    }
}
