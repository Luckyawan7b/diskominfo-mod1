<?php

namespace Tests\Feature\Mpr;

use App\Models\RefAreaDampakOrg;
use App\Models\RefIndikatorAreaDampak;
use App\Models\RefKategoriStakeholder;
use App\Models\RefKegiatanStrategiOrganisasi;
use App\Models\RefKlasifikasiPerubahan;
use App\Models\RefLingkupPerubahan;
use App\Models\RefStrategiOrganisasi;
use App\Models\RefUkuranKompleksitas;
use Database\Seeders\RefAreaDampakOrgSeeder;
use Database\Seeders\RefJenisResistensiSeeder;
use Database\Seeders\RefKategoriStakeholderSeeder;
use Database\Seeders\RefKlasifikasiPerubahanSeeder;
use Database\Seeders\RefLingkupPerubahanSeeder;
use Database\Seeders\RefStrategiItSeeder;
use Database\Seeders\RefStrategiOrganisasiSeeder;
use Database\Seeders\RefTingkatResistensiSeeder;
use Database\Seeders\RefUkuranKompleksitasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MprSeederTest extends TestCase
{
    use RefreshDatabase;

    private function jalankanSemua(): void
    {
        $this->seed([
            RefKlasifikasiPerubahanSeeder::class,
            RefLingkupPerubahanSeeder::class,
            RefAreaDampakOrgSeeder::class,
            RefUkuranKompleksitasSeeder::class,
            RefStrategiItSeeder::class,
            RefStrategiOrganisasiSeeder::class,
            RefKategoriStakeholderSeeder::class,
            RefJenisResistensiSeeder::class,
            RefTingkatResistensiSeeder::class,
        ]);
    }

    private function hitungTabel(): array
    {
        return collect([
            'ref_klasifikasi_perubahan', 'ref_lingkup_perubahan', 'ref_area_dampak_org', 'ref_indikator_area_dampak',
            'ref_ukuran_kompleksitas', 'ref_strategi_it', 'ref_strategi_organisasi', 'ref_kegiatan_strategi_organisasi',
            'ref_kategori_stakeholder', 'ref_jenis_resistensi', 'ref_tingkat_resistensi',
        ])->mapWithKeys(fn ($t) => [$t => \DB::table($t)->count()])->all();
    }

    public function test_seeder_idempoten_dijalankan_dua_kali(): void
    {
        $this->jalankanSemua();
        $pertama = $this->hitungTabel();
        $this->jalankanSemua();

        $this->assertSame($pertama, $this->hitungTabel());
    }

    public function test_flag_klasifikasi_dan_lingkup(): void
    {
        $this->jalankanSemua();

        $normal = RefKlasifikasiPerubahan::where('nama', 'Normal')->first();
        $standard = RefKlasifikasiPerubahan::where('nama', 'Standard')->first();
        $emergency = RefKlasifikasiPerubahan::where('nama', 'Emergency')->first();
        $this->assertTrue($normal->wajib_analisis_dampak && $normal->wajib_persetujuan);
        $this->assertFalse($standard->wajib_analisis_dampak || $standard->wajib_persetujuan);
        $this->assertTrue($emergency->wajib_analisis_dampak && $emergency->wajib_persetujuan);

        $teknis = RefLingkupPerubahan::where('nama', 'Teknis')->first();
        $org = RefLingkupPerubahan::where('nama', 'Organisasi')->first();
        $both = RefLingkupPerubahan::where('nama', 'Teknis & Organisasi')->first();
        $this->assertTrue($teknis->butuh_analisis_teknis && ! $teknis->butuh_analisis_organisasi);
        $this->assertTrue(! $org->butuh_analisis_teknis && $org->butuh_analisis_organisasi);
        $this->assertTrue($both->butuh_analisis_teknis && $both->butuh_analisis_organisasi);
    }

    public function test_jumlah_indikator_area_dampak_5_9_5_9(): void
    {
        $this->jalankanSemua();

        $jumlah = RefAreaDampakOrg::orderBy('urutan')->get()
            ->map(fn ($a) => RefIndikatorAreaDampak::where('ref_area_dampak_org_id', $a->id)->count())
            ->all();

        $this->assertSame([5, 9, 5, 9], $jumlah);
    }

    public function test_bobot_ukuran_dan_struktur_strategi_organisasi(): void
    {
        $this->jalankanSemua();

        $this->assertSame(
            ['Tidak Ada' => 0, 'Rendah' => 1, 'Sedang' => 2, 'Tinggi' => 3],
            RefUkuranKompleksitas::pluck('bobot', 'nama')->all()
        );
        $this->assertSame(4, RefStrategiOrganisasi::count());
        RefStrategiOrganisasi::all()->each(
            fn ($s) => $this->assertSame(8, RefKegiatanStrategiOrganisasi::where('ref_strategi_organisasi_id', $s->id)->count())
        );
        $this->assertSame(3, RefKategoriStakeholder::count());
    }
}
