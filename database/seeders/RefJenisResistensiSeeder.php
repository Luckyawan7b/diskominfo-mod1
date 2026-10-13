<?php

namespace Database\Seeders;

use App\Models\RefJenisResistensi;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR03, Tabel 3d. Penjelasan Resistensi.
 */
class RefJenisResistensiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Penolakan secara aktif atau terbuka',
                'deskripsi' => 'Penolakan secara terbuka biasanya lebih mudah ditangani, biasanya orang akan menyatakan secara terbuka mengenai keberatan atau ketidaksetujuan terhadap perubahan.',
            ],
            [
                'nama' => 'Penolakan secara pasif',
                'deskripsi' => 'Penolakan secara pasif biasanya muncul dalam tanda-tanda tertentu, seperti sering tidak hadir dalam rapat, tidak berpartisipasi dalam rapat, tidak memenuhi komitmen, produktivitas kerja menurun.',
            ],
            [
                'nama' => 'Penolakan individual',
                'deskripsi' => 'Penolakan individu umumnya tidak akan berpengaruh terlalu besar, kecuali individu tersebut adalah pejabat atau pimpinan tertinggi Instansi Pusat atau Pemerintah Daerah.',
            ],
            [
                'nama' => 'Penolakan kolektif',
                'deskripsi' => 'Penolakan secara kolektif akan sangat besar pengaruhnya terhadap proses perubahan.',
            ],
        ];

        foreach ($data as $item) {
            RefJenisResistensi::firstOrCreate(
                ['nama' => $item['nama']],
                ['deskripsi' => $item['deskripsi']]
            );
        }
    }
}
