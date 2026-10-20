<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use App\Models\MrSasaranUpr;
use App\Models\RefAreaDampakOrg;
use App\Models\RefAspekPemdi;
use App\Models\RefJenisResistensi;
use App\Models\RefKategoriStakeholder;
use App\Models\RefKlasifikasiPerubahan;
use App\Models\RefLingkupPerubahan;
use App\Models\RefStrategiIt;
use App\Models\RefStrategiOrganisasi;
use App\Models\RefTingkatResistensi;
use App\Models\RefUkuranKompleksitas;
use App\Models\User;
use App\Services\MprKesimpulanOrganisasi;
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

        // 5. Create Konteks & Perubahan MPR (Manajemen Perubahan)
        $mprKonteks = MprKonteks::create([
            'layanan_id'          => $layanan->id,
            'nama_instansi'       => $operator->nama_dinas ?? 'Dinas Komunikasi dan Informatika',
            'nama_opd_terkait'    => 'Bappeda, Disdukcapil',
            'tahun_penilaian'     => 2025,
            'periode_perencanaan' => 'Jan–Des 2025',
            'created_by'          => $operator->id,
        ]);

        // F0: Analisis Prioritas
        $prioritas = $mprKonteks->analisisPrioritas()->create([
            'kondisi_perlu_berubah' => 'Sinkronisasi data sektoral antar OPD masih dilakukan manual lewat unggah berkas bulanan.',
            'as_is'                 => 'Unggah spreadsheet manual setiap akhir bulan.',
            'to_be'                 => 'Integrasi API otomatis real-time antar sistem OPD dengan Portal Satu Data.',
            'prioritas'             => 1,
            'urutan'                => 0,
        ]);

        $aspekPertama = RefAspekPemdi::with('indikatorPemdi')->first();
        if ($aspekPertama) {
            $prioritas->aspek()->sync([$aspekPertama->id]);
            $indIds = $aspekPertama->indikatorPemdi->take(2)->pluck('id')->all();
            if ($indIds !== []) {
                $prioritas->indikator()->sync($indIds);
            }
        }

        // F1: Permohonan Perubahan (Normal + Teknis & Organisasi)
        $klasifikasiNormal = RefKlasifikasiPerubahan::where('nama', 'Normal')->first();
        $lingkupTno = RefLingkupPerubahan::where('nama', 'Teknis & Organisasi')->first();

        if ($klasifikasiNormal && $lingkupTno) {
            $perubahan = MprPerubahan::create([
                'mpr_konteks_id'               => $mprKonteks->id,
                'mpr_analisis_prioritas_id'    => $prioritas->id,
                'id_perubahan'                 => 'MPR-DISKOMINFO-2025-001',
                'nama_layanan_digital'         => 'Portal Satu Data Daerah v2 (Integrasi API)',
                'is_layanan_prioritas'         => true,
                'detail_perubahan'             => 'Pembaruan arsitektur Portal Satu Data untuk mendukung pipeline API otomatis dari seluruh OPD.',
                'unit_pemohon'                 => 'Bidang E-Government',
                'ref_klasifikasi_perubahan_id' => $klasifikasiNormal->id,
                'ref_lingkup_perubahan_id'     => $lingkupTno->id,
                'created_by'                   => $operator->id,
            ]);

            // F2A: Analisis Risiko (1 risiko baru, 1 dari MR)
            MprAnalisisRisiko::create([
                'mpr_perubahan_id'           => $perubahan->id,
                'layanan_id'                 => $layanan->id,
                'kode_risiko'                => 'R-PR-001',
                'sumber_risiko'              => 'baru',
                'risiko'                     => 'Ketidakcocokan format payload API dari sistem OPD lama saat cutover.',
                'penyebab'                   => 'Standar metadata belum seragam di semua OPD.',
                'level_kemungkinan'          => 3,
                'level_dampak'               => 3,
                'perencanaan_keberlangsungan' => 'Menyediakan adapter kompatibilitas dan masa transisi dual-input selama 1 bulan.',
                'created_by'                 => $operator->id,
            ]);

            MprAnalisisRisiko::create([
                'mpr_perubahan_id'           => $perubahan->id,
                'layanan_id'                 => $layanan->id,
                'kode_risiko'                => 'R-PR-002',
                'sumber_risiko'              => 'dari_mr',
                'mr_risiko_id'               => $risiko->id,
                'risiko'                     => $risiko->peristiwa_risiko,
                'penyebab'                   => $risiko->penyebab,
                'level_kemungkinan'          => $risiko->level_kemungkinan,
                'level_dampak'               => $risiko->level_dampak,
                'perencanaan_keberlangsungan' => 'Deployment blue-green di luar jam kerja dengan replikasi database aktif.',
                'created_by'                 => $operator->id,
            ]);

            // F2B: Analisis Organisasi
            $analisisOrg = $perubahan->analisisOrganisasi()->create([]);
            $ukuranSedang = RefUkuranKompleksitas::where('nama', 'Sedang')->first();
            foreach (RefAreaDampakOrg::with('indikator')->orderBy('urutan')->get() as $area) {
                $orgArea = $analisisOrg->areas()->create([
                    'ref_area_dampak_org_id'    => $area->id,
                    'ref_ukuran_kompleksitas_id' => $ukuranSedang?->id,
                ]);
                $indPertama = $area->indikator->first();
                if ($indPertama) {
                    $orgArea->indikator()->sync([$indPertama->id]);
                }
            }
            app(MprKesimpulanOrganisasi::class)->terapkan($analisisOrg);

            // Persetujuan
            foreach (['analisis_teknis', 'analisis_organisasi', 'pelaksanaan'] as $tahap) {
                $perubahan->persetujuan()->create([
                    'tahap'         => $tahap,
                    'status'        => 'Disetujui',
                    'pic_penyetuju' => 'Kepala Dinas Komunikasi dan Informatika',
                    'tanggal'       => '2025-03-10',
                    'catatan'       => 'Disetujui untuk dilaksanakan sesuai jadwal.',
                ]);
            }

            // F3: Perencanaan
            $perubahan->implementasi()->create([
                'tanggal_mulai'    => '2025-04-01',
                'tanggal_selesai'  => '2025-06-30',
                'jangka_waktu'     => '3 Bulan',
                'pic_implementasi' => 'Kabid E-Government',
            ]);

            $perubahan->strategi()->create([
                'ref_strategi_it_id'         => RefStrategiIt::where('nama', 'Blue - Green')->value('id'),
                'deskripsi_strategi_it'      => 'Menyiapkan environment v2 berdampingan dengan v1 sebelum pengalihan trafik.',
                'rollback_plan'              => 'Mengembalikan routing load balancer ke cluster v1 bila error rate > 1%.',
                'ref_strategi_organisasi_id' => RefStrategiOrganisasi::where('nama', 'Pendekatan Empiris-Rasional')->value('id'),
                'detail_strategi_organisasi' => 'Pendampingan teknis dan uji coba bersama walidata OPD.',
            ]);

            $perubahan->sumberDaya()->create([
                'jenis'            => 'TIK',
                'deskripsi'        => 'Penambahan kapasitas node API Gateway dan penyimpanan cadangan',
                'alokasi_anggaran' => 45000000,
                'skema_pembiayaan' => 'APBD Diskominfo 2025',
                'pic'              => 'Subbag Perencanaan & Keuangan',
                'urutan'           => 0,
            ]);

            $perubahan->indikatorKeberhasilan()->create([
                'indikator' => 'Persentase OPD yang terhubung melalui API otomatis',
                'satuan'    => '%',
                'target'    => 90,
                'realisasi' => 95,
                'urutan'    => 0,
            ]);

            $perubahan->stakeholder()->create([
                'nama_stakeholder'            => 'Walidata & Produsen Data OPD',
                'ref_kategori_stakeholder_id' => RefKategoriStakeholder::where('nama', 'Pemangku kepentingan utama')->value('id'),
                'ref_jenis_resistensi_id'     => RefJenisResistensi::where('nama', 'Penolakan secara pasif')->value('id'),
                'ref_tingkat_resistensi_id'   => RefTingkatResistensi::where('nama', 'Floating Voter')->value('id'),
            ]);

            $perubahan->komunikasi()->create([
                'informasi'          => 'Sosialisasi spesifikasi teknis API dan jadwal migrasi Portal Satu Data v2',
                'pengirim'           => 'Diskominfo',
                'target_audiens'     => 'Pengelola Data Seluruh OPD',
                'media_komunikasi'   => 'Surat Edaran & Forum Walidata',
                'periode_komunikasi' => 'Minggu ke-1 April 2025',
            ]);

            $perubahan->pelatihan()->create([
                'nama_pelatihan'      => 'Bimtek Integrasi API Portal Satu Data v2',
                'target_peserta'      => 'Admin Teknis OPD',
                'target_jumlah'       => 38,
                'metode_pelatihan'    => 'Workshop Tatap Muka',
                'tanggal_pelaksanaan' => '2025-04-15',
            ]);

            // F4: Logbook Pelaksanaan
            $perubahan->logbook()->create([
                'unit_pelaksana'   => 'Tim Infrastruktur & Integrasi Diskominfo',
                'pic_pelaksana'    => 'Ketua Tim Integrasi Aplikasi',
                'tanggal_mulai'    => '2025-04-01',
                'tanggal_selesai'  => '2025-06-25',
                'catatan'          => 'Migrasi selesai 5 hari lebih cepat dari jadwal tanpa downtime.',
                'bukti_url'        => 'https://satudata.diskominfo.test/rilis-v2',
                'status_perubahan' => 'Berhasil',
            ]);

            // F5: Evaluasi
            $perubahan->evaluasi()->create([
                'hasil'              => 'Berhasil',
                'lesson_learned'     => 'Penyediaan sandbox pengujian sejak bulan pertama mempercepat kesiapan teknis OPD.',
                'rekomendasi_solusi' => 'Standar API ini dapat direplikasi untuk integrasi layanan kepegawaian dan perencanaan.',
            ]);

            $this->command->info('Demo data MprKonteks & MprPerubahan created.');
        }
    }
}
