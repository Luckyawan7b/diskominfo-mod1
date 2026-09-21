<?php

namespace Database\Seeders;

use App\Models\RefAspekPemdi;
use App\Models\RefIndikatorPemdi;
use Illuminate\Database\Seeder;

class RefIndikatorPemdiSeeder extends Seeder
{
    /**
     * Sumber: Form 1 Perencanaan, "Tabel 1b. Aspek dan Indikator Pemerintah Digital"
     * (Pemda_MPN-Manajemen Pengetahuan Contoh Pengisian.xlsx)
     */
    public function run(): void
    {
        $data = [
            'Tata Kelola dan Manajemen' => [
                'Tata Kelola dan Manajemen',
                'Manajemen Layanan Digital',
            ],
            'Penyelenggara' => [
                'Sumber daya manusia',
                'Kolaborasi Pemerintah Digital',
            ],
            'Data' => [
                'Tata kelola Data',
                'Pemanfaatan Informasi Geospasial',
                'Pembangunan Statistik',
                'Perlindungan data pribadi',
            ],
            'Keamanan Siber' => [
                'Pelaksanaan Audit Keamanan Siber',
                'Keamanan Siber',
                'Kriptografi untuk Keamanan Data',
                'Penanganan Insiden Siber',
            ],
            'Teknologi Digital' => [
                'Aplikasi Pemerintah Digital',
                'Infrastruktur Pemerintah Digital',
            ],
            'Keterpaduan Layanan Digital Pemerintah' => [
                'Keterpaduan proses bisnis',
                'Integrasi aplikasi',
                'Portal Layanan Digital Pemerintah',
                'Interoperabilitas data',
            ],
            'Kepuasan Pengguna Layanan Digital Pemerintah' => [
                'Fasilitas dukungan pengguna',
                'Tingkat kepuasan pengguna',
            ],
        ];

        foreach ($data as $namaAspek => $indikators) {
            $aspek = RefAspekPemdi::where('nama', $namaAspek)->first();

            if (! $aspek) {
                $this->command?->warn("Aspek PemDi '{$namaAspek}' tidak ditemukan. Jalankan RefAspekPemdiSeeder terlebih dahulu.");
                continue;
            }

            foreach ($indikators as $namaIndikator) {
                RefIndikatorPemdi::firstOrCreate([
                    'ref_aspek_pemdi_id' => $aspek->id,
                    'nama' => $namaIndikator,
                ]);
            }
        }
    }
}
