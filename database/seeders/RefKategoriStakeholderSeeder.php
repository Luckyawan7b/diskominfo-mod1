<?php

namespace Database\Seeders;

use App\Models\RefKategoriStakeholder;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR03, Tabel 3c. Kategori Pemangku Kepentingan.
 */
class RefKategoriStakeholderSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Pemangku kepentingan utama',
                'deskripsi' => 'Pemangku kepentingan utama adalah pihak yang memiliki kaitan kepentingan secara langsung dengan suatu kebijakan, program, dan proyek. Mereka harus ditempatkan sebagai penentu utama dalam proses pengambilan keputusan.',
            ],
            [
                'nama' => 'Pemangku kepentingan pendukung',
                'deskripsi' => 'Pemangku kepentingan pendukung adalah pihak yang tidak memiliki kaitan kepentingan secara langsung terhadap suatu kebijakan, program, dan proyek, tetapi memiliki kepedulian dan keprihatinan sehingga mereka turut bersuara dan berpengaruh terhadap sikap masyarakat dan keputusan pemerintah.',
            ],
            [
                'nama' => 'Pemangku kepentingan kunci',
                'deskripsi' => 'Pemangku kepentingan kunci adalah pihak yang memiliki kewenangan secara resmi dalam hal pengambilan keputusan. Pemangku kepentingan kunci yang dimaksud adalah pengambil keputusan di Instansi Pusat dan Pemerintah Daerah.',
            ],
        ];

        foreach ($data as $item) {
            RefKategoriStakeholder::firstOrCreate(
                ['nama' => $item['nama']],
                ['deskripsi' => $item['deskripsi']]
            );
        }
    }
}
