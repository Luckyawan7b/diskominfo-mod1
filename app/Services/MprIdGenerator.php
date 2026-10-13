<?php

namespace App\Services;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * MprIdGenerator
 *
 * Membuat ID Perubahan otomatis dengan format `MPR-{ALIAS}-{TAHUN}-{NNN}`
 * (contoh: MPR-DISKOMINFO-2025-001). Catatan: kode MPN memakai `MRP-`, jangan disamakan.
 *
 * Nomor urut dicari dari ID tertinggi dengan prefix yang sama termasuk baris soft-deleted
 * (withTrashed), sehingga ID tidak pernah dipakai ulang dan unique DB tetap aman.
 */
class MprIdGenerator
{
    /**
     * Hasilkan ID perubahan berikutnya untuk tahun & alias tertentu.
     *
     * @param  string|null  $alias  Bila null, memakai alias user yang login (fallback 'USER').
     */
    public function generate(int $tahun, ?string $alias = null): string
    {
        $alias = strtoupper($alias ?? (auth()->user()?->alias ?: 'USER'));
        $prefix = "MPR-{$alias}-{$tahun}-";

        $tertinggi = MprPerubahan::withTrashed()
            ->where('id_perubahan', 'like', $prefix . '%')
            ->pluck('id_perubahan')
            ->map(fn (string $id) => preg_match('/-(\d+)$/', $id, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        $seq = $tertinggi + 1;
        do {
            $kandidat = $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            $ada = MprPerubahan::withTrashed()->where('id_perubahan', $kandidat)->exists();
            $seq++;
        } while ($ada);

        return $kandidat;
    }

    /**
     * Buat MprPerubahan dengan ID otomatis di dalam transaksi.
     * Retry sekali bila terjadi UniqueConstraintViolationException (race condition).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function buatPerubahan(MprKonteks $konteks, array $attributes, ?string $alias = null): MprPerubahan
    {
        $percobaan = function () use ($konteks, $attributes, $alias) {
            return DB::transaction(function () use ($konteks, $attributes, $alias) {
                $attributes['mpr_konteks_id'] = $konteks->id;
                $attributes['id_perubahan'] = $this->generate((int) $konteks->tahun_penilaian, $alias);

                return MprPerubahan::create($attributes);
            });
        };

        try {
            return $percobaan();
        } catch (UniqueConstraintViolationException) {
            return $percobaan();
        }
    }
}
