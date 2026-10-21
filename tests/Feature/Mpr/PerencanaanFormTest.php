<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\Perencanaan\PerencanaanForm;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\MprSumberDaya;
use App\Models\RefJenisResistensi;
use App\Models\RefKategoriStakeholder;
use App\Models\RefStrategiIt;
use App\Models\RefStrategiOrganisasi;
use App\Models\RefTingkatResistensi;
use Database\Seeders\RefJenisResistensiSeeder;
use Database\Seeders\RefKategoriStakeholderSeeder;
use Database\Seeders\RefStrategiItSeeder;
use Database\Seeders\RefStrategiOrganisasiSeeder;
use Database\Seeders\RefTingkatResistensiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 5: F3 Perencanaan Perubahan (7 tab).
 */
class PerencanaanFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RefStrategiItSeeder::class,
            RefStrategiOrganisasiSeeder::class,
            RefKategoriStakeholderSeeder::class,
            RefJenisResistensiSeeder::class,
            RefTingkatResistensiSeeder::class,
        ]);
    }

    private function komponen($user, MprKonteks $konteks, MprPerubahan $perubahan)
    {
        return Livewire::actingAs($user)->test(PerencanaanForm::class, [
            'konteks'   => $konteks,
            'perubahan' => $perubahan,
        ]);
    }

    public function test_admin_tidak_bisa_menulis_di_semua_tab_403(): void
    {
        $data = $this->siapkanDataMpr();

        foreach ([
            'saveImplementasi',
            'saveStrategi',
            'addSumberDaya',
            'addIndikator',
            'addStakeholder',
            'addKomunikasi',
            'addPelatihan',
        ] as $method) {
            $this->komponen($data->admin, $data->konteks, $data->perubahan)
                ->call($method)
                ->assertForbidden();
        }
    }

    public function test_admin_melihat_form_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.perencanaan.form', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Simpan Implementasi');
    }

    public function test_operator_lain_ditolak_403(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.perencanaan.form', [$data->konteks, $data->perubahan]))
            ->assertForbidden();
    }

    public function test_perubahan_milik_konteks_lain_404(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();
        $konteksLain = MprKonteks::create([
            'layanan_id'      => $lain->layanan->id,
            'nama_instansi'   => 'Lain',
            'tahun_penilaian' => 2026,
            'created_by'      => $lain->operator->id,
        ]);
        $asing = MprPerubahan::factory()->create([
            'mpr_konteks_id' => $konteksLain->id,
            'created_by'     => $lain->operator->id,
        ]);

        $this->actingAs($data->operator)
            ->get(route('mpr.perencanaan.form', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_tab_implementasi_validasi_tanggal_selesai_dan_simpan(): void
    {
        $data = $this->siapkanDataMpr();

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->set('tanggal_mulai', '2026-06-15')
            ->set('tanggal_selesai', '2026-06-10')
            ->call('saveImplementasi')
            ->assertHasErrors(['tanggal_selesai' => 'after_or_equal']);

        $c->set('tanggal_selesai', '2026-07-15')
            ->set('jangka_waktu', '1 Bulan')
            ->set('pic_implementasi', 'Bidang Aplikasi')
            ->call('saveImplementasi')
            ->assertHasNoErrors()
            ->assertDispatched('perencanaan-saved')
            ->assertSee('Perencanaan implementasi berhasil disimpan.');

        $imp = $data->perubahan->fresh()->implementasi;
        $this->assertNotNull($imp);
        $this->assertSame('2026-06-15', $imp->tanggal_mulai->format('Y-m-d'));
        $this->assertSame('2026-07-15', $imp->tanggal_selesai->format('Y-m-d'));
        $this->assertSame('1 Bulan', $imp->jangka_waktu);
        $this->assertSame('Bidang Aplikasi', $imp->pic_implementasi);
    }

    public function test_tab_strategi_menampilkan_panduan_8_kegiatan_dan_simpan(): void
    {
        $data = $this->siapkanDataMpr();

        $serverless = RefStrategiIt::where('nama', 'Serverless')->firstOrFail();
        $empiris = RefStrategiOrganisasi::where('nama', 'Pendekatan Empiris-Rasional')->firstOrFail();

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->set('ref_strategi_it_id', $serverless->id)
            ->assertSee($serverless->deskripsi)
            ->set('deskripsi_strategi_it', 'Migrasi fungsi ke cloud')
            ->set('rollback_plan', 'Kembali ke image lama')
            ->set('ref_strategi_organisasi_id', $empiris->id)
            ->assertViewHas('selectedStrategiOrg', fn ($org) => $org && $org->kegiatan->count() === 8)
            ->set('detail_strategi_organisasi', 'Sosialisasi bertahap')
            ->call('saveStrategi')
            ->assertHasNoErrors()
            ->assertDispatched('perencanaan-saved')
            ->assertSee('Strategi perubahan berhasil disimpan.');

        $str = $data->perubahan->fresh()->strategi;
        $this->assertNotNull($str);
        $this->assertSame($serverless->id, $str->ref_strategi_it_id);
        $this->assertSame('Kembali ke image lama', $str->rollback_plan);
        $this->assertSame($empiris->id, $str->ref_strategi_organisasi_id);
        $this->assertSame('Sosialisasi bertahap', $str->detail_strategi_organisasi);
    }

    public function test_tab_sumber_daya_validasi_anggaran_simpan_reorder_dan_hapus(): void
    {
        $data = $this->siapkanDataMpr();

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('addSumberDaya')
            ->call('saveSumberDaya', 0)
            ->assertHasErrors([
                'sumberDayaBlocks.0.jenis'     => 'required',
                'sumberDayaBlocks.0.deskripsi' => 'required',
            ]);

        // Validasi numerik anggaran
        $c->set('sumberDayaBlocks.0.jenis', 'TIK')
            ->set('sumberDayaBlocks.0.deskripsi', 'Sewa server cloud')
            ->set('sumberDayaBlocks.0.alokasi_anggaran', 'bukan-angka')
            ->call('saveSumberDaya', 0)
            ->assertHasErrors(['sumberDayaBlocks.0.alokasi_anggaran' => 'numeric']);

        $c->set('sumberDayaBlocks.0.alokasi_anggaran', '15000000.50')
            ->set('sumberDayaBlocks.0.skema_pembiayaan', 'APBD')
            ->set('sumberDayaBlocks.0.pic', 'Subbag Keuangan')
            ->call('saveSumberDaya', 0)
            ->assertHasNoErrors()
            ->assertDispatched('perencanaan-saved')
            ->assertSee('Sumber daya berhasil disimpan.');

        // Tambah baris kedua untuk uji pengubahan urutan
        $c->call('addSumberDaya')
            ->set('sumberDayaBlocks.1.jenis', 'Manusia')
            ->set('sumberDayaBlocks.1.deskripsi', 'Tenaga ahli')
            ->call('saveSumberDaya', 1)
            ->assertHasNoErrors();

        $idPertama = $c->get('sumberDayaBlocks.0.id');
        $idKedua = $c->get('sumberDayaBlocks.1.id');

        // Pindah baris kedua ke atas
        $c->call('moveSumberDayaUp', 1);
        $this->assertSame(0, MprSumberDaya::find($idKedua)->urutan);
        $this->assertSame(1, MprSumberDaya::find($idPertama)->urutan);

        // Hapus baris pertama di urutan saat ini ($idKedua)
        $c->call('removeSumberDaya', 0)
            ->assertDispatched('perencanaan-deleted')
            ->assertSee('Sumber daya berhasil dihapus.');
        $this->assertDatabaseMissing('mpr_sumber_daya', ['id' => $idKedua]);
        $this->assertSame(0, MprSumberDaya::find($idPertama)->urutan);
    }

    public function test_tab_indikator_keberhasilan_simpan_reorder_dan_hapus(): void
    {
        $data = $this->siapkanDataMpr();

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan)
            ->call('addIndikator')
            ->call('saveIndikator', 0)
            ->assertHasErrors(['indikatorBlocks.0.indikator' => 'required']);

        $c->set('indikatorBlocks.0.indikator', 'Waktu respon layanan')
            ->set('indikatorBlocks.0.satuan', 'detik')
            ->set('indikatorBlocks.0.target', '1.5')
            ->set('indikatorBlocks.0.realisasi', '1.2')
            ->call('saveIndikator', 0)
            ->assertHasNoErrors()
            ->assertDispatched('perencanaan-saved')
            ->assertSee('Indikator keberhasilan berhasil disimpan.');

        $c->call('addIndikator')
            ->set('indikatorBlocks.1.indikator', 'Tingkat kepuasan pengguna')
            ->set('indikatorBlocks.1.satuan', '%')
            ->set('indikatorBlocks.1.target', '90')
            ->call('saveIndikator', 1)
            ->assertHasNoErrors();

        $idA = $c->get('indikatorBlocks.0.id');
        $idB = $c->get('indikatorBlocks.1.id');

        $c->call('moveIndikatorDown', 0);
        $this->assertSame([$idB, $idA], $data->perubahan->indikatorKeberhasilan()->pluck('id')->all());

        $c->call('removeIndikator', 1)
            ->assertDispatched('perencanaan-deleted')
            ->assertSee('Indikator keberhasilan berhasil dihapus.');
        $this->assertDatabaseMissing('mpr_indikator_keberhasilan', ['id' => $idA]);
    }

    public function test_tab_stakeholder_komunikasi_dan_pelatihan(): void
    {
        $data = $this->siapkanDataMpr();

        $kat = RefKategoriStakeholder::firstOrFail();
        $jns = RefJenisResistensi::firstOrFail();
        $tkt = RefTingkatResistensi::where('nama', 'Blocker')->firstOrFail();

        $c = $this->komponen($data->operator, $data->konteks, $data->perubahan);

        // Stakeholder
        $c->call('addStakeholder')
            ->call('saveStakeholder', 0)
            ->assertHasErrors(['stakeholderBlocks.0.nama_stakeholder' => 'required']);

        $c->set('stakeholderBlocks.0.nama_stakeholder', 'Operator OPD')
            ->set('stakeholderBlocks.0.ref_kategori_stakeholder_id', $kat->id)
            ->set('stakeholderBlocks.0.ref_jenis_resistensi_id', $jns->id)
            ->set('stakeholderBlocks.0.ref_tingkat_resistensi_id', $tkt->id)
            ->call('saveStakeholder', 0)
            ->assertHasNoErrors()
            ->assertDispatched('perencanaan-saved')
            ->assertSee('Data pemangku kepentingan berhasil disimpan.');

        $this->assertSame(1, $data->perubahan->stakeholder()->count());

        // Komunikasi
        $c->call('addKomunikasi')
            ->call('saveKomunikasi', 0)
            ->assertHasErrors(['komunikasiBlocks.0.informasi' => 'required']);

        $c->set('komunikasiBlocks.0.informasi', 'Jadwal downtime sistem')
            ->set('komunikasiBlocks.0.pengirim', 'Diskominfo')
            ->set('komunikasiBlocks.0.target_audiens', 'Seluruh OPD')
            ->set('komunikasiBlocks.0.media_komunikasi', 'Surat Edaran & Grup WA')
            ->set('komunikasiBlocks.0.periode_komunikasi', 'H-3 sebelum rilis')
            ->call('saveKomunikasi', 0)
            ->assertHasNoErrors()
            ->assertDispatched('perencanaan-saved')
            ->assertSee('Rencana komunikasi berhasil disimpan.');

        $this->assertSame(1, $data->perubahan->komunikasi()->count());

        // Pelatihan
        $c->call('addPelatihan')
            ->call('savePelatihan', 0)
            ->assertHasErrors(['pelatihanBlocks.0.nama_pelatihan' => 'required']);

        $c->set('pelatihanBlocks.0.nama_pelatihan', 'Bimtek Portal Baru')
            ->set('pelatihanBlocks.0.target_peserta', 'Admin OPD')
            ->set('pelatihanBlocks.0.target_jumlah', '45')
            ->set('pelatihanBlocks.0.metode_pelatihan', 'Tatap Muka')
            ->set('pelatihanBlocks.0.tanggal_pelaksanaan', '2026-06-20')
            ->call('savePelatihan', 0)
            ->assertHasNoErrors()
            ->assertDispatched('perencanaan-saved')
            ->assertSee('Rencana pelatihan berhasil disimpan.');

        $this->assertSame(1, $data->perubahan->pelatihan()->count());

        // Hapus masing-masing
        $c->call('removeStakeholder', 0)
            ->assertDispatched('perencanaan-deleted')
            ->assertSee('Data pemangku kepentingan berhasil dihapus.')
            ->call('removeKomunikasi', 0)
            ->assertDispatched('perencanaan-deleted')
            ->assertSee('Rencana komunikasi berhasil dihapus.')
            ->call('removePelatihan', 0)
            ->assertDispatched('perencanaan-deleted')
            ->assertSee('Rencana pelatihan berhasil dihapus.');

        $this->assertSame(0, $data->perubahan->stakeholder()->count());
        $this->assertSame(0, $data->perubahan->komunikasi()->count());
        $this->assertSame(0, $data->perubahan->pelatihan()->count());
    }
}
