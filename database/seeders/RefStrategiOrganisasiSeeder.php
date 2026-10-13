<?php

namespace Database\Seeders;

use App\Models\RefKegiatanStrategiOrganisasi;
use App\Models\RefStrategiOrganisasi;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR03, Tabel 3a. Pilihan Strategi Organisasi Manajemen Perubahan PemDi & Kegiatannya.
 * Catatan: baris "8.0 Serverless" pada kolom yang sama di Excel adalah sisipan Tabel 3b, bukan kegiatan 3a.
 */
class RefStrategiOrganisasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Pendekatan Empiris-Rasional' => [
                'Peringatan awal dapat memberikan waktu persiapan kepada pegawai',
                'Prediksi pola perilaku pegawai dapat dilakukan',
                'Agen perubahan berperan dalam meyakinkan bahwa perubahan bermanfaat bagi pegawai',
                'Informasi yang tepat waktu dan konsisten dapat selalu disampaikan ke pegawai',
                'Peran baru dalam pekerjaan setelah perubahan ditetapkan perlu diketahui dan dipahami oleh pegawai',
                'Argumen yang logis melandasi adanya perubahan',
                'Perubahan yang penting dapat didukung oleh semua pegawai',
                'Perubahan permanen dapat diciptakan dengan hubungan, sikap, nilai, dan kepiawaian di dalam instansi',
            ],
            'Pendekatan Reedukasi-Normatif' => [
                'Pandangan manusia sebagai makhluk sosial yang perilakunya dapat diubah melalui budaya kelompok dapat melandasi perubahan',
                'Nilai dan kepercayaan kelompok dapat memperkuat penerimaan terhadap perubahan',
                'Efektivitas perubahan dapat dicapai melalui adopsi budaya kelompok',
                'Pendefinisian ulang dan penafsiran norma dan nilai dapat mendukung perubahan',
                'Komitmen perubahan yang terus dijaga dapat mendorong perubahan',
                'Agen perubahan dapat merekonstruksi nilai perorangan untuk menyelesaikan konflik',
                'Bahasa, budaya, norma, dan kebiasaan dapat mempengaruhi pegawai',
                'Kolaborasi dapat membujuk pegawai untuk melakukan perubahan',
            ],
            'Pendekatan Pemaksaan-Kekuasaan' => [
                'Kekuatan pada unit terkait manajemen internal mendorong perubahan',
                'Pemberian hadiah mendukung kepatuhan terhadap perubahan',
                'Pemberian penghargaan mendukung perubahan dapat diterima',
                'Pemberian ancaman dapat mendukung perubahan',
                'Kepatuhan kepada pemegang kekuasaan melandasi perubahan',
                'Otoritas dan pengaruh kebijakan dapat memaksakan kehendak atasan kepada pegawai',
                'Aturan yang telah berlaku dan ditetapkan cenderung dipatuhi oleh pegawai',
                'Pengenaan sanksi dapat mendukung perubahan',
            ],
            'Pendekatan Adaptif terhadap Lingkungan' => [
                'Kecepatan dalam penerapan struktur perubahan yang baru memudahkan agen perubahan dalam menghapus cara lama dan membuat perubahan yang permanen',
                'Kecepatan dalam asimilasi proses perubahan memudahkan pegawai beradaptasi dengan perubahan',
                'Perubahan akan diikuti pegawai apabila tidak ada pilihan lain selain berubah',
                'Pencapaian visi dapat didukung dengan pembentukan komunitas baru',
                'Integritas terhadap instansi mendorong pegawai untuk melakukan perubahan',
                'Kesadaran individu untuk melakukan perubahan mendukung perubahan secara menyeluruh',
                'Pengaruh lingkungan eksternal dalam menekan perubahan individu mendukung perubahan dapat diterima',
                'Pembentukan kelompok baru mendukung perubahan dapat diterima',
            ],
        ];

        foreach ($data as $nama => $kegiatan) {
            // Excel tidak menyediakan deskripsi per pendekatan; dibiarkan null.
            $strategi = RefStrategiOrganisasi::firstOrCreate(['nama' => $nama]);

            foreach ($kegiatan as $i => $teks) {
                RefKegiatanStrategiOrganisasi::firstOrCreate(
                    ['ref_strategi_organisasi_id' => $strategi->id, 'no' => $i + 1],
                    ['teks' => $teks]
                );
            }
        }
    }
}
