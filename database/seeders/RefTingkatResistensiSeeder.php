<?php

namespace Database\Seeders;

use App\Models\RefTingkatResistensi;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR03, Tabel 3e. Tingkat Resistensi.
 *
 * Tabel 3e tidak terbaca di file Excel (gambar). Hanya nilai yang muncul pada data contoh
 * yang diisi; deskripsi TIDAK dikarang.
 */
class RefTingkatResistensiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Champion', 'deskripsi' => null],
            // Pada data contoh Excel tertulis "Floating  Voter" (spasi ganda); dinormalkan.
            ['nama' => 'Floating Voter', 'deskripsi' => null],
            // TODO: lengkapi tingkat resistensi lain (dan deskripsi) dari Tabel 3e.
        ];

        foreach ($data as $item) {
            RefTingkatResistensi::firstOrCreate(
                ['nama' => $item['nama']],
                ['deskripsi' => $item['deskripsi']]
            );
        }
    }
}
