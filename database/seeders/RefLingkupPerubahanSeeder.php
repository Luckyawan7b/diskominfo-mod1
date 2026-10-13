<?php

namespace Database\Seeders;

use App\Models\RefLingkupPerubahan;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR01, Tabel 1b. Lingkup Perubahan.
 */
class RefLingkupPerubahanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Teknis',
                'deskripsi' => 'Perubahan yang terjadi pada aspek teknis atau komponen layanan, seperti aplikasi, sistem, infrastruktur, atau konfigurasi teknologi yang menjadi Prioritas',
                'butuh_analisis_teknis' => true,
                'butuh_analisis_organisasi' => false,
            ],
            [
                'nama' => 'Organisasi',
                'deskripsi' => 'Perubahan yang berkaitan dengan aspek non-teknis, seperti struktur organisasi, proses bisnis, kebijakan, peran dan tanggung jawab, serta tata kelola layanan yang menjadi Prioritas',
                'butuh_analisis_teknis' => false,
                'butuh_analisis_organisasi' => true,
            ],
            [
                'nama' => 'Teknis & Organisasi',
                'deskripsi' => 'Perubahan yang mencakup kombinasi aspek teknis dan non-teknis secara bersamaan',
                'butuh_analisis_teknis' => true,
                'butuh_analisis_organisasi' => true,
            ],
        ];

        foreach ($data as $item) {
            RefLingkupPerubahan::firstOrCreate(
                ['nama' => $item['nama']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'butuh_analisis_teknis' => $item['butuh_analisis_teknis'],
                    'butuh_analisis_organisasi' => $item['butuh_analisis_organisasi'],
                ]
            );
        }
    }
}
