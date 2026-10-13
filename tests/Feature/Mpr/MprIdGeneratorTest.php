<?php

namespace Tests\Feature\Mpr;

use App\Models\MprKonteks;
use App\Models\RefKlasifikasiPerubahan;
use App\Models\RefLingkupPerubahan;
use App\Services\MprIdGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MprIdGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private function atribut(): array
    {
        return [
            'nama_layanan_digital'         => 'Layanan Uji',
            'detail_perubahan'             => 'Detail',
            'ref_klasifikasi_perubahan_id' => RefKlasifikasiPerubahan::firstOrCreate(['nama' => 'Normal'])->id,
            'ref_lingkup_perubahan_id'     => RefLingkupPerubahan::firstOrCreate(['nama' => 'Teknis'])->id,
        ];
    }

    public function test_id_pertama_dan_kedua_berurutan(): void
    {
        $konteks = MprKonteks::factory()->create(['tahun_penilaian' => 2025]);
        $gen = new MprIdGenerator();

        $p1 = $gen->buatPerubahan($konteks, $this->atribut(), 'diskominfo');
        $p2 = $gen->buatPerubahan($konteks, $this->atribut(), 'diskominfo');

        $this->assertSame('MPR-DISKOMINFO-2025-001', $p1->id_perubahan);
        $this->assertSame('MPR-DISKOMINFO-2025-002', $p2->id_perubahan);
    }

    public function test_nomor_tidak_dipakai_ulang_setelah_soft_delete(): void
    {
        $konteks = MprKonteks::factory()->create(['tahun_penilaian' => 2025]);
        $gen = new MprIdGenerator();

        $p1 = $gen->buatPerubahan($konteks, $this->atribut(), 'DK');
        $p1->delete();
        $p2 = $gen->buatPerubahan($konteks, $this->atribut(), 'DK');

        $this->assertSame('MPR-DK-2025-002', $p2->id_perubahan);
    }

    public function test_alias_berbeda_memiliki_urutan_terpisah(): void
    {
        $konteks = MprKonteks::factory()->create(['tahun_penilaian' => 2025]);
        $gen = new MprIdGenerator();

        $gen->buatPerubahan($konteks, $this->atribut(), 'AAA');
        $b = $gen->buatPerubahan($konteks, $this->atribut(), 'BBB');

        $this->assertSame('MPR-BBB-2025-001', $b->id_perubahan);
    }

    public function test_alias_diambil_dari_user_login_dengan_fallback_user(): void
    {
        $gen = new MprIdGenerator();
        $this->assertSame('MPR-USER-2026-001', $gen->generate(2026));

        $user = \App\Models\User::factory()->operator()->create(['alias' => 'dinkes']);
        $this->actingAs($user);
        $this->assertSame('MPR-DINKES-2026-001', $gen->generate(2026));
    }
}
