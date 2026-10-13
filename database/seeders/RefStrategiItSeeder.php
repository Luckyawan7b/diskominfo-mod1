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
            // TODO: lengkapi deskripsi Canary dari Tabel 3b (tidak terbaca di Excel).
            ['nama' => 'Canary', 'deskripsi' => null],
            // TODO: lengkapi deskripsi Rolling dari Tabel 3b (tidak terbaca di Excel).
            ['nama' => 'Rolling', 'deskripsi' => null],
            [
                'nama' => 'Serverless',
                'deskripsi' => 'Serverless Deployment adalah pendekatan modern di mana pengembang hanya fokus pada kode dan logika aplikasi tanpa perlu mengelola server fisik atau virtual. Infrastruktur dikendalikan sepenuhnya oleh penyedia layanan cloud, dan aplikasi dijalankan sebagai fungsi-fungsi kecil yang merespons peristiwa (event-driven). Cocok untuk sistem mikroservis atau aplikasi ringan, serverless deployment menawarkan skalabilitas otomatis dan biaya yang efisien karena hanya membayar ketika fungsi dijalankan.',
            ],
            // TODO: tambahkan strategi lain pada Tabel 3b (mis. Blue-Green, Big Bang) setelah daftar lengkap
            // diperoleh dari pemilik formulir. Jangan ditambah tanpa sumber.
        ];

        foreach ($data as $item) {
            RefStrategiIt::firstOrCreate(
                ['nama' => $item['nama']],
                ['deskripsi' => $item['deskripsi']]
            );
        }
    }
}
