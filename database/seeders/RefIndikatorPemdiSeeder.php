<?php

namespace Database\Seeders;

use App\Models\RefAspekPemdi;
use App\Models\RefIndikatorPemdi;
use Illuminate\Database\Seeder;

class RefIndikatorPemdiSeeder extends Seeder
{
    public function run(): void
    {
        // TODO: Sesuaikan dengan ±17 Indikator di Tabel 1b
        $data = [
            'Kebijakan Internal Terkait Tata Kelola SPBE' => [
                'Tingkat Kematangan Kebijakan Internal Arsitektur SPBE Instansi Pusat/Pemerintah Daerah',
                'Tingkat Kematangan Kebijakan Internal Peta Rencana SPBE Instansi Pusat/Pemerintah Daerah',
            ],
            'Perencanaan Strategis SPBE' => [
                'Tingkat Kematangan Keterpaduan Rencana Strategis SPBE Instansi Pusat/Pemerintah Daerah',
            ],
            // Tambahkan indikator untuk aspek lainnya...
        ];

        foreach ($data as $namaAspek => $indikators) {
            $aspek = RefAspekPemdi::where('nama', $namaAspek)->first();
            if ($aspek) {
                foreach ($indikators as $namaIndikator) {
                    RefIndikatorPemdi::firstOrCreate([
                        'ref_aspek_pemdi_id' => $aspek->id,
                        'nama' => $namaIndikator
                    ]);
                }
            }
        }
    }
}
