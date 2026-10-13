<?php

namespace Tests\Feature\Mpr;

use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MprSchemaTest extends TestCase
{
    use RefreshDatabase;

    public static function tabelSatuKeSatu(): array
    {
        return [
            ['mpr_evaluasi'], ['mpr_logbook'], ['mpr_strategi'],
            ['mpr_implementasi'], ['mpr_analisis_organisasi'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tabelSatuKeSatu')]
    public function test_relasi_satu_ke_satu_unik_per_perubahan(string $tabel): void
    {
        $perubahan = MprPerubahan::factory()->create();
        $baris = ['mpr_perubahan_id' => $perubahan->id, 'created_at' => now(), 'updated_at' => now()];

        DB::table($tabel)->insert($baris);
        $this->expectException(QueryException::class);
        DB::table($tabel)->insert($baris);
    }

    public function test_persetujuan_unik_per_perubahan_dan_tahap(): void
    {
        $perubahan = MprPerubahan::factory()->create();
        $baris = ['mpr_perubahan_id' => $perubahan->id, 'tahap' => 'pelaksanaan', 'created_at' => now(), 'updated_at' => now()];

        DB::table('mpr_persetujuan')->insert($baris);
        // tahap lain tetap boleh
        DB::table('mpr_persetujuan')->insert(['tahap' => 'analisis_teknis'] + $baris);

        $this->expectException(QueryException::class);
        DB::table('mpr_persetujuan')->insert($baris);
    }

    public function test_hapus_konteks_secara_permanen_menghapus_seluruh_anak(): void
    {
        $risiko = MprAnalisisRisiko::factory()->create();
        $perubahan = $risiko->perubahan;
        $perubahan->evaluasi()->create(['hasil' => 'Berhasil']);
        $perubahan->persetujuan()->create(['tahap' => 'pelaksanaan']);
        $perubahan->logbook()->create([]);

        $perubahan->konteks->forceDelete();

        foreach (['mpr_perubahan', 'mpr_analisis_risiko', 'mpr_evaluasi', 'mpr_persetujuan', 'mpr_logbook'] as $tabel) {
            $this->assertSame(0, DB::table($tabel)->count(), "$tabel harus kosong");
        }
    }

    public function test_soft_delete_mengisi_deleted_by_pada_tiga_tabel(): void
    {
        $risiko = MprAnalisisRisiko::factory()->create();
        $perubahan = $risiko->perubahan;
        $konteks = $perubahan->konteks;
        $user = $konteks->creator;
        $this->actingAs($user);

        $risiko->delete();
        $perubahan->delete();
        $konteks->delete();

        $this->assertSoftDeleted('mpr_analisis_risiko', ['id' => $risiko->id, 'deleted_by' => $user->id]);
        $this->assertSoftDeleted('mpr_perubahan', ['id' => $perubahan->id, 'deleted_by' => $user->id]);
        $this->assertSoftDeleted('mpr_konteks', ['id' => $konteks->id, 'deleted_by' => $user->id]);
    }

    /** K3: soft delete induk tidak menyentuh anak; restore mengembalikan semuanya. */
    public function test_soft_delete_perubahan_tidak_menyentuh_anak_dan_restore_aman(): void
    {
        $perubahan = MprPerubahan::factory()->create();
        $perubahan->evaluasi()->create(['hasil' => 'Berhasil']);
        $perubahan->persetujuan()->create(['tahap' => 'pelaksanaan']);

        $perubahan->delete();

        $this->assertSame(1, DB::table('mpr_evaluasi')->count());
        $this->assertSame(1, DB::table('mpr_persetujuan')->count());

        $perubahan->restore();
        $this->assertSame('Berhasil', MprPerubahan::find($perubahan->id)->evaluasi->hasil);
    }

    public function test_unique_konteks_per_layanan_dan_tahun(): void
    {
        $konteks = MprKonteks::factory()->create(['tahun_penilaian' => 2025]);

        $this->expectException(QueryException::class);
        MprKonteks::factory()->create(['layanan_id' => $konteks->layanan_id, 'tahun_penilaian' => 2025]);
    }
}
