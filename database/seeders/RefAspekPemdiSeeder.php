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
            'Tata Kelola dan Manajemen',
            'Penyelenggara',
            'Data',
            'Keamanan Siber',
            'Teknologi Digital',
            'Keterpaduan Layanan Digital Pemerintah',
            'Kepuasan Pengguna Layanan Digital Pemerintah',
        ];

        foreach ($aspek as $nama) {
            RefAspekPemdi::firstOrCreate(['nama' => $nama]);
        }
    }
}
