<?php

namespace Tests\Feature\Mpr;

use App\Livewire\Mpr\Evaluasi\EvaluasiForm;
use App\Livewire\Mpr\Logbook\LogbookForm;
use App\Models\MprKonteks;
use App\Models\MprLogbook;
use App\Models\MprPerubahan;
use App\Models\MrLampiran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\MembuatSkafoldMpr;
use Tests\TestCase;

/**
 * Fase 6: Pelaksanaan/Logbook (F4) dan Evaluasi (F5), termasuk pembersihan lampiran orphan.
 */
class LogbookEvaluasiFormTest extends TestCase
{
    use RefreshDatabase, MembuatSkafoldMpr;

    private function logbookKomponen($user, MprKonteks $konteks, MprPerubahan $perubahan)
    {
        return Livewire::actingAs($user)->test(LogbookForm::class, [
            'konteks'   => $konteks,
            'perubahan' => $perubahan,
        ]);
    }

    private function evaluasiKomponen($user, MprKonteks $konteks, MprPerubahan $perubahan)
    {
        return Livewire::actingAs($user)->test(EvaluasiForm::class, [
            'konteks'   => $konteks,
            'perubahan' => $perubahan,
        ]);
    }

    // ─── Logbook (F4) ────────────────────────────────────────────────────────

