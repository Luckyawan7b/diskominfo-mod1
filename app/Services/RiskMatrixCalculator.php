<?php

namespace App\Services;

/**
 * RiskMatrixCalculator
 *
 * Mengimplementasikan Tabel 5 Matriks Risiko resmi SPBE Dinas.
 * Hasil `besaran_risiko` = kemungkinan × dampak (1–25).
 *
 * Disimpan (bukan dihitung on-the-fly) agar laporan historis tidak
 * berubah bila rumus matriks direvisi di masa depan.
 */
class RiskMatrixCalculator
{
    /**
     * Matriks besaran risiko resmi.
     * $matrix[kemungkinan][dampak] = besaran
     *
     * Skala kemungkinan: 1 (sangat rendah) — 5 (sangat tinggi)
     * Skala dampak     : 1 (tidak signifikan) — 5 (bencana)
     */
    private array $matrix = [
        1 => [1 => 1,  2 => 2,  3 => 3,  4 => 4,  5 => 5],
        2 => [1 => 2,  2 => 4,  3 => 6,  4 => 8,  5 => 10],
        3 => [1 => 3,  2 => 6,  3 => 9,  4 => 12, 5 => 15],
        4 => [1 => 4,  2 => 8,  3 => 12, 4 => 16, 5 => 20],
        5 => [1 => 5,  2 => 10, 3 => 15, 4 => 20, 5 => 25],
    ];

    /**
     * Hitung besaran risiko berdasarkan level kemungkinan dan dampak.
     *
     * @param  int  $kemungkinan  Nilai 1–5
     * @param  int  $dampak       Nilai 1–5
     * @return int|null           Nilai 1–25, atau null jika input tidak valid
     */
    public function hitung(int $kemungkinan, int $dampak): ?int
    {
        if (! $this->isValidLevel($kemungkinan) || ! $this->isValidLevel($dampak)) {
            return null;
        }

        return $this->matrix[$kemungkinan][$dampak];
    }

    /**
     * Alias for hitung()
     */
    public function calculate(int $kemungkinan, int $dampak): ?int
    {
        return $this->hitung($kemungkinan, $dampak);
    }

    /**
     * Tentukan label kategori risiko berdasarkan besaran.
     *
     * Klasifikasi resmi:
     *   1–4   → Rendah
     *   5–9   → Sedang
     *   10–16 → Tinggi
     *   17–25 → Sangat Tinggi
     */
    public function label(int $besaran): string
    {
        return match (true) {
            $besaran <= 4  => 'Rendah',
            $besaran <= 9  => 'Sedang',
            $besaran <= 16 => 'Tinggi',
            default        => 'Sangat Tinggi',
        };
    }

    /**
     * Apakah risiko ini melampaui selera risiko (appetit) instansi?
     *
     * @param  int  $besaran         Nilai besaran risiko (1–25)
     * @param  int  $seleraRisiko    Nilai batas dari mr_konteks.selera_risiko
     */
    public function melampauiSelera(int $besaran, int $seleraRisiko): bool
    {
        return $besaran > $seleraRisiko;
    }

    /**
     * Kembalikan pasangan class token semantik untuk badge besaran risiko.
     * Digunakan IDENTIK di keempat tempat: index, peta, konteks/form, risiko/form.
     *
     * @param  string  $label  Hasil dari label()
     * @return string          Class string untuk bg, text, dan border
     */
    public function colorClass(string $label): string
    {
        return match ($label) {
            'Rendah'        => 'bg-risk-low-bg text-risk-low border-risk-low/30',
            'Sedang'        => 'bg-risk-medium-bg text-risk-medium border-risk-medium/30',
            'Tinggi'        => 'bg-risk-high-bg text-risk-high border-risk-high/30',
            'Sangat Tinggi' => 'bg-risk-critical-bg text-risk-critical border-risk-critical/30',
            default         => 'bg-surface-soft text-muted border-border',
        };
    }

    private function isValidLevel(int $level): bool
    {
        return $level >= 1 && $level <= 5;
    }
}
