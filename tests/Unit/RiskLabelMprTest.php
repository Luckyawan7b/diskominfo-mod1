<?php

namespace Tests\Unit;

use App\Services\RiskMatrixCalculator;
use PHPUnit\Framework\TestCase;

class RiskLabelMprTest extends TestCase
{
    public function test_batas_label_mpr_tiga_level(): void
    {
        $c = new RiskMatrixCalculator();

        $this->assertSame('Rendah', $c->labelMpr(1));
        $this->assertSame('Rendah', $c->labelMpr(5));
        $this->assertSame('Sedang', $c->labelMpr(6));
        $this->assertSame('Sedang', $c->labelMpr(16));
        $this->assertSame('Tinggi', $c->labelMpr(17));
        $this->assertSame('Tinggi', $c->labelMpr(25));
    }

    public function test_label_mr_empat_level_tidak_berubah(): void
    {
        $c = new RiskMatrixCalculator();

        $this->assertSame('Rendah', $c->label(4));
        $this->assertSame('Sedang', $c->label(5));
        $this->assertSame('Sedang', $c->label(9));
        $this->assertSame('Tinggi', $c->label(10));
        $this->assertSame('Tinggi', $c->label(16));
        $this->assertSame('Sangat Tinggi', $c->label(17));
    }

    public function test_color_class_mpr_untuk_tiga_label(): void
    {
        $c = new RiskMatrixCalculator();

        foreach (['Rendah', 'Sedang', 'Tinggi'] as $label) {
            $this->assertSame($c->colorClass($label), $c->colorClassMpr($label));
        }
    }
}
