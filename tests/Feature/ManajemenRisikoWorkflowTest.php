<?php

namespace Tests\Feature;

use App\Livewire\Admin\ReviewIndex;
use App\Livewire\Admin\User\UserIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Konteks\KonteksForm;
use App\Livewire\Konteks\KonteksIndex;
use App\Livewire\Risiko\RisikoForm;
use App\Livewire\Sasaran\SasaranForm;
use App\Livewire\StrukturPelaksana\StrukturPelaksanaForm;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrRisiko;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManajemenRisikoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function createLayananForOperator(User $operator): Layanan
    {
        return Layanan::create([
            'nama_layanan'   => 'Layanan Test Otomatis',
            'status_layanan' => 'berjalan',
            'unit_pelaksana' => $operator->nama_dinas ?? '',
            'created_by'     => $operator->id,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_flow(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'operator.diskominfo@diskominfo.test')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('layanan.index'));

        $this->assertAuthenticated();
    }

    public function test_operator_without_layanan_is_redirected_to_create_layanan(): void
    {
        $role = Role::where('name', 'operator')->first();

        $operator = User::create([
            'name'       => 'Operator Baru Test',
            'email'      => 'operator.baru@test.com',
            'password'   => bcrypt('password'),
            'role_id'    => $role->id,
            'nama_dinas' => 'Dinas Test',
            'alias'      => 'dinastest',
        ]);

        $this->actingAs($operator);

        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->get('/manajemen-risiko');
        $response->assertRedirect(route('layanan.create'));
    }

    public function test_layanan_index_renders_for_operator_with_layanan(): void
    {
        $operator = User::where('email', 'operator.diskominfo@diskominfo.test')->first();
        $this->actingAs($operator);

        $layanan = $this->createLayananForOperator($operator);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Layanan Test Otomatis');
    }

    public function test_dashboard_5_modul_renders_for_layanan(): void
    {
        $operator = User::where('email', 'operator.diskominfo@diskominfo.test')->first();
        $this->actingAs($operator);

        $layanan = $this->createLayananForOperator($operator);

        $response = $this->get(route('layanan.dashboard', $layanan));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Risiko');
        $response->assertSee('Manajemen Pengetahuan');
        $response->assertSee('Manajemen Perubahan');
        $response->assertSee('Manajemen Keberlangsungan');
        $response->assertSee('Manajemen Relasi');
    }

    public function test_operator_can_create_konteks_via_layanan_and_fill_all_forms(): void
    {
        $operator = User::where('email', 'operator.diskominfo@diskominfo.test')->first();
        $this->actingAs($operator);

        // 1. Buat Layanan
        $layanan = $this->createLayananForOperator($operator);

        // 2. Buat Konteks MR
        $konteks = MrKonteks::create([
            'layanan_id'        => $layanan->id,
            'tahun_penilaian'   => (int) date('Y'),
            'tahun_pelaksanaan' => (int) date('Y'),
            'nama_instansi'     => $operator->nama_dinas,
            'nama_upr'          => $layanan->nama_layanan,
            'created_by'        => $operator->id,
        ]);
        $this->assertNotNull($konteks);
        $this->assertEquals($layanan->id, $konteks->layanan_id);
        // Tidak ada lagi desa_id — nama_instansi diisi dari nama_dinas creator
        $this->assertEquals($operator->nama_dinas, $konteks->nama_instansi);

        // 3. Fill Konteks
        Livewire::test(KonteksForm::class, ['konteks' => $konteks])
            ->set('nama_instansi', 'Dinas Kominfo Kabupaten Test')
            ->set('nama_upr', 'UPR SPBE Kominfo')
            ->set('tugas_upr', 'Melaksanakan administrasi SPBE dinas')
            ->set('fungsi_upr', 'Pengelolaan layanan digital dinas')
            ->set('selera_risiko', 12)
            ->call('save')
            ->assertHasNoErrors();

        $konteks->refresh();
        $this->assertEquals('UPR SPBE Kominfo', $konteks->nama_upr);
        $this->assertEquals(12, $konteks->selera_risiko);

        // 4. Fill Sasaran
        Livewire::test(SasaranForm::class, ['konteks' => $konteks])
            ->call('addBlock')
            ->set('blocks.0.sasaran_nasional', 'Sasaran Nasional Test')
            ->set('blocks.0.sasaran_upr', 'Meningkatkan kualitas data SPBE dinas')
            ->set('blocks.0.indikator.0.indikator_kinerja', 'Persentase update data per bulan')
            ->set('blocks.0.indikator.0.target_kinerja', '100%')
            ->call('saveBlock', 0)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mr_sasaran_upr', [
            'mr_konteks_id' => $konteks->id,
            'sasaran_upr'   => 'Meningkatkan kualitas data SPBE dinas',
        ]);

        // 5. Fill Struktur
        Livewire::test(StrukturPelaksanaForm::class, ['konteks' => $konteks])
            ->set('pemilik_risiko', 'Kepala Dinas Kominfo')
            ->set('koordinator_risiko', 'Sekretaris Dinas')
            ->set('pengelola_risiko', 'Kabid Infrastruktur & Kabid Aplikasi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mr_struktur_pelaksana', [
            'mr_konteks_id'  => $konteks->id,
            'pemilik_risiko' => 'Kepala Dinas Kominfo',
        ]);

        // 6. Fill Risiko — kategori_risiko sekarang teks bebas (bukan dropdown FK)
        Livewire::test(RisikoForm::class, ['konteks' => $konteks, 'risiko' => 'new'])
            ->set('kode_risiko', 'SKM-R-01')
            ->set('peristiwa_risiko', 'Server website dinas mengalami downtime saat jam kerja')
            ->set('penyebab', 'Koneksi internet dinas terputus dan tidak ada backup')
            ->set('dampak', 'Pelayanan surat menyurat warga tertunda')
            ->set('kategori_risiko', 'Risiko Operasional')
            ->set('level_kemungkinan', 4)
            ->set('level_dampak', 3)
            ->set('keputusan_perlakuan', 'Mengurangi risiko')
            ->set('deskripsi_detail_perlakuan', 'Menyediakan koneksi internet cadangan GSM modem')
            ->set('waktu_rencana_perlakuan', 'Triwulan I 2026')
            ->set('penanggung_jawab', 'Kabid Infrastruktur')
            ->set('level_kemungkinan_residual', 2)
            ->set('level_dampak_residual', 2)
            ->set('layanan_pendukung', 'Sistem Informasi SPBE Dinas')
            ->set('layanan_prioritas', 'Prioritas')
            ->call('save')
            ->assertHasNoErrors();

        $risiko = MrRisiko::where('kode_risiko', 'SKM-R-01')->first();
        $this->assertNotNull($risiko);
        $this->assertEquals('Risiko Operasional', $risiko->kategori_risiko);
        $this->assertEquals(12, $risiko->besaran_risiko); // 4 x 3 = 12
        $this->assertEquals(1, $risiko->prioritas_risiko);
    }

    public function test_admin_review_monitoring_shows_all_konteks(): void
    {
        $admin    = User::where('email', 'admin@diskominfo.test')->first();
        $operator = User::where('email', 'operator.diskominfo@diskominfo.test')->first();

        $layanan = $this->createLayananForOperator($operator);

        $konteks = MrKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => $operator->nama_dinas ?? 'Dinas Kominfo',
            'nama_upr'        => 'UPR Kominfo',
            'tahun_penilaian' => 2026,
            'created_by'      => $operator->id,
        ]);

        MrRisiko::create([
            'mr_konteks_id'     => $konteks->id,
            'kode_risiko'       => 'SKM-R-01',
            'peristiwa_risiko'  => 'Kebocoran data penduduk',
            'kategori_risiko'   => 'Risiko Keamanan Informasi',
            'level_kemungkinan' => 3,
            'level_dampak'      => 4,
            'created_by'        => $operator->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(ReviewIndex::class)
            ->assertSee('UPR Kominfo')
            ->assertSee('2026');
    }

    public function test_admin_user_crud_with_dinas(): void
    {
        $admin = User::where('email', 'admin@diskominfo.test')->first();
        $this->actingAs($admin);

        $operatorRole = Role::where('name', 'operator')->first();

        Livewire::test(UserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Operator Bojonggede')
            ->set('email', 'operator.bdg@diskominfo.test')
            ->set('password', 'secret123')
            ->set('role_id', $operatorRole->id)
            ->set('nama_dinas', 'Dinas Kesehatan Kabupaten Bogor')
            ->set('alias', 'dinkes')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email'      => 'operator.bdg@diskominfo.test',
            'nama_dinas' => 'Dinas Kesehatan Kabupaten Bogor',
            'alias'      => 'dinkes',
        ]);
    }

    public function test_operator_a_cannot_access_layanan_of_operator_b(): void
    {
        $operatorA = User::where('email', 'operator.diskominfo@diskominfo.test')->first();
        $operatorB = User::where('email', 'operator.bappeda@diskominfo.test')->first();

        $layananB = Layanan::create([
            'nama_layanan'   => 'Layanan Milik Operator B',
            'status_layanan' => 'berjalan',
            'created_by'     => $operatorB->id,
        ]);

        $this->actingAs($operatorA);
        $response = $this->get(route('layanan.edit', $layananB));
        $response->assertForbidden();
    }
}