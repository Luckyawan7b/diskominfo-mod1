<?php

namespace Database\Seeders;

use App\Models\RefAspekPemdi;
use Illuminate\Database\Seeder;

class RefAspekPemdiSeeder extends Seeder
{
    public function run(): void
    {
        // TODO: Sesuaikan dengan 7 Aspek di Tabel 1b
        $aspek = [
            'Kebijakan Internal Terkait Tata Kelola SPBE',
            'Perencanaan Strategis SPBE',
            'Teknologi Informasi dan Komunikasi',
            'Penyelenggaraan SPBE',
            'Penerapan Manajemen SPBE',
            'Audit TIK',
            'Layanan SPBE',
        ];

        foreach ($aspek as $nama) {
            RefAspekPemdi::firstOrCreate(['nama' => $nama]);
        }
    }
}
