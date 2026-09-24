<?php

namespace Tests\Feature;

use App\Livewire\Mpn\Pengumpulan\PengumpulanForm;
use App\Models\Layanan;
use App\Models\MpnKonteks;
use App\Models\MpnPengumpulan;
use App\Models\MpnPengetahuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * PengumpulanFormTest
 *
 * Teknik: Path Coverage & Branch Coverage untuk field lokasi_penyimpanan_lain.
 * Target: memastikan behavior dropdown + conditional keterangan 100% sesuai rencana.
 *
 * Skenario yang dicakup:
 *   1. Simpan dengan pilihan BUKAN "Lainnya" -> keterangan_lokasi_lainnya di DB harus NULL.
 *   2. Simpan dengan pilihan "Lainnya" tapi keterangan kosong -> validation error.
 *   3. Simpan dengan pilihan "Lainnya" + keterangan diisi -> keduanya tersimpan benar.
 *   4. Edit: ubah dari "Lainnya" ke pilihan lain -> keterangan lama otomatis terhapus dari DB.
 *   5. Bonus: nilai diluar opsi resmi -> ditolak validasi.
 *
 * Catatan: status_publikasi_simpan di-set ke "Draft" (nilai enum valid per migrasi).
 * Nilai "Disimpan"/"Publikasi" yang ada di blade adalah inkonsistensi pre-existing
 * yang berada di luar scope perbaikan ini.
 */
class PengumpulanFormTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Buat operator + layanan + mpn_konteks + mpn_pengetahuan sebagai scaffold test.
     */
    private function buatScaffold(): array
    {
        $operator = User::factory()->operator()->create(['nama_dinas' => 'Dinas Test MPN']);

        $layanan = Layanan::factory()->ownedBy($operator)->create([
            'status_layanan' => 'berjalan',
        ]);

        $konteks = MpnKonteks::create([
            'layanan_id'      => $layanan->id,
            'tahun_penilaian' => (int) date('Y'),
            'created_by'      => $operator->id,
        ]);

        $pengetahuan = MpnPengetahuan::create([
            'mpn_konteks_id'  => $konteks->id,
            'nama_sub_fitur'  => 'Sub Fitur Test',
            'nama_pengetahuan' => 'Pengetahuan Test Lokasi Penyimpanan',
            'created_by'      => $operator->id,
        ]);

        return compact('operator', 'layanan', 'konteks', 'pengetahuan');
    }

    /**
     * Helper untuk setup komponen Livewire dengan field wajib yang valid.
     * status_publikasi_simpan di-set "Draft" (nilai valid per enum migrasi).
     */
    private function setupKomponen(array $scaffold, string $pengumpulan = 'new')
    {
        return Livewire::test(PengumpulanForm::class, [
            'konteks'     => $scaffold['konteks'],
            'pengetahuan' => $scaffold['pengetahuan'],
            'pengumpulan' => $pengumpulan,
        ])
            ->set('id_pengetahuan', 'MRP-TST-' . date('Y') . '-001')
            ->set('tanggal_pengumpulan', date('Y-m-d'))
            ->set('unit_pengumpulan', 'Unit Test')
            ->set('status_publikasi_simpan', 'Draft'); // enum valid per migrasi
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Skenario 1: Pilihan bukan "Lainnya" -> keterangan_lokasi_lainnya harus NULL di DB
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Path: simpan dengan lokasi_penyimpanan_lain = "SIMPAN" (bukan Lainnya)
     * -> keterangan_lokasi_lainnya tersimpan NULL di database, meskipun ada nilai sebelumnya.
     */
    public function test_simpan_lokasi_bukan_lainnya_keterangan_null_di_db(): void
    {
        $scaffold = $this->buatScaffold();
        $this->actingAs($scaffold['operator']);

        $this->setupKomponen($scaffold)
            ->set('lokasi_penyimpanan_lain', 'SIMPAN')
            // Simulasi: pengguna pernah isi keterangan tapi ganti pilihan
            ->set('keterangan_lokasi_lainnya', 'Nilai yang harus diabaikan')
            ->call('save')
            ->assertHasNoErrors();

        $pengumpulan = MpnPengumpulan::where('id_pengetahuan', 'MRP-TST-' . date('Y') . '-001')->first();
        $this->assertNotNull($pengumpulan, 'Record pengumpulan harus terbuat.');
        $this->assertEquals('SIMPAN', $pengumpulan->lokasi_penyimpanan_lain);
        $this->assertNull(
            $pengumpulan->keterangan_lokasi_lainnya,
            'keterangan_lokasi_lainnya HARUS NULL jika pilihan bukan "Lainnya" (proteksi ganda di save).'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Skenario 2: Pilihan "Lainnya" + keterangan kosong -> validation error
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch: pilih "Lainnya" tapi keterangan dibiarkan kosong -> validation error.
     */
    public function test_simpan_lainnya_tanpa_keterangan_memicu_error_validasi(): void
    {
        $scaffold = $this->buatScaffold();
        $this->actingAs($scaffold['operator']);

        $this->setupKomponen($scaffold)
            ->set('lokasi_penyimpanan_lain', 'Lainnya')
            ->set('keterangan_lokasi_lainnya', '') // sengaja kosong
            ->call('save')
            ->assertHasErrors(['keterangan_lokasi_lainnya']);

        $this->assertDatabaseMissing('mpn_pengumpulan', [
            'lokasi_penyimpanan_lain' => 'Lainnya',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Skenario 3: Pilihan "Lainnya" + keterangan diisi -> keduanya tersimpan benar
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Path: pilih "Lainnya" dan isi keterangan -> kedua kolom tersimpan dengan benar di DB.
     */
    public function test_simpan_lainnya_dengan_keterangan_berhasil(): void
    {
        $scaffold = $this->buatScaffold();
        $this->actingAs($scaffold['operator']);

        $this->setupKomponen($scaffold)
            ->set('lokasi_penyimpanan_lain', 'Lainnya')
            ->set('keterangan_lokasi_lainnya', 'Lemari Arsip Lantai 3 Ruang Kepala')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mpn_pengumpulan', [
            'lokasi_penyimpanan_lain'   => 'Lainnya',
            'keterangan_lokasi_lainnya' => 'Lemari Arsip Lantai 3 Ruang Kepala',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Skenario 4: Edit — ubah dari "Lainnya" ke pilihan lain -> keterangan dihapus dari DB
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch/Edit: record awal memiliki lokasi="Lainnya" + keterangan.
     * Pengguna mengedit dan mengganti ke "Database Manajemen Risiko".
     * -> keterangan_lokasi_lainnya harus NULL di DB setelah save.
     */
    public function test_edit_ubah_dari_lainnya_ke_opsi_lain_keterangan_terhapus(): void
    {
        $scaffold = $this->buatScaffold();

        // Buat record awal dengan lokasi "Lainnya" + keterangan (langsung via DB)
        $pengumpulanModel = MpnPengumpulan::create([
            'mpn_pengetahuan_id'        => $scaffold['pengetahuan']->id,
            'id_pengetahuan'            => 'MRP-TST-' . date('Y') . '-001',
            'tanggal_pengumpulan'       => date('Y-m-d'),
            'unit_pengumpulan'          => 'Unit Test',
            'lokasi_penyimpanan_lain'   => 'Lainnya',
            'keterangan_lokasi_lainnya' => 'Rak Buku A2 Lantai 2',
            'status_publikasi_simpan'   => null, // nullable, valid
        ]);

        $this->actingAs($scaffold['operator']);

        Livewire::test(PengumpulanForm::class, [
            'konteks'     => $scaffold['konteks'],
            'pengetahuan' => $scaffold['pengetahuan'],
            'pengumpulan' => $pengumpulanModel,
        ])
            // Pastikan data lama termuat di form
            ->assertSet('lokasi_penyimpanan_lain', 'Lainnya')
            ->assertSet('keterangan_lokasi_lainnya', 'Rak Buku A2 Lantai 2')
            // Ganti pilihan: updatedLokasiPenyimpananLain() akan auto-clear keterangan
            ->set('lokasi_penyimpanan_lain', 'Database Manajemen Risiko')
            ->assertSet('keterangan_lokasi_lainnya', '')
            ->set('status_publikasi_simpan', 'Draft') // set nilai enum valid
            ->call('save')
            ->assertHasNoErrors();

        $pengumpulanModel->refresh();
        $this->assertEquals('Database Manajemen Risiko', $pengumpulanModel->lokasi_penyimpanan_lain);
        $this->assertNull(
            $pengumpulanModel->keterangan_lokasi_lainnya,
            'keterangan lama HARUS NULL setelah pilihan diubah dari "Lainnya".'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bonus: Pilihan di luar daftar resmi -> validation error
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch: kirim nilai lokasi_penyimpanan_lain yang tidak ada dalam daftar resmi
     * -> validation error (mencegah manipulasi langsung).
     */
    public function test_lokasi_diluar_opsi_resmi_ditolak_validasi(): void
    {
        $scaffold = $this->buatScaffold();
        $this->actingAs($scaffold['operator']);

        $this->setupKomponen($scaffold)
            ->set('lokasi_penyimpanan_lain', 'Nilai Ilegal Dari Luar')
            ->call('save')
            ->assertHasErrors(['lokasi_penyimpanan_lain']);
    }
}
