<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Admin\TrashIndex;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 7 & 8: Monitoring Admin (read-only), Tempat Sampah (restore berjenjang via UI), dan DemoDataSeeder.
 */
class MprAdminTrashDemoTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    public function test_admin_monitoring_membuka_seluruh_halaman_mpr_dalam_mode_read_only(): void
    {
        $data = $this->siapkanDataMpr(); // Normal + Teknis

        // 1. Daftar Konteks MPR mode admin lewat admin.review.mpr.konteks
        $this->actingAs($data->admin)
            ->get(route('admin.review.mpr.konteks', $data->layanan))
            ->assertOk()
            ->assertSee((string) $data->konteks->tahun_penilaian)
            ->assertSee('read-only')
            ->assertDontSee('Buat Konteks Baru');

        // 2. Semua halaman di dalam konteks MPR menampilkan banner read-only dan isEditable=false
        $routes = [
            route('konteks-mpr.form', $data->konteks),
            route('mpr.analisis-prioritas.form', $data->konteks),
            route('mpr.perubahan.index', $data->konteks),
            route('mpr.perubahan.hub', [$data->konteks, $data->perubahan]),
            route('mpr.perubahan.form', [$data->konteks, $data->perubahan]),
            route('mpr.analisis-risiko.form', [$data->konteks, $data->perubahan]),
            route('mpr.persetujuan.form', [$data->konteks, $data->perubahan]),
            route('mpr.perencanaan.form', [$data->konteks, $data->perubahan]),
            route('mpr.logbook.form', [$data->konteks, $data->perubahan]),
            route('mpr.evaluasi.form', [$data->konteks, $data->perubahan]),
        ];

        foreach ($routes as $url) {
            $this->actingAs($data->admin)
                ->get($url)
                ->assertOk()
                ->assertSee('read-only');
        }
    }

    public function test_alur_restore_berjenjang_di_tempat_sampah_ui_konteks_lalu_perubahan_lalu_risiko(): void
    {
        $data = $this->siapkanDataMpr();
        $risiko = MprAnalisisRisiko::factory()->withLevels(4, 3)->create([
            'mpr_perubahan_id' => $data->perubahan->id,
            'kode_risiko'      => 'R-TRASH-001',
            'risiko'           => 'Risiko yang dihapus',
        ]);
        $data->perubahan->evaluasi()->create(['hasil' => 'Berhasil']);

        // Operator menghapus risiko, perubahan, dan konteks (soft delete)
        $this->actingAs($data->operator);
        $risiko->delete();
        $data->perubahan->delete();
        $data->konteks->delete();

        $this->assertSoftDeleted('mpr_konteks', ['id' => $data->konteks->id]);
        $this->assertSoftDeleted('mpr_perubahan', ['id' => $data->perubahan->id]);
        $this->assertSoftDeleted('mpr_analisis_risiko', ['id' => $risiko->id]);

        // Admin membuka Tempat Sampah (TrashIndex) dan memulihkan berurutan:
        // 1. Restore Konteks MPR
        $trash = Livewire::actingAs($data->admin)
            ->test(TrashIndex::class)
            ->set('activeTab', 'mpr_konteks')
            ->assertSee((string) $data->konteks->tahun_penilaian)
            ->call('restore', 'mpr_konteks', $data->konteks->id);

        $this->assertNull($data->konteks->fresh()->deleted_at);

        // 2. Restore Perubahan MPR
        $trash->set('activeTab', 'mpr_perubahan')
            ->assertSee($data->perubahan->id_perubahan)
            ->call('restore', 'mpr_perubahan', $data->perubahan->id);

        $this->assertNull($data->perubahan->fresh()->deleted_at);
        // Anak non-soft-delete (mis. evaluasi) tetap utuh setelah perubahan direstore
        $this->assertSame('Berhasil', $data->perubahan->fresh()->evaluasi?->hasil);

        // 3. Restore Risiko MPR
        $trash->set('activeTab', 'mpr_analisis_risiko')
            ->assertSee('R-TRASH-001')
            ->call('restore', 'mpr_analisis_risiko', $risiko->id);

        $this->assertNull($risiko->fresh()->deleted_at);
        $this->assertSame(1, $data->perubahan->fresh()->analisisRisiko()->count());
    }

    public function test_demo_data_seeder_membuat_data_mpr_lengkap(): void
    {
        $this->seed(DatabaseSeeder::class);

        $konteks = MprKonteks::first();
        $this->assertNotNull($konteks, 'DemoDataSeeder harus membuat MprKonteks');
        $this->assertSame(1, $konteks->analisisPrioritas()->count());

        $perubahan = MprPerubahan::where('id_perubahan', 'MPR-DISKOMINFO-2025-001')->first();
        $this->assertNotNull($perubahan, 'DemoDataSeeder harus membuat MprPerubahan demo');
        $this->assertSame(2, $perubahan->analisisRisiko()->count());
        $this->assertNotNull($perubahan->analisisOrganisasi);
        $this->assertSame(4, $perubahan->analisisOrganisasi->areas()->count());
        $this->assertSame(3, $perubahan->persetujuan()->count());
        $this->assertNotNull($perubahan->implementasi);
        $this->assertNotNull($perubahan->strategi);
        $this->assertSame(1, $perubahan->sumberDaya()->count());
        $this->assertSame(1, $perubahan->indikatorKeberhasilan()->count());
        $this->assertSame(1, $perubahan->stakeholder()->count());
        $this->assertSame(1, $perubahan->komunikasi()->count());
        $this->assertSame(1, $perubahan->pelatihan()->count());
        $this->assertNotNull($perubahan->logbook);
        $this->assertNotNull($perubahan->evaluasi);
    }
}
