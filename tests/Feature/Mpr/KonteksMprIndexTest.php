<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\Konteks\KonteksMprIndex;
use App\Models\MprKonteks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

class KonteksMprIndexTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    public function test_create_konteks_admin_mendapat_403(): void
    {
        $data = $this->siapkanDataMpr();

        Livewire::actingAs($data->admin)
            ->test(KonteksMprIndex::class, ['layanan' => $data->layanan])
            ->call('createKonteks')
            ->assertForbidden();
    }

    public function test_create_konteks_tanpa_layanan_aktif_menolak(): void
    {
        $data = $this->siapkanDataMpr();

        Livewire::actingAs($data->operator)
            ->test(KonteksMprIndex::class)
            ->call('createKonteks')
            ->assertHasErrors(['newTahun']);
    }

    public function test_create_konteks_duplikat_ditolak(): void
    {
        $data = $this->siapkanDataMpr();
        session(['active_layanan_id' => $data->layanan->id]);

        Livewire::actingAs($data->operator)
            ->test(KonteksMprIndex::class)
            ->set('newTahun', 2026) // sudah ada di skafold
            ->call('createKonteks')
            ->assertHasErrors(['newTahun' => 'unique']);
    }

    public function test_create_konteks_boundary_tahun(): void
    {
        $data = $this->siapkanDataMpr();
        session(['active_layanan_id' => $data->layanan->id]);

        foreach ([2019, 2100] as $tahun) {
            Livewire::actingAs($data->operator)
                ->test(KonteksMprIndex::class)
                ->set('newTahun', $tahun)
                ->call('createKonteks')
                ->assertHasErrors(['newTahun']);
        }
    }

    public function test_create_konteks_periode_terlalu_panjang_ditolak(): void
    {
        $data = $this->siapkanDataMpr();
        session(['active_layanan_id' => $data->layanan->id]);

        Livewire::actingAs($data->operator)
            ->test(KonteksMprIndex::class)
            ->set('newTahun', 2027)
            ->set('newPeriode', str_repeat('x', 101))
            ->call('createKonteks')
            ->assertHasErrors(['newPeriode']);
    }

    public function test_create_konteks_sukses_mengisi_nama_instansi_otomatis(): void
    {
        $data = $this->siapkanDataMpr();
        session(['active_layanan_id' => $data->layanan->id]);

        Livewire::actingAs($data->operator)
            ->test(KonteksMprIndex::class)
            ->set('newTahun', 2027)
            ->set('newPeriode', '2027-2028')
            ->call('createKonteks')
            ->assertHasNoErrors();

        $baru = MprKonteks::where('layanan_id', $data->layanan->id)->where('tahun_penilaian', 2027)->first();

        $this->assertNotNull($baru);
        $this->assertSame('2027-2028', $baru->periode_perencanaan);
        $this->assertSame($data->layanan->creator->nama_dinas ?? '', $baru->nama_instansi);
        $this->assertSame($data->operator->id, $baru->created_by);
    }

    public function test_tombol_buat_konteks_hanya_untuk_operator(): void
    {
        $data = $this->siapkanDataMpr();
        session(['active_layanan_id' => $data->layanan->id]);

        Livewire::actingAs($data->operator)
            ->test(KonteksMprIndex::class)
            ->assertSee('Buat Konteks Baru');

        Livewire::actingAs($data->admin)
            ->test(KonteksMprIndex::class, ['layanan' => $data->layanan])
            ->assertDontSee('Buat Konteks Baru');
    }
}
