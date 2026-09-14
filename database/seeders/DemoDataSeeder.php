<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use App\Models\MrSasaranUpr;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the operator user to be the creator of the demo data
        $operator = User::where('email', 'operator.diskominfo@diskominfo.test')->first();

        if (!$operator) {
            $this->command->warn('Operator user not found. Demo data not seeded. Please run UserSeeder first.');
            return;
        }

        // 1. Create Layanan
        $layanan = Layanan::create([
            'unit_pelaksana' => 'Bidang E-Government',
            'bidang_bagian' => 'Bidang Aplikasi Informatika',
            'status_layanan' => 'berjalan',
            'nama_layanan' => 'Layanan Portal Satu Data Daerah',
            'deskripsi_layanan' => 'Sistem informasi yang mengintegrasikan berbagai data sektoral dari seluruh OPD untuk mendukung pengambilan keputusan.',
            'target_pengguna' => 'publik/masyarakat',
            'sifat_data' => 'terbuka',
            'interoperabilitas' => true,
            'tahun_pembuatan' => 2023,
            'is_prioritas' => true,
            'created_by' => $operator->id,
        ]);

        $this->command->info('Demo data Layanan created: ' . $layanan->nama_layanan);

        // 2. Create Konteks Manajemen Risiko
        $konteks = MrKonteks::create([
            'layanan_id' => $layanan->id,
            'nama_instansi' => 'Pemerintah Provinsi',
            'nama_upr' => 'Dinas Komunikasi dan Informatika',
            'tugas_upr' => 'Melaksanakan urusan pemerintahan bidang komunikasi dan informatika',
            'fungsi_upr' => 'Perumusan kebijakan, pelaksanaan kebijakan, evaluasi dan pelaporan',
            'tahun_penilaian' => 2025,
            'tahun_pelaksanaan' => 2025,
            'selera_risiko' => 16,
            'created_by' => $operator->id,
        ]);

        $this->command->info('Demo data MrKonteks created.');

        // 3. Create Sasaran UPR
        $sasaranUpr = MrSasaranUpr::create([
            'mr_konteks_id' => $konteks->id,
            'sasaran_upr' => 'Meningkatnya ketersediaan dan kualitas data sektoral daerah yang terintegrasi dan mudah diakses oleh publik',
            'urutan' => 1,
        ]);
        
        $this->command->info('Demo data MrSasaranUpr created.');

        // 4. Create Risiko
        $risiko = MrRisiko::create([
            'mr_konteks_id' => $konteks->id,
            'mr_sasaran_upr_id' => $sasaranUpr->id,
            'kategori_risiko' => 'Risiko Operasional',
            'sasaran_pembangunan_nasional_snapshot' => null,
            'sasaran_upr_snapshot' => $sasaranUpr->sasaran_upr,
            'indikator_kinerja_snapshot' => null,
            'kode_risiko' => 'RSK-SD-001',
            'peristiwa_risiko' => 'Terjadinya downtime server atau aplikasi Portal Satu Data yang mengakibatkan data tidak dapat diakses',
            'penyebab' => 'Pemeliharaan server yang tidak terjadwal dengan baik, atau serangan siber (DDoS)',
            'dampak' => 'Masyarakat dan pengambil keputusan tidak mendapatkan data secara real-time, menurunkan reputasi SPBE daerah',
            'area_dampak' => 'Gangguan Terhadap Layanan Organisasi',
            'level_kemungkinan' => 3, // Mungkin terjadi
            'level_dampak' => 4, // Berdampak signifikan
            'besaran_risiko' => 12, // 3 * 4
            'prioritas_risiko' => 1,
            'created_by' => $operator->id,
        ]);

        $this->command->info('Demo data MrRisiko created.');
        
        $risiko2 = MrRisiko::create([
            'mr_konteks_id' => $konteks->id,
            'mr_sasaran_upr_id' => $sasaranUpr->id,
            'kategori_risiko' => 'Risiko Kepatuhan',
            'sasaran_pembangunan_nasional_snapshot' => null,
            'sasaran_upr_snapshot' => $sasaranUpr->sasaran_upr,
            'indikator_kinerja_snapshot' => null,
            'kode_risiko' => 'RSK-SD-002',
            'peristiwa_risiko' => 'OPD tidak memperbarui data sektoral sesuai dengan jadwal yang telah ditetapkan',
            'penyebab' => 'Kurangnya kesadaran dan SOP yang mengikat terkait kewajiban update data dari produsen data',
            'dampak' => 'Data yang disajikan tidak akurat/usang, menyesatkan pengambilan keputusan',
            'area_dampak' => 'Penurunan Kinerja',
            'level_kemungkinan' => 4, // Sering terjadi
            'level_dampak' => 3, // Berdampak sedang
            'besaran_risiko' => 12, // 4 * 3
            'prioritas_risiko' => 2,
            'created_by' => $operator->id,
        ]);
        
        $this->command->info('Demo data MrRisiko (2) created.');
    }
}
