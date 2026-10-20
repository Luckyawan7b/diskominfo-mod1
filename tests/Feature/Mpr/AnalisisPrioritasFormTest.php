<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\AnalisisPrioritas\AnalisisPrioritasForm;
use App\Models\Layanan;
use App\Models\MprAnalisisPrioritas;
use App\Models\MprKonteks;
use App\Models\RefAspekPemdi;
use App\Models\RefIndikatorPemdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

class AnalisisPrioritasFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    private function refPemdi(): object
    {
        $aspekA = RefAspekPemdi::firstOrCreate(['nama' => 'Aspek A']);
        $aspekB = RefAspekPemdi::firstOrCreate(['nama' => 'Aspek B']);
        $indA = RefIndikatorPemdi::firstOrCreate(['ref_aspek_pemdi_id' => $aspekA->id, 'nama' => 'Indikator A1']);
        $indB = RefIndikatorPemdi::firstOrCreate(['ref_aspek_pemdi_id' => $aspekB->id, 'nama' => 'Indikator B1']);

        return (object) compact('aspekA', 'aspekB', 'indA', 'indB');
    }

    private function komponen($user, $konteks)
    {
        return Livewire::actingAs($user)->test(AnalisisPrioritasForm::class, ['konteks' => $konteks]);
    }

    public function test_admin_tidak_bisa_menulis_403(): void
    {
        $data = $this->siapkanDataMpr();

        $this->komponen($data->admin, $data->konteks)->call('addBlock')->assertForbidden();
        $this->assertSame(0, $data->konteks->analisisPrioritas()->count());

        $p = $data->konteks->analisisPrioritas()->create(['kondisi_perlu_berubah' => 'X']);

        $this->komponen($data->admin, $data->konteks)->call('saveBlock', 0)->assertForbidden();
        $this->komponen($data->admin, $data->konteks)->call('removeBlock', 0)->assertForbidden();
        $this->assertTrue(MprAnalisisPrioritas::whereKey($p->id)->exists());
    }

    public function test_add_block_mengisi_nama_layanan_otomatis(): void
    {
        $data = $this->siapkanDataMpr();

        $this->komponen($data->operator, $data->konteks)
            ->call('addBlock')
            ->assertSet('blocks.0.nama_layanan_prioritas', $data->layanan->nama_layanan);

        $this->assertSame(1, $data->konteks->analisisPrioritas()->count());
    }

    public function test_kondisi_wajib_diisi(): void
    {
        $data = $this->siapkanDataMpr();

        $this->komponen($data->operator, $data->konteks)
            ->call('addBlock')
            ->call('saveBlock', 0)
            ->assertHasErrors(['blocks.0.kondisi_perlu_berubah' => 'required']);
    }

    public function test_prioritas_harus_angka_dan_dalam_batas(): void
    {
        $data = $this->siapkanDataMpr();

        $c = $this->komponen($data->operator, $data->konteks)
            ->call('addBlock')
            ->set('blocks.0.kondisi_perlu_berubah', 'Kondisi');

        $c->set('blocks.0.prioritas', 'abc')->call('saveBlock', 0)->assertHasErrors(['blocks.0.prioritas']);
        $c->set('blocks.0.prioritas', 0)->call('saveBlock', 0)->assertHasErrors(['blocks.0.prioritas']);
        $c->set('blocks.0.prioritas', 1000)->call('saveBlock', 0)->assertHasErrors(['blocks.0.prioritas']);
    }

    public function test_simpan_menyimpan_isian_aspek_dan_indikator(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->refPemdi();

        $this->komponen($data->operator, $data->konteks)
            ->call('addBlock')
            ->set('blocks.0.kondisi_perlu_berubah', 'Kondisi lama')
            ->set('blocks.0.as_is', 'Manual')
            ->set('blocks.0.to_be', 'Digital')
            ->set('blocks.0.prioritas', 2)
            ->set('blocks.0.aspek_ids', [$ref->aspekA->id])
            ->set('blocks.0.indikator_ids', [$ref->indA->id])
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $p = $data->konteks->analisisPrioritas()->first();

        $this->assertSame('Kondisi lama', $p->kondisi_perlu_berubah);
        $this->assertSame(2, $p->prioritas);
        $this->assertSame([$ref->aspekA->id], $p->aspek()->pluck('ref_aspek_pemdi.id')->all());
        $this->assertSame([$ref->indA->id], $p->indikator()->pluck('ref_indikator_pemdi.id')->all());
    }

    public function test_indikator_dibuang_saat_aspeknya_dilepas(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->refPemdi();

        $this->komponen($data->operator, $data->konteks)
            ->call('addBlock')
            ->set('blocks.0.aspek_ids', [$ref->aspekA->id, $ref->aspekB->id])
            ->set('blocks.0.indikator_ids', [$ref->indA->id, $ref->indB->id])
            ->set('blocks.0.aspek_ids', [$ref->aspekA->id])
            ->assertSet('blocks.0.indikator_ids', [$ref->indA->id]);
    }

    public function test_indikator_milik_aspek_lain_tidak_tersimpan(): void
    {
        $data = $this->siapkanDataMpr();
        $ref = $this->refPemdi();

        $this->komponen($data->operator, $data->konteks)
            ->call('addBlock')
            ->set('blocks.0.kondisi_perlu_berubah', 'Kondisi')
            ->set('blocks.0.aspek_ids', [$ref->aspekA->id])
            ->set('blocks.0.indikator_ids', [$ref->indB->id]) // bukan milik aspek A
            ->call('saveBlock', 0);

        $this->assertSame(0, $data->konteks->analisisPrioritas()->first()->indikator()->count());
    }

    public function test_hapus_blok_menghapus_baris_dan_tautan_perubahan_dilepas(): void
    {
        $data = $this->siapkanDataMpr();

        $p = $data->konteks->analisisPrioritas()->create(['kondisi_perlu_berubah' => 'X']);
        $data->perubahan->update(['mpr_analisis_prioritas_id' => $p->id]);

        $this->komponen($data->operator, $data->konteks)->call('removeBlock', 0);

        $this->assertDatabaseMissing('mpr_analisis_prioritas', ['id' => $p->id]);
        $this->assertNull($data->perubahan->fresh()->mpr_analisis_prioritas_id);
        $this->assertNull($data->perubahan->fresh()->deleted_at);
    }

    public function test_tidak_bisa_mengubah_blok_milik_konteks_lain(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();
        $konteksLain = MprKonteks::create([
            'layanan_id'      => $lain->layanan->id,
            'nama_instansi'   => 'Lain',
            'tahun_penilaian' => 2026,
            'created_by'      => $lain->operator->id,
        ]);
        $asing = $konteksLain->analisisPrioritas()->create(['kondisi_perlu_berubah' => 'Asli']);

        $c = $this->komponen($data->operator, $data->konteks)->call('addBlock');
        // Simulasi manipulasi id dari klien.
        $c->set('blocks.0.id', $asing->id)
            ->set('blocks.0.kondisi_perlu_berubah', 'Diretas')
            ->call('saveBlock', 0)
            ->call('removeBlock', 0);

        $asing->refresh();
        $this->assertSame('Asli', $asing->kondisi_perlu_berubah);
        $this->assertTrue(MprAnalisisPrioritas::whereKey($asing->id)->exists());
    }

    public function test_admin_melihat_form_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.analisis-prioritas.form', $data->konteks))
            ->assertOk()
            ->assertDontSee('Tambah Analisis Prioritas');
    }

    public function test_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.analisis-prioritas.form', $data->konteks))
            ->assertForbidden();
    }
}
