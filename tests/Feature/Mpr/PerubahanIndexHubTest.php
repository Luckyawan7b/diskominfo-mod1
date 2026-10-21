<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\Perubahan\PerubahanHub;
use App\Livewire\Mpr\Perubahan\PerubahanIndex;
use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Services\MprAlurResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 3: daftar perubahan, hub, dan checklist resolver.
 */
class PerubahanIndexHubTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    private function konteksLain(): array
    {
        $lain = $this->buatOperatorLainMpr();
        $konteks = MprKonteks::create([
            'layanan_id' => $lain->layanan->id, 'nama_instansi' => 'Lain',
            'tahun_penilaian' => 2026, 'created_by' => $lain->operator->id,
        ]);
        $perubahan = MprPerubahan::factory()->create(['mpr_konteks_id' => $konteks->id, 'created_by' => $lain->operator->id]);

        return [$lain, $konteks, $perubahan];
    }

    // ─── Index ──────────────────────────────────────────────────────────────

    public function test_index_hanya_menampilkan_perubahan_konteks_ini(): void
    {
        $data = $this->siapkanDataMpr();
        [, , $asing] = $this->konteksLain();

        $this->actingAs($data->operator)
            ->get(route('mpr.perubahan.index', $data->konteks))
            ->assertOk()
            ->assertSee($data->perubahan->id_perubahan)
            ->assertDontSee($asing->id_perubahan);
    }

    public function test_index_search_dan_filter(): void
    {
        $data = $this->siapkanDataMpr();
        $data->perubahan->update(['nama_layanan_digital' => 'Aplikasi Alpha']);
        $lain = MprPerubahan::factory()->klasifikasiStandard()->create([
            'mpr_konteks_id' => $data->konteks->id, 'created_by' => $data->operator->id,
            'nama_layanan_digital' => 'Aplikasi Beta',
        ]);

        Livewire::actingAs($data->operator)
            ->test(PerubahanIndex::class, ['konteks' => $data->konteks])
            ->assertViewHas('perubahanList', fn ($l) => $l->count() === 2)
            ->set('search', 'Alpha')
            ->assertViewHas('perubahanList', fn ($l) => $l->count() === 1 && $l->first()->id === $data->perubahan->id)
            ->set('search', '')
            ->set('filterKlasifikasi', (string) $lain->ref_klasifikasi_perubahan_id)
            ->assertViewHas('perubahanList', fn ($l) => $l->count() === 1 && $l->first()->id === $lain->id);
    }

    public function test_index_badge_kelengkapan_dari_resolver(): void
    {
        $data = $this->siapkanDataMpr();
        MprAnalisisRisiko::factory()->withLevels(5, 3)->create(['mpr_perubahan_id' => $data->perubahan->id]);

        Livewire::actingAs($data->operator)
            ->test(PerubahanIndex::class, ['konteks' => $data->konteks])
            ->assertViewHas('perubahanList', function ($list) {
                $k = $list->first()->kelengkapan;

                return $k['terisi'] === 1 && $k['wajib'] === 11; // Normal+Teknis: 12 formulir, organisasi tidak wajib
            });
    }

    public function test_admin_tidak_bisa_menghapus_403(): void
    {
        $data = $this->siapkanDataMpr();

        Livewire::actingAs($data->admin)
            ->test(PerubahanIndex::class, ['konteks' => $data->konteks])
            ->call('hapus', $data->perubahan->id)
            ->assertForbidden();

        $this->assertNull($data->perubahan->fresh()->deleted_at);
    }

    public function test_operator_hapus_soft_delete_dan_restore_kembali(): void
    {
        $data = $this->siapkanDataMpr();

        Livewire::actingAs($data->operator)
            ->test(PerubahanIndex::class, ['konteks' => $data->konteks])
            ->call('hapus', $data->perubahan->id)
            ->assertDispatched('perubahan-deleted')
            ->assertSee('Perubahan berhasil dihapus');

        $this->assertSoftDeleted('mpr_perubahan', ['id' => $data->perubahan->id, 'deleted_by' => $data->operator->id]);

        $data->perubahan->refresh()->restore();
        $this->assertNull($data->perubahan->fresh()->deleted_at);
    }

    public function test_hapus_perubahan_konteks_lain_ditolak(): void
    {
        $data = $this->siapkanDataMpr();
        [, , $asing] = $this->konteksLain();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($data->operator)
            ->test(PerubahanIndex::class, ['konteks' => $data->konteks])
            ->call('hapus', $asing->id);
    }

    public function test_index_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.perubahan.index', $data->konteks))
            ->assertForbidden();
    }

    public function test_index_admin_tanpa_tombol_ajukan(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.perubahan.index', $data->konteks))
            ->assertOk()
            ->assertDontSee('Ajukan Perubahan');
    }

    // ─── Hub ────────────────────────────────────────────────────────────────

    public function test_hub_menampilkan_checklist_wajib_dan_tidak_wajib(): void
    {
        $data = $this->siapkanDataMpr(); // Normal + Teknis

        Livewire::actingAs($data->operator)
            ->test(PerubahanHub::class, ['konteks' => $data->konteks, 'perubahan' => $data->perubahan])
            ->assertViewHas('formulir', function (array $f) {
                return $f['analisis_risiko']['wajib'] === true
                    && $f['analisis_organisasi']['wajib'] === false
                    && $f['persetujuan']['wajib'] === true
                    && $f['analisis_risiko']['terisi'] === false;
            })
            ->assertSee('Tidak wajib untuk klasifikasi dan lingkup ini.');
    }

    public function test_hub_menandai_formulir_terisi(): void
    {
        $data = $this->siapkanDataMpr();
        MprAnalisisRisiko::factory()->withLevels(5, 3)->create(['mpr_perubahan_id' => $data->perubahan->id]);

        Livewire::actingAs($data->operator)
            ->test(PerubahanHub::class, ['konteks' => $data->konteks, 'perubahan' => $data->perubahan])
            ->assertViewHas('formulir', fn (array $f) => $f['analisis_risiko']['terisi'] === true)
            ->assertViewHas('kelengkapan', fn (array $k) => $k['terisi'] === 1);
    }

    public function test_hub_perubahan_konteks_lain_404(): void
    {
        $data = $this->siapkanDataMpr();
        [, , $asing] = $this->konteksLain();

        $this->actingAs($data->operator)
            ->get(route('mpr.perubahan.hub', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_hub_admin_boleh_lihat(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.perubahan.hub', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('Checklist Formulir');
    }

    // ─── Resolver (tambahan Fase 3) ─────────────────────────────────────────

    public function test_resolver_formulir_terisi_dan_kelengkapan(): void
    {
        $data = $this->siapkanDataMpr();

        $r = MprAlurResolver::untuk($data->perubahan);
        $this->assertSame([], $r->formulirTerisi());
        $this->assertSame(['terisi' => 0, 'wajib' => 11], $r->kelengkapan());

        MprAnalisisRisiko::factory()->withLevels(5, 3)->create(['mpr_perubahan_id' => $data->perubahan->id]);

        $r = MprAlurResolver::untuk($data->perubahan->fresh());
        $this->assertSame(['Analisis Risiko (2A)'], $r->formulirTerisi());
        $this->assertSame(['terisi' => 1, 'wajib' => 11], $r->kelengkapan());
    }
}
