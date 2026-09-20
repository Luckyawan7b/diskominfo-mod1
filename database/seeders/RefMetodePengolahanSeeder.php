<?php

namespace Database\Seeders;

use App\Models\RefMetodePengolahan;
use Illuminate\Database\Seeder;

class RefMetodePengolahanSeeder extends Seeder
{
    public function run(): void
    {
        // TODO: Sesuaikan dengan 7 metode pengolahan di Tabel 2a
        $metode = [
            [
                'nama' => 'Klasifikasi',
                'deskripsi' => 'Pengelompokan pengetahuan berdasarkan kategori tertentu.',
                'contoh_output' => 'Direktori Pengetahuan Terstruktur'
            ],
            [
                'nama' => 'Ekstraksi',
                'deskripsi' => 'Pengambilan intisari dari dokumen/pengetahuan yang panjang.',
                'contoh_output' => 'Ringkasan Eksekutif, Resume'
            ],
            [
                'nama' => 'Integrasi',
                'deskripsi' => 'Penggabungan beberapa sumber pengetahuan menjadi satu kesatuan.',
                'contoh_output' => 'Buku Panduan Terpadu'
            ],
        ];

        foreach ($metode as $item) {
            RefMetodePengolahan::firstOrCreate(
                ['nama' => $item['nama']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'contoh_output' => $item['contoh_output']
                ]
            );
        }
    }
}