    public function test_logbook_admin_403_dan_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.logbook.form', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Simpan Logbook');

        $this->logbookKomponen($data->admin, $data->konteks, $data->perubahan)
            ->call('save')
            ->assertForbidden();

        $this->logbookKomponen($data->admin, $data->konteks, $data->perubahan)
            ->call('deleteLampiran', 1)
            ->assertForbidden();
    }

    public function test_logbook_operator_lain_403_dan_lintas_konteks_404(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.logbook.form', [$data->konteks, $data->perubahan]))
            ->assertForbidden();

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
            ->get(route('mpr.logbook.form', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_logbook_validasi_simpan_dan_unggah_serta_hapus_lampiran(): void
    {
        Storage::fake('public');
        $data = $this->siapkanDataMpr();

        $c = $this->logbookKomponen($data->operator, $data->konteks, $data->perubahan)
            ->set('tanggal_mulai', '2026-08-10')
            ->set('tanggal_selesai', '2026-08-05')
            ->set('bukti_url', 'bukan-url')
            ->set('status_perubahan', 'StatusPalsu')
            ->call('save')
            ->assertHasErrors([
                'tanggal_selesai'  => 'after_or_equal',
                'bukti_url'        => 'url',
                'status_perubahan' => 'in',
            ]);

        $file = UploadedFile::fake()->create('berita-acara.pdf', 500, 'application/pdf');

        $c->set('unit_pelaksana', 'Bidang Infrastruktur')
            ->set('pic_pelaksana', 'Budi Santoso')
            ->set('tanggal_mulai', '2026-08-10')
            ->set('tanggal_selesai', '2026-08-12')
            ->set('catatan', 'Migrasi berjalan lancar')
            ->set('bukti_url', 'https://diskominfo.go.id/bukti')
            ->set('status_perubahan', 'Berhasil')
            ->set('file_bukti', $file)
            ->call('save')
            ->assertHasNoErrors();

        $logbook = $data->perubahan->fresh()->logbook;
        $this->assertNotNull($logbook);
        $this->assertSame('Bidang Infrastruktur', $logbook->unit_pelaksana);
        $this->assertSame('Berhasil', $logbook->status_perubahan);
        $this->assertSame(1, $logbook->lampiran()->count());

        $lampiran = $logbook->lampiran()->first();
        $this->assertSame('berita-acara.pdf', $lampiran->nama_file);
        Storage::disk('public')->assertExists($lampiran->path_file);

        // Hapus lampiran dari form
        $c->call('deleteLampiran', $lampiran->id);
        Storage::disk('public')->assertMissing($lampiran->path_file);
        $this->assertSame(0, MrLampiran::withTrashed()->whereKey($lampiran->id)->count());
    }

    public function test_event_deleting_dan_force_delete_perubahan_menghapus_file_fisik_lampiran(): void
    {
        Storage::fake('public');
        $data = $this->siapkanDataMpr();

        $file = UploadedFile::fake()->create('bukti-deploy.pdf', 200, 'application/pdf');
        $this->logbookKomponen($data->operator, $data->konteks, $data->perubahan)
            ->set('status_perubahan', 'Berhasil')
            ->set('file_bukti', $file)
            ->call('save')
            ->assertHasNoErrors();

        $lampiran = $data->perubahan->fresh()->logbook->lampiran()->firstOrFail();
        $path = $lampiran->path_file;
        Storage::disk('public')->assertExists($path);

        // Soft delete perubahan TIDAK menghapus logbook maupun file lampiran (K3)
        $data->perubahan->delete();
        Storage::disk('public')->assertExists($path);
        $this->assertTrue(MrLampiran::whereKey($lampiran->id)->exists());

        // Force delete perubahan menghapus logbook sekaligus membersihkan file fisik & baris mr_lampiran
        $data->perubahan->forceDelete();
        Storage::disk('public')->assertMissing($path);
        $this->assertSame(0, MrLampiran::withTrashed()->whereKey($lampiran->id)->count());
    }

    public function test_command_artisan_pembersih_lampiran_orphan(): void
    {
        Storage::fake('public');
        $data = $this->siapkanDataMpr();

        $logbook = $data->perubahan->logbook()->create(['status_perubahan' => 'Berhasil']);
        $pathValid = UploadedFile::fake()->create('valid.pdf', 100)->store('lampiran', 'public');
        $pathOrphanDb = UploadedFile::fake()->create('orphan-db.pdf', 100)->store('lampiran', 'public');
        $pathOrphanDisk = UploadedFile::fake()->create('orphan-disk.pdf', 100)->store('lampiran', 'public');

        $lampiranValid = $logbook->lampiran()->create([
            'nama_file' => 'valid.pdf',
            'path_file' => $pathValid,
            'ukuran_kb' => 100,
        ]);

        // Baris mr_lampiran yang menunjuk ke logbook_id yang sudah tidak ada di DB (mis. terhapus lewat DB query langsung)
        $lampiranOrphan = MrLampiran::create([
            'lampirable_type' => MprLogbook::class,
            'lampirable_id'   => 999999,
            'nama_file'       => 'orphan-db.pdf',
            'path_file'       => $pathOrphanDb,
            'ukuran_kb'       => 100,
        ]);

        $this->artisan('lampiran:bersihkan-orphan')->assertSuccessful();

        // Lampiran valid tetap utuh
        Storage::disk('public')->assertExists($pathValid);
        $this->assertTrue(MrLampiran::whereKey($lampiranValid->id)->exists());

        // Baris lampiran orphan dan file fisiknya terhapus, begitu pula file liar tanpa baris DB
        Storage::disk('public')->assertMissing($pathOrphanDb);
        Storage::disk('public')->assertMissing($pathOrphanDisk);
        $this->assertSame(0, MrLampiran::withTrashed()->whereKey($lampiranOrphan->id)->count());
    }

    // ─── Evaluasi (F5) ───────────────────────────────────────────────────────

    public function test_evaluasi_admin_403_dan_read_only(): void
    {
        $data = $this->siapkanDataMpr();

        $this->actingAs($data->admin)
            ->get(route('mpr.evaluasi.form', [$data->konteks, $data->perubahan]))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Simpan Evaluasi');

        $this->evaluasiKomponen($data->admin, $data->konteks, $data->perubahan)
            ->call('save')
            ->assertForbidden();
    }

    public function test_evaluasi_operator_lain_403_dan_lintas_konteks_404(): void
    {
        $data = $this->siapkanDataMpr();
        $lain = $this->buatOperatorLainMpr();

        $this->actingAs($lain->operator)
            ->get(route('mpr.evaluasi.form', [$data->konteks, $data->perubahan]))
            ->assertForbidden();

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
            ->get(route('mpr.evaluasi.form', [$data->konteks, $asing]))
            ->assertNotFound();
    }

    public function test_evaluasi_menampilkan_ringkasan_indikator_f3_dan_simpan(): void
    {
        $data = $this->siapkanDataMpr();

        $data->perubahan->indikatorKeberhasilan()->create([
            'indikator' => 'Uptime layanan portal',
            'satuan'    => '%',
            'target'    => 99.5,
            'realisasi' => 99.8,
            'urutan'    => 0,
        ]);

        $c = $this->evaluasiKomponen($data->operator, $data->konteks, $data->perubahan)
            ->assertSee('Uptime layanan portal')
            ->assertSee('99.5')
            ->assertSee('99.8')
            ->set('hasil', 'BukanPilihan')
            ->call('save')
            ->assertHasErrors(['hasil' => 'in']);

        $c->set('hasil', 'Berhasil')
            ->set('lesson_learned', 'Pengujian beban sebelum rilis sangat membantu.')
            ->set('rekomendasi_solusi', 'Terapkan juga pada layanan lain.')
            ->call('save')
            ->assertHasNoErrors();

        $eval = $data->perubahan->fresh()->evaluasi;
        $this->assertNotNull($eval);
        $this->assertSame('Berhasil', $eval->hasil);
        $this->assertSame('Pengujian beban sebelum rilis sangat membantu.', $eval->lesson_learned);
        $this->assertSame('Terapkan juga pada layanan lain.', $eval->rekomendasi_solusi);
    }
}
