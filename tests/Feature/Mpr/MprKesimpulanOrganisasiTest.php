<?php

namespace Tests\Feature\Mpr;

use App\Models\MprAnalisisOrganisasi;
use App\Models\MprPerubahan;
use App\Models\RefAreaDampakOrg;
use App\Models\RefUkuranKompleksitas;
use App\Services\MprKesimpulanOrganisasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MprKesimpulanOrganisasiTest extends TestCase
{
    use RefreshDatabase;

    public static function kasusAturan(): array
    {
        return [
            'contoh R,S,S,R -> Rendah'    => [['Rendah', 'Sedang', 'Sedang', 'Rendah'], 'Rendah'],
            'contoh T,R,S,T -> Tinggi'    => [['Tinggi', 'Rendah', 'Sedang', 'Tinggi'], 'Tinggi'],
            '>=2 Tidak Ada didahulukan'   => [['Tidak Ada', 'Tidak Ada', 'Tinggi', 'Tinggi'], 'Tidak Ada'],
            '>=2 Sedang'                  => [['Sedang', 'Sedang', 'Tinggi', 'Tidak Ada'], 'Sedang'],
            'semua Tinggi'                => [['Tinggi', 'Tinggi', 'Tinggi', 'Tinggi'], 'Tinggi'],
            'tanpa pasangan: ambil Tinggi' => [['Tinggi', 'Sedang', 'Rendah', 'Tidak Ada'], 'Tinggi'],
            'tanpa pasangan: ambil Sedang' => [['Sedang', 'Rendah', 'Tidak Ada', null], 'Sedang'],
            'tanpa pasangan: ambil Rendah' => [['Rendah', 'Tidak Ada', null, null], 'Rendah'],
            'satu Tidak Ada saja'         => [['Tidak Ada', null, null, null], 'Tidak Ada'],
            'semua kosong'                => [[null, null, null, null], 'Tidak Ada'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('kasusAturan')]
    public function test_aturan_pasangan(array $ukuran, string $harapan): void
    {
        $this->assertSame($harapan, (new MprKesimpulanOrganisasi())->simpulkan($ukuran));
    }


    public function test_terapkan_menyimpan_ukuran_dan_snapshot_rekomendasi(): void
    {
        $rendah = RefUkuranKompleksitas::create(['nama' => 'Rendah', 'definisi' => 'Definisi rendah', 'bobot' => 1]);
        $sedang = RefUkuranKompleksitas::create(['nama' => 'Sedang', 'definisi' => 'Definisi sedang', 'bobot' => 2]);

        $analisis = MprAnalisisOrganisasi::create(['mpr_perubahan_id' => MprPerubahan::factory()->create()->id]);
        foreach ([$rendah, $sedang, $sedang, $rendah] as $i => $ukuran) {
            $area = RefAreaDampakOrg::create(['nama' => "Area $i", 'urutan' => $i]);
            $analisis->areas()->create(['ref_area_dampak_org_id' => $area->id, 'ref_ukuran_kompleksitas_id' => $ukuran->id]);
        }

        (new MprKesimpulanOrganisasi())->terapkan($analisis);

        $analisis->refresh();
        $this->assertSame($rendah->id, $analisis->ref_ukuran_kompleksitas_id);
        $this->assertSame('Definisi rendah', $analisis->rekomendasi);
    }
}
