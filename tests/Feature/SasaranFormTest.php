<?php

namespace Tests\Feature;

use App\Livewire\Sasaran\SasaranForm;
use App\Models\MrIndikatorKinerja;
use App\Models\MrKonteks;
use App\Models\MrSasaranUpr;
use App\Models\RefSasaranNasional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SasaranFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_add_save_and_manage_sasaran_upr_with_new_and_existing_national_goals(): void
    {
        $operator = User::where('email', 'operator.diskominfo@diskominfo.test')->first();
        $this->actingAs($operator);

        // layanan_id NOT NULL — buat layanan dulu
        $layanan = \App\Models\Layanan::create([
            'nama_layanan'   => 'Layanan Sasaran Test',
            'status_layanan' => 'berjalan',
            'unit_pelaksana' => $operator->nama_dinas ?? '',
            'created_by'     => $operator->id,
        ]);

        // Tidak ada lagi desa_id atau status — buat konteks langsung dengan created_by
        $konteks = MrKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => $operator->nama_dinas ?? 'Dinas Kominfo',
            'nama_upr'        => 'UPR SPBE Dinas Kominfo',
            'tahun_penilaian' => 2026,
            'created_by'      => $operator->id,
        ]);


        // 1. Operator adds a block and types a brand new Sasaran Pembangunan Nasional
        $testComponent = Livewire::test(SasaranForm::class, ['konteks' => $konteks])
            ->call('addBlock')
            ->set('blocks.0.sasaran_upr', 'Peningkatan Kualitas Pelayanan Publik Dinas')
            ->set('blocks.0.sasaran_nasional', 'Terwujudnya Tata Kelola Pemerintahan Digital')
            ->set('blocks.0.indikator.0.indikator_kinerja', 'Tingkat kepuasan pengguna layanan')
            ->set('blocks.0.indikator.0.target_kinerja', '85%')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ref_sasaran_nasional', [
            'teks_sasaran' => 'Terwujudnya Tata Kelola Pemerintahan Digital',
        ]);

        $createdRef = RefSasaranNasional::where('teks_sasaran', 'Terwujudnya Tata Kelola Pemerintahan Digital')->first();
        $this->assertNotNull($createdRef);

        $this->assertDatabaseHas('mr_sasaran_upr', [
            'mr_konteks_id'           => $konteks->id,
            'sasaran_upr'             => 'Peningkatan Kualitas Pelayanan Publik Dinas',
            'ref_sasaran_nasional_id' => $createdRef->id,
        ]);

        // 2. Add multiple indicators
        $testComponent->call('addIndikator', 0)
            ->set('blocks.0.indikator.1.indikator_kinerja', 'Waktu penyelesaian permohonan layanan')
            ->set('blocks.0.indikator.1.target_kinerja', '< 1 hari kerja')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $this->assertCount(2, MrIndikatorKinerja::all());

        // 3. Add another block with same national goal
        $testComponent->call('addBlock')
            ->set('blocks.1.sasaran_upr', 'Pengembangan Sistem Arsip Digital Dinas')
            ->set('blocks.1.sasaran_nasional', 'Terwujudnya Tata Kelola Pemerintahan Digital')
            ->set('blocks.1.indikator.0.indikator_kinerja', 'Persentase arsip terdigitalisasi')
            ->set('blocks.1.indikator.0.target_kinerja', '100%')
            ->call('saveBlock', 1)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mr_sasaran_upr', [
            'mr_konteks_id'           => $konteks->id,
            'sasaran_upr'             => 'Pengembangan Sistem Arsip Digital Dinas',
            'ref_sasaran_nasional_id' => $createdRef->id,
        ]);

        // 4. Remove an indicator from first block
        $testComponent->call('removeIndikator', 0, 1)->assertHasNoErrors();

        $firstBlockUpr = MrSasaranUpr::where('sasaran_upr', 'Peningkatan Kualitas Pelayanan Publik Dinas')->first();
        $this->assertCount(1, $firstBlockUpr->indikator);

        // 5. Remove second block
        $testComponent->call('removeBlock', 1)->assertHasNoErrors();

        $this->assertSoftDeleted('mr_sasaran_upr', [
            'sasaran_upr' => 'Pengembangan Sistem Arsip Digital Dinas',
        ]);
    }
}