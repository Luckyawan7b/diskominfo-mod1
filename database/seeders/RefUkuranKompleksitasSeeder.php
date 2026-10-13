<?php

namespace Database\Seeders;

use App\Models\RefUkuranKompleksitas;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR02B, Tabel 2d. Uraian Ukuran dan Kompleksitas Perubahan Organisasi.
 */
class RefUkuranKompleksitasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Tidak Ada', 'bobot' => 0, 'definisi' => 'Tidak terdapat inisiatif perubahan atau perubahan tidak memerlukan intervensi manajemen perubahan.'],
            ['nama' => 'Rendah', 'bobot' => 1, 'definisi' => 'Perubahan berskala kecil, terbatas pada satu area/komponen, dengan kompleksitas rendah, sedikit ketergantungan, dan risiko minimal.'],
            ['nama' => 'Sedang', 'bobot' => 2, 'definisi' => 'Perubahan melibatkan beberapa komponen atau unit kerja, memiliki ketergantungan lintas fungsi, serta memerlukan koordinasi dan pengelolaan dampak yang lebih terstruktur.'],
            ['nama' => 'Tinggi', 'bobot' => 3, 'definisi' => 'Perubahan berskala besar dan kompleks, mencakup banyak komponen dan/atau unit organisasi, memiliki ketergantungan tinggi, serta memerlukan pendekatan manajemen perubahan yang komprehensif (termasuk komunikasi, pelibatan pemangku kepentingan, dan mitigasi risiko intensif).'],
        ];

        foreach ($data as $item) {
            RefUkuranKompleksitas::firstOrCreate(
                ['nama' => $item['nama']],
                ['definisi' => $item['definisi'], 'bobot' => $item['bobot']]
            );
        }
    }
}
