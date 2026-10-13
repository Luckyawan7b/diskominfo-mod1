<?php

namespace App\Rules;

use App\Models\MprAnalisisRisiko;
use App\Models\MrRisiko;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Kode risiko MPR harus unik per layanan, lintas modul:
 *  1. mr_risiko dari mr_konteks dengan layanan_id yang sama (belum dihapus), ATAU
 *  2. mpr_analisis_risiko dengan layanan_id yang sama (belum dihapus, kecuali $ignoreId).
 *
 * Keunikan ditegakkan di aplikasi (bukan unique DB) karena unique MySQL tidak
 * mengabaikan baris soft-deleted. Perbandingan case-insensitive lewat kolasi utf8mb4_unicode_ci.
 */
class KodeRisikoMprUnik implements ValidationRule
{
    public function __construct(
        private readonly int $layananId,
        private readonly ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $kode = trim((string) $value);

        $dipakaiMr = MrRisiko::query()
            ->whereNull('deleted_at')
            ->where('kode_risiko', $kode)
            ->whereHas('konteks', fn ($q) => $q->where('layanan_id', $this->layananId))
            ->exists();

        $dipakaiMpr = MprAnalisisRisiko::query()
            ->whereNull('deleted_at')
            ->where('layanan_id', $this->layananId)
            ->where('kode_risiko', $kode)
            ->when($this->ignoreId, fn ($q) => $q->where('id', '!=', $this->ignoreId))
            ->exists();

        if ($dipakaiMr || $dipakaiMpr) {
            $fail('Kode risiko sudah dipakai pada Manajemen Risiko atau Manajemen Perubahan layanan ini.');
        }
    }
}
