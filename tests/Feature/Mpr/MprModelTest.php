<?php

namespace Tests\Feature\Mpr;

use App\Models\Layanan;
use App\Models\MprAnalisisPrioritas;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 4: model & factory MPR, termasuk aturan K5
 * (nama_layanan_prioritas otomatis dari layanan yang sedang diakses).
 */
class MprModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_nama_layanan_prioritas_terisi_otomatis_dari_layanan(): void
    {
        $layanan = Layanan::factory()->create(['nama_layanan' => 'Layanan Asli']);
        $konteks = MprKonteks::factory()->create(['layanan_id' => $layanan->id]);

        $prioritas = $konteks->analisisPrioritas()->create([
            'nama_layanan_prioritas' => 'Diisi Manual (harus diabaikan)',
            'kondisi_perlu_berubah'  => 'Kondisi',
        ]);

        $this->assertSame('Layanan Asli', $prioritas->nama_layanan_prioritas);
    }

    public function test_nama_layanan_prioritas_tidak_berubah_saat_layanan_diganti_nama(): void
    {
        $layanan = Layanan::factory()->create(['nama_layanan' => 'Nama Lama']);
        $konteks = MprKonteks::factory()->create(['layanan_id' => $layanan->id]);
        $prioritas = $konteks->analisisPrioritas()->create(['kondisi_perlu_berubah' => 'Kondisi']);

        $layanan->update(['nama_layanan' => 'Nama Baru']);

        $this->assertSame('Nama Lama', $prioritas->fresh()->nama_layanan_prioritas);
    }

    public function test_factory_dan_relasi_dasar(): void
    {
        $risiko = MprAnalisisRisiko::factory()->withLevels(5, 3)->create();

        $this->assertInstanceOf(MprPerubahan::class, $risiko->perubahan);
        $this->assertSame($risiko->perubahan->konteks->layanan_id, $risiko->layanan_id);
        $this->assertTrue($risiko->perubahan->klasifikasi->wajib_persetujuan);
        $this->assertSame(1, $risiko->perubahan->analisisRisiko()->count());
    }

    public function test_soft_delete_mengisi_deleted_by(): void
    {
        $perubahan = MprPerubahan::factory()->create();
        $user = $perubahan->creator;

        $this->actingAs($user);
        $perubahan->delete();

        $this->assertSoftDeleted('mpr_perubahan', ['id' => $perubahan->id, 'deleted_by' => $user->id]);
    }
}
