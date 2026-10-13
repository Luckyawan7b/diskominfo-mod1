<?php

namespace Tests\Feature\Mpr;

use App\Models\Layanan;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use App\Rules\KodeRisikoMprUnik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class KodeRisikoMprUnikTest extends TestCase
{
    use RefreshDatabase;

    private function lolos(string $kode, int $layananId, ?int $ignoreId = null): bool
    {
        return Validator::make(['kode' => $kode], ['kode' => [new KodeRisikoMprUnik($layananId, $ignoreId)]])->passes();
    }

    private function mrRisiko(Layanan $layanan, string $kode): MrRisiko
    {
        $konteks = MrKonteks::create([
            'layanan_id' => $layanan->id, 'nama_instansi' => 'X', 'nama_upr' => 'U',
            'tahun_penilaian' => date('Y'), 'created_by' => \App\Models\User::factory()->operator()->create()->id,
        ]);

        return MrRisiko::create(['mr_konteks_id' => $konteks->id, 'kode_risiko' => $kode, 'peristiwa_risiko' => 'P']);
    }

    private function mprRisiko(MprKonteks $konteks, string $kode): MprAnalisisRisiko
    {
        $perubahan = MprPerubahan::factory()->create(['mpr_konteks_id' => $konteks->id]);

        return MprAnalisisRisiko::factory()->create(['mpr_perubahan_id' => $perubahan->id, 'kode_risiko' => $kode]);
    }

    public function test_ditolak_bila_sama_dengan_mr_risiko_layanan_yang_sama(): void
    {
        $layanan = Layanan::factory()->create();
        $this->mrRisiko($layanan, 'MR-001');

        $this->assertFalse($this->lolos('MR-001', $layanan->id));
        $this->assertFalse($this->lolos('  mr-001  ', $layanan->id), 'trim + case-insensitive');
    }

    public function test_ditolak_bila_sama_dengan_mpr_lain_layanan_yang_sama(): void
    {
        $konteks = MprKonteks::factory()->create();
        $this->mprRisiko($konteks, 'R-PR-001');

        $this->assertFalse($this->lolos('R-PR-001', $konteks->layanan_id));
    }

    public function test_lolos_bila_hanya_sama_dengan_layanan_lain(): void
    {
        $konteks = MprKonteks::factory()->create();
        $this->mprRisiko($konteks, 'R-PR-001');
        $layananLain = Layanan::factory()->create();
        $this->mrRisiko($layananLain, 'MR-009');

        $this->assertTrue($this->lolos('R-PR-001', $layananLain->id));
        $this->assertTrue($this->lolos('MR-009', $konteks->layanan_id));
    }

    public function test_lolos_bila_bentrok_dengan_baris_soft_deleted(): void
    {
        $konteks = MprKonteks::factory()->create();
        $this->mprRisiko($konteks, 'R-PR-002')->delete();
        $layanan = Layanan::factory()->create();
        $this->mrRisiko($layanan, 'MR-002')->delete();

        $this->assertTrue($this->lolos('R-PR-002', $konteks->layanan_id));
        $this->assertTrue($this->lolos('MR-002', $layanan->id));
    }

    public function test_lolos_saat_edit_diri_sendiri(): void
    {
        $konteks = MprKonteks::factory()->create();
        $risiko = $this->mprRisiko($konteks, 'R-PR-003');

        $this->assertFalse($this->lolos('R-PR-003', $konteks->layanan_id));
        $this->assertTrue($this->lolos('R-PR-003', $konteks->layanan_id, $risiko->id));
    }

    public function test_pesan_error(): void
    {
        $konteks = MprKonteks::factory()->create();
        $this->mprRisiko($konteks, 'R-PR-004');

        $v = Validator::make(['kode' => 'R-PR-004'], ['kode' => [new KodeRisikoMprUnik($konteks->layanan_id)]]);
        $this->assertSame(
            'Kode risiko sudah dipakai pada Manajemen Risiko atau Manajemen Perubahan layanan ini.',
            $v->errors()->first('kode')
        );
    }
}
