<?php

namespace Database\Seeders;

use App\Models\RefStrategiIt;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR03, Tabel 3b (Strategi IT Generik).
 *
 * Tabel 3b di file Excel tidak terbaca sebagai teks. Hanya "Serverless" yang memiliki uraian
 * lengkap pada sel; "Canary" dan "Rolling" hanya muncul sebagai nilai contoh isian tanpa uraian.
 * Deskripsi TIDAK dikarang.
 */
class RefStrategiItSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Blue - Green', 'deskripsi' => null],
            ['nama' => 'Canary', 'deskripsi' => null],
            ['nama' => 'Rolling', 'deskripsi' => null],
            ['nama' => 'Recreate', 'deskripsi' => null],
            ['nama' => 'A/B Testing', 'deskripsi' => null],
            ['nama' => 'Shadow', 'deskripsi' => null],
            ['nama' => 'Immutable', 'deskripsi' => null],
            [
                'nama' => 'Serverless',
                'deskripsi' => 'Serverless Deployment adalah pendekatan modern di mana pengembang hanya fokus pada kode dan logika aplikasi tanpa perlu mengelola server fisik atau virtual. Infrastruktur dikendalikan sepenuhnya oleh penyedia layanan cloud, dan aplikasi dijalankan sebagai fungsi-fungsi kecil yang merespons peristiwa (event-driven). Cocok untuk sistem mikroservis atau aplikasi ringan, serverless deployment menawarkan skalabilitas otomatis dan biaya yang efisien karena hanya membayar ketika fungsi dijalankan.',
            ],
            // Daftar 8 strategi sudah dikonfirmasi lengkap oleh pemilik formulir. Jangan ditambah tanpa sumber.
        ];

        foreach ($data as $item) {
            RefStrategiIt::firstOrCreate(
                ['nama' => $item['nama']],
                ['deskripsi' => $item['deskripsi']]
            );
        }
    }
}
