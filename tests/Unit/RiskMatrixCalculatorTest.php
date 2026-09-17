<?php

namespace Tests\Unit;

use App\Services\RiskMatrixCalculator;
use PHPUnit\Framework\TestCase;

/**
 * RiskMatrixCalculatorTest
 *
 * Teknik: Path Coverage (25 kombinasi kemungkinan×dampak), Boundary Value Analysis,
 * Branch Coverage (label, melampauiSelera, colorClass).
 *
 * Target: 100% Statement & Branch (class kecil, murni PHP tanpa dependensi Laravel).
 */
class RiskMatrixCalculatorTest extends TestCase
{
    private RiskMatrixCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new RiskMatrixCalculator();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // hitung() — Path Coverage: 25 kombinasi valid (1–5 × 1–5)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @dataProvider kombinasiMatriksProvider
     * Path Coverage — setiap jalur kemungkinan×dampak menghasilkan besaran yang benar.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('kombinasiMatriksProvider')]
    public function test_hitung_semua_kombinasi_valid(int $k, int $d, int $expected): void
    {
        $this->assertSame($expected, $this->calc->hitung($k, $d));
    }

    public static function kombinasiMatriksProvider(): array
    {
        // Matriks resmi: kemungkinan × dampak = besaran
        return [
            // kemungkinan = 1
            'k1_d1' => [1, 1,  1],
            'k1_d2' => [1, 2,  2],
            'k1_d3' => [1, 3,  3],
            'k1_d4' => [1, 4,  4],
            'k1_d5' => [1, 5,  5],
            // kemungkinan = 2
            'k2_d1' => [2, 1,  2],
            'k2_d2' => [2, 2,  4],
            'k2_d3' => [2, 3,  6],
            'k2_d4' => [2, 4,  8],
            'k2_d5' => [2, 5, 10],
            // kemungkinan = 3
            'k3_d1' => [3, 1,  3],
            'k3_d2' => [3, 2,  6],
            'k3_d3' => [3, 3,  9],
            'k3_d4' => [3, 4, 12],
            'k3_d5' => [3, 5, 15],
            // kemungkinan = 4
            'k4_d1' => [4, 1,  4],
            'k4_d2' => [4, 2,  8],
            'k4_d3' => [4, 3, 12],
            'k4_d4' => [4, 4, 16],
            'k4_d5' => [4, 5, 20],
            // kemungkinan = 5
            'k5_d1' => [5, 1,  5],
            'k5_d2' => [5, 2, 10],
            'k5_d3' => [5, 3, 15],
            'k5_d4' => [5, 4, 20],
            'k5_d5' => [5, 5, 25],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // hitung() — Boundary Value Analysis: input di luar batas → null
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Boundary: kemungkinan = 0 (tepat di bawah batas bawah) → null.
     */
    public function test_hitung_kemungkinan_nol_returns_null(): void
    {
        $this->assertNull($this->calc->hitung(0, 3));
    }

    /**
     * Boundary: dampak = 0 (tepat di bawah batas bawah) → null.
     */
    public function test_hitung_dampak_nol_returns_null(): void
    {
        $this->assertNull($this->calc->hitung(3, 0));
    }

    /**
     * Boundary: kemungkinan = 6 (tepat di atas batas atas) → null.
     */
    public function test_hitung_kemungkinan_enam_returns_null(): void
    {
        $this->assertNull($this->calc->hitung(6, 3));
    }

    /**
     * Boundary: dampak = 6 (tepat di atas batas atas) → null.
     */
    public function test_hitung_dampak_enam_returns_null(): void
    {
        $this->assertNull($this->calc->hitung(3, 6));
    }

    /**
     * Boundary: kedua input invalid → null.
     */
    public function test_hitung_keduanya_invalid_returns_null(): void
    {
        $this->assertNull($this->calc->hitung(0, 0));
        $this->assertNull($this->calc->hitung(6, 6));
        $this->assertNull($this->calc->hitung(-1, -1));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // calculate() — alias hitung(), cukup 1 skenario (DRY)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_calculate_adalah_alias_hitung(): void
    {
        $this->assertSame($this->calc->hitung(4, 3), $this->calc->calculate(4, 3));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // label() — Boundary Value Analysis + Branch Coverage
    // Klasifikasi: ≤4 Rendah, ≤9 Sedang, ≤16 Tinggi, ≥17 Sangat Tinggi
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @dataProvider labelBoundaryProvider
     * Branch + Boundary: nilai tepat di ambang batas atas/bawah tiap kategori.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('labelBoundaryProvider')]
    public function test_label_boundary_values(int $besaran, string $expected): void
    {
        $this->assertSame($expected, $this->calc->label($besaran));
    }

    public static function labelBoundaryProvider(): array
    {
        return [
            'besaran_1_rendah'          => [1,  'Rendah'],
            'besaran_4_rendah_max'      => [4,  'Rendah'],       // batas atas Rendah
            'besaran_5_sedang_min'      => [5,  'Sedang'],       // batas bawah Sedang
            'besaran_9_sedang_max'      => [9,  'Sedang'],       // batas atas Sedang
            'besaran_10_tinggi_min'     => [10, 'Tinggi'],       // batas bawah Tinggi
            'besaran_16_tinggi_max'     => [16, 'Tinggi'],       // batas atas Tinggi
            'besaran_17_sangat_tinggi'  => [17, 'Sangat Tinggi'],// batas bawah Sangat Tinggi
            'besaran_25_sangat_tinggi'  => [25, 'Sangat Tinggi'],// batas atas (max matriks)
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // melampauiSelera() — Branch Coverage: true & false
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch (true): besaran > selera → melampaui.
     */
    public function test_melampaui_selera_true_jika_besaran_lebih_besar(): void
    {
        $this->assertTrue($this->calc->melampauiSelera(13, 12));
    }

    /**
     * Branch (false): besaran = selera → tidak melampaui.
     */
    public function test_melampaui_selera_false_jika_besaran_sama_dengan_selera(): void
    {
        $this->assertFalse($this->calc->melampauiSelera(12, 12));
    }

    /**
     * Branch (false): besaran < selera → tidak melampaui.
     */
    public function test_melampaui_selera_false_jika_besaran_lebih_kecil(): void
    {
        $this->assertFalse($this->calc->melampauiSelera(6, 12));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // colorClass() — Branch Coverage: 4 label valid + 1 default/fallback
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @dataProvider colorClassProvider
     * Branch: setiap label valid dipetakan ke class CSS yang benar.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('colorClassProvider')]
    public function test_color_class_semua_label(string $label, string $expectedFragment): void
    {
        $result = $this->calc->colorClass($label);
        $this->assertStringContainsString($expectedFragment, $result);
    }

    public static function colorClassProvider(): array
    {
        return [
            'rendah'        => ['Rendah',        'bg-risk-low-bg'],
            'sedang'        => ['Sedang',         'bg-risk-medium-bg'],
            'tinggi'        => ['Tinggi',         'bg-risk-high-bg'],
            'sangat_tinggi' => ['Sangat Tinggi',  'bg-risk-critical-bg'],
        ];
    }

    /**
     * Branch (default/fallback): label tidak dikenal → class fallback.
     */
    public function test_color_class_fallback_untuk_label_tidak_dikenal(): void
    {
        $result = $this->calc->colorClass('Label Tidak Valid');
        $this->assertStringContainsString('bg-surface-soft', $result);
    }
}
