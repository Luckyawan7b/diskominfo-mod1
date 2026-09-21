<?php

namespace Database\Seeders;

use App\Models\RefMetodePengolahan;
use Illuminate\Database\Seeder;

class RefMetodePengolahanSeeder extends Seeder
{
    /**
     * Sumber: Form2 Database Pengumpulan&Peng, "Table 2a. Deskripsi Metode Pengolahan Pengetahuan"
     * (Pemda_MPN-Manajemen Pengetahuan Contoh Pengisian.xlsx)
     */
    public function run(): void
    {
        $metode = [
            [
                'nama' => 'Penyimpulan Otomatis (Automated Summarization)',
                'deskripsi' => 'Pemanfaatan AI untuk memadatkan dokumen panjang (regulasi, laporan, SOP) tanpa menghilangkan esensi informasi.',
                'contoh_output' => 'Ringkasan eksekutif, poin-poin penting (bullet points).',
            ],
            [
                'nama' => 'Sintesis Pengetahuan Mutakhir (Knowledge Synthesis)',
                'deskripsi' => 'Pemanfaatan AI untuk memadatkan menggabungkan informasi dari beberapa dokumen terpisah untuk menjawab satu isu spesifik.',
                'contoh_output' => 'Jawaban komprehensif berbasis arsitektur RAG (Retrieval-Augmented Generation).',
            ],
            [
                'nama' => 'Anotasi Gambar & Diagram Fisik',
                'deskripsi' => 'Pemanfaatan AI untuk menerjemahkan aset visual (diagram alir, foto fasilitas) menjadi deskripsi tekstual.',
                'contoh_output' => 'Dokumentasi proses bisnis (BPMN) atau manual teknis yang dapat dicari.',
            ],
            [
                'nama' => 'Narasi Data (Data Storytelling)',
                'deskripsi' => 'Pemanfaatan AI untuk mengubah angka dan tren dari dasbor analitik menjadi laporan berbasis narasi bahasa alami.',
                'contoh_output' => 'Laporan perkembangan bulanan otomatis, analisis tren performa.',
            ],
            [
                'nama' => 'Penyusunan Materi Pembelajaran',
                'deskripsi' => 'Mengolah dokumen teknis internal menjadi materi edukasi yang siap pakai.',
                'contoh_output' => 'Modul pelatihan karyawan, kurikulum onboarding, soal kuis evaluasi.',
            ],
            [
                'nama' => 'Digitalisasi Tacit Knowledge',
                'deskripsi' => 'Mengolah hasil wawancara informal dengan pakar senior menjadi aset pengetahuan formal.',
                'contoh_output' => 'Dokumen FAQ (Tanya-Jawab), panduan troubleshooting mandiri.',
            ],
            [
                'nama' => 'Dedupikasi & Rekonsiliasi Konseptual',
                'deskripsi' => 'Mendeteksi dan menyatukan dokumen yang memiliki kemiripan makna meskipun redaksi katanya berbeda.',
                'contoh_output' => 'Repositori bersih dari dokumen ganda/redundan.',
            ],
        ];

        foreach ($metode as $item) {
            RefMetodePengolahan::firstOrCreate(
                ['nama' => $item['nama']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'contoh_output' => $item['contoh_output'],
                ]
            );
        }
    }
}
