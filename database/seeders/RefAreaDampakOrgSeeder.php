<?php

namespace Database\Seeders;

use App\Models\RefAreaDampakOrg;
use App\Models\RefIndikatorAreaDampak;
use Illuminate\Database\Seeder;

/**
 * Sumber: Excel MPR02B, Tabel 2c. Indikator Area Dampak (5 / 9 / 5 / 9 butir).
 */
class RefAreaDampakOrgSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Hubungan Pekerjaan' => [
                'Peralihan tugas ke orang, peran, atau lokasi baru',
                'Peningkatan koordinasi lintas batas organisasi',
                'Perubahan hubungan dengan pengguna PemDi atau vendor',
                'Pelaporan ke pegawai atau Formulir lain dari organisasi',
                'Peningkatan atau penurunan rentang kendali',
            ],
            'Konten Pekerjaan' => [
                'Pertambahan atau pengurangan jumlah pekerjaan',
                'Tugas atau tanggung jawab baru',
                'Proses atau prosedur baru',
                'Kebijakan baru atau yang telah diubah',
                'Kualitas baru dan/atau persyaratan waktu',
                'Jenis keputusan baru yang harus dibuat',
                'Peningkatan atau penurunan otoritas persetujuan',
                'Standar atau metrik kinerja baru',
                'Lokasi baru untuk substansi pendukung',
            ],
            'Keterampilan dan Kompetensi' => [
                'Prosedur menjadi lebih/kurang kompleks',
                'Beralih ke prosedur manual atau otomatis',
                'Kompetensi baru yang dibutuhkan untuk melakukan tugas',
                'Sistem baru atau berbeda',
                'Alat baru atau berbeda yang akan digunakan',
            ],
            'Lingkungan kerja' => [
                'Perubahan lokasi kerja',
                'Perubahan kualitas dan suasana lokasi kerja',
                'Perubahan rekan kerja',
                'Perubahan pendapatan',
                'Perubahan manfaat',
                'Perubahan jalur karir',
                'Perubahan dalam cara, tempat, dan orang yang melakukan tinjauan kinerja',
                'Perubahan dalam akses ke pelatihan dan pengembangan profesional',
                'Perubahan akses ke atasan',
            ],
        ];

        $urutanArea = 0;
        foreach ($data as $namaArea => $indikator) {
            $urutanArea++;
            $area = RefAreaDampakOrg::firstOrCreate(['nama' => $namaArea], ['urutan' => $urutanArea]);

            foreach ($indikator as $i => $teks) {
                RefIndikatorAreaDampak::firstOrCreate(
                    ['ref_area_dampak_org_id' => $area->id, 'urutan' => $i + 1],
                    ['teks' => $teks]
                );
            }
        }
    }
}
