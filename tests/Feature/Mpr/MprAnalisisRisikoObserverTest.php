<?php

namespace Tests\Feature\Mpr;

use App\Models\Layanan;
use App\Models\MprAnalisisRisiko;
use App\Models\MprPerubahan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MprAnalisisRisikoObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_besaran_dihitung_dari_level(): void
    {
        $risiko = MprAnalisisRisiko::factory()->create(['level_kemungkinan' => 5, 'level_dampak' => 3]);

        $this->assertSame(15, $risiko->fresh()->besaran_risiko);
    }

    public function test_besaran_tidak_dihitung_bila_salah_satu_level_null(): void
    {
        $risiko = MprAnalisisRisiko::factory()->create(['level_kemungkinan' => 4, 'level_dampak' => null]);

        $this->assertNull($risiko->fresh()->besaran_risiko);
    }

    public function test_besaran_dihitung_ulang_saat_level_berubah(): void
    {
        $risiko = MprAnalisisRisiko::factory()->withLevels(2, 2)->create();
        $this->assertSame(4, $risiko->fresh()->besaran_risiko);

        $risiko->update(['level_dampak' => 5]);
        $this->assertSame(10, $risiko->fresh()->besaran_risiko);
    }

    public function test_layanan_id_terisi_otomatis_dari_perubahan(): void
    {
        $perubahan = MprPerubahan::factory()->create();

        $risiko = new MprAnalisisRisiko([
            'mpr_perubahan_id' => $perubahan->id,
            'kode_risiko'      => 'R-PR-100',
            'risiko'           => 'Risiko',
        ]);
        $risiko->save();

        $this->assertSame($perubahan->konteks->layanan_id, $risiko->fresh()->layanan_id);
    }

    public function test_layanan_id_salah_selalu_ditimpa(): void
    {
        $perubahan = MprPerubahan::factory()->create();
        $layananLain = Layanan::factory()->create();

        $risiko = MprAnalisisRisiko::create([
            'mpr_perubahan_id' => $perubahan->id,
            'layanan_id'       => $layananLain->id,
            'kode_risiko'      => 'R-PR-101',
            'risiko'           => 'Risiko',
        ]);

        $this->assertSame($perubahan->konteks->layanan_id, $risiko->fresh()->layanan_id);
    }
}
