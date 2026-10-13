<?php

namespace Database\Seeders;

use App\Models\RefKlasifikasiPerubahan;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR01, Tabel 1a. Klasifikasi Perubahan.
 */
class RefKlasifikasiPerubahanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Normal',
                'deskripsi' => 'Perubahan yang tidak bersifat rutin dan memerlukan perencanaan, analisis dampak, serta proses persetujuan sebelum dilaksanakan. Perubahan ini memiliki tingkat risiko yang bervariasi dan perlu dikendalikan melalui tahapan manajemen perubahan yang lengkap.',
                'wajib_analisis_dampak' => true,
                'wajib_persetujuan' => true,
            ],
            [
                'nama' => 'Standard',
                'deskripsi' => 'Perubahan yang bersifat rutin, berulang, dan telah memiliki prosedur baku serta tingkat risiko yang rendah. Perubahan ini biasanya sudah terdokumentasi dan dapat dilaksanakan tanpa memerlukan proses persetujuan yang kompleks.',
                'wajib_analisis_dampak' => false,
                'wajib_persetujuan' => false,
            ],
            [
                'nama' => 'Emergency',
                'deskripsi' => 'Perubahan yang harus dilakukan segera untuk menangani insiden kritis atau gangguan layanan yang berdampak signifikan. Perubahan ini dilakukan dengan proses yang dipercepat, namun tetap mempertimbangkan kontrol minimum untuk menjaga stabilitas layanan.',
                'wajib_analisis_dampak' => true,
                'wajib_persetujuan' => true,
            ],
        ];

        foreach ($data as $item) {
            RefKlasifikasiPerubahan::firstOrCreate(
                ['nama' => $item['nama']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'wajib_analisis_dampak' => $item['wajib_analisis_dampak'],
                    'wajib_persetujuan' => $item['wajib_persetujuan'],
                ]
            );
        }
    }
}
