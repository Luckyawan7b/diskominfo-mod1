<?php

namespace App\Services;

use App\Models\MprAnalisisOrganisasi;
use App\Models\RefUkuranKompleksitas;

/**
 * MprKesimpulanOrganisasi
 *
 * Menentukan kesimpulan ukuran kompleksitas Formulir 2.B dari 4 ukuran per area dampak
 * (mengikuti formula Excel MPR02B!H3). Urutan pengecekan:
 *   1. >=2 area 'Tidak Ada' -> Tidak Ada
 *   2. >=2 'Rendah'         -> Rendah
 *   3. >=2 'Sedang'         -> Sedang
 *   4. >=2 'Tinggi'         -> Tinggi
 *   5. ada 'Tinggi' -> Tinggi; ada 'Sedang' -> Sedang; ada 'Rendah' -> Rendah
 *   6. selain itu (kosong / hanya satu 'Tidak Ada') -> Tidak Ada
 */
class MprKesimpulanOrganisasi
{
    private const URUTAN_CEK = ['Tidak Ada', 'Rendah', 'Sedang', 'Tinggi'];

    /**
     * @param  array<int, string|null>  $ukuran  Nama ukuran kompleksitas tiap area (null = belum diisi).
     * @return string  Nama ukuran kesimpulan (default 'Tidak Ada').
     */
    public function simpulkan(array $ukuran): string
    {
        $hitung = array_count_values(array_filter($ukuran, fn ($u) => $u !== null && $u !== ''));

        foreach (self::URUTAN_CEK as $nama) {
            if (($hitung[$nama] ?? 0) >= 2) {
                return $nama;
            }
        }

        // Rumus Excel MPR02B!H3 langkah 5–7: tanpa pasangan >=2, ambil tertinggi yang ada
        // (Tinggi > Sedang > Rendah); bila tidak ada satu pun -> 'Tidak Ada'.
        foreach (['Tinggi', 'Sedang', 'Rendah'] as $nama) {
            if (isset($hitung[$nama])) {
                return $nama;
            }
        }

        return 'Tidak Ada';
    }

    /**
     * Hitung kesimpulan dari area yang tersimpan lalu simpan ke header analisis:
     * ref_ukuran_kompleksitas_id + rekomendasi (snapshot definisi).
     */
    public function terapkan(MprAnalisisOrganisasi $analisis): ?RefUkuranKompleksitas
    {
        $nama = $this->simpulkan(
            $analisis->areas()->with('ukuranKompleksitas')->get()
                ->map(fn ($area) => $area->ukuranKompleksitas?->nama)
                ->all()
        );

        $ukuran = $nama ? RefUkuranKompleksitas::where('nama', $nama)->first() : null;

        $analisis->update([
            'ref_ukuran_kompleksitas_id' => $ukuran?->id,
            'rekomendasi'                => $ukuran?->definisi,
        ]);

        return $ukuran;
    }
}
