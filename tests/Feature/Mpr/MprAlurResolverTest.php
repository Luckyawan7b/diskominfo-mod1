<?php

namespace Tests\Feature\Mpr;

use App\Models\MprPerubahan;
use App\Services\MprAlurResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MprAlurResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_teknis_hanya_analisis_risiko(): void
    {
        $r = MprAlurResolver::untuk(MprPerubahan::factory()->create());

        $this->assertSame(
            ['analisis_risiko' => true, 'analisis_organisasi' => false, 'persetujuan' => true],
            $r->checklist()
        );
    }

    public function test_normal_organisasi_hanya_analisis_organisasi(): void
    {
        $r = MprAlurResolver::untuk(MprPerubahan::factory()->lingkupOrganisasi()->create());

        $this->assertFalse($r->perluAnalisisTeknis());
        $this->assertTrue($r->perluAnalisisOrganisasi());
    }

    public function test_normal_teknis_dan_organisasi_keduanya(): void
    {
        $r = MprAlurResolver::untuk(MprPerubahan::factory()->lingkupTeknisOrganisasi()->create());

        $this->assertTrue($r->perluAnalisisTeknis());
        $this->assertTrue($r->perluAnalisisOrganisasi());
    }

    public function test_standard_tanpa_analisis_dan_persetujuan(): void
    {
        $r = MprAlurResolver::untuk(MprPerubahan::factory()->klasifikasiStandard()->lingkupTeknisOrganisasi()->create());

        $this->assertSame(
            ['analisis_risiko' => false, 'analisis_organisasi' => false, 'persetujuan' => false],
            $r->checklist()
        );
    }

    public function test_emergency_mewajibkan_analisis_dan_persetujuan(): void
    {
        $r = MprAlurResolver::untuk(MprPerubahan::factory()->klasifikasiEmergency()->create());

        $this->assertTrue($r->perluAnalisisTeknis());
        $this->assertTrue($r->perluPersetujuan());
    }
}
