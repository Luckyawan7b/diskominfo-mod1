<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\MrKonteks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MiddlewareAccessTest
 *
 * Teknik: Path Testing — jalur diizinkan vs ditolak untuk setiap middleware.
 *
 * Middleware yang diuji:
 * - RoleMiddleware ('role:admin')
 * - EnsureHasLayanan ('has.layanan')
 * - EnsureKonteksAccessible ('konteks.access')
 * - EnsureKonteksEditable (diuji lewat middleware stack yang sama)
 *
 * Target coverage: ≥ 90%
 */
class MiddlewareAccessTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buatOperatorDenganLayanan(): array
    {
        $operator = \App\Models\User::factory()->operator()->create(['nama_dinas' => 'Dinas Test']);
        $layanan  = Layanan::factory()->ownedBy($operator)->create(['status_layanan' => 'berjalan']);
        $konteks  = MrKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => 'Dinas Test',
            'nama_upr'        => 'UPR SPBE Test',
            'tahun_penilaian' => date('Y'),
            'created_by'      => $operator->id,
        ]);

        return compact('operator', 'layanan', 'konteks');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RoleMiddleware — 'role:admin'
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Path (positive): admin akses route role:admin → lolos (200).
     */
    public function test_role_middleware_admin_lolos_ke_route_admin(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();

        $this->actingAs($admin)
             ->get(route('admin.review.index'))
             ->assertStatus(200);
    }

    /**
     * Path (negative): operator akses route role:admin → redirect (bukan 200/403).
     * RoleMiddleware redirect ke layanan.index dengan flash error untuk non-JSON.
     */
    public function test_role_middleware_operator_ditolak_dari_route_admin(): void
    {
        ['operator' => $operator, 'layanan' => $layanan] = $this->buatOperatorDenganLayanan();

        $this->actingAs($operator)
             ->get(route('admin.review.index'))
             ->assertRedirect(route('layanan.index'));
    }

    /**
     * Condition (negative): user tidak login akses route admin → ditolak.
     */
    public function test_role_middleware_guest_ditolak_dari_route_admin(): void
    {
        $this->get(route('admin.review.index'))
             ->assertRedirect(route('login'));
    }

    /**
     * Path (negative / JSON): operator akses JSON request ke route admin → 403.
     */
    public function test_role_middleware_operator_json_request_returns_403(): void
    {
        ['operator' => $operator, 'layanan' => $layanan] = $this->buatOperatorDenganLayanan();

        $this->actingAs($operator)
             ->getJson(route('admin.review.index'))
             ->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EnsureHasLayanan — 'has.layanan'
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Condition (array in_array, negative): operator tanpa layanan akses route terproteksi
     * → redirect ke layanan.create.
     */
    public function test_has_layanan_operator_tanpa_layanan_diredirect(): void
    {
        $operatorTanpaLayanan = \App\Models\User::factory()->operator()->create();

        // Buat konteks dummy dari user lain untuk bisa hit route has.layanan
        ['operator' => $lain, 'konteks' => $konteks] = $this->buatOperatorDenganLayanan();

        $this->actingAs($operatorTanpaLayanan)
             ->get(route('konteks.index'))
             ->assertRedirect(route('layanan.create'));
    }

    /**
     * Condition (array in_array, negative): operator tanpa layanan akses route whitelist
     * (layanan.create) → TIDAK diblokir (200).
     */
    public function test_has_layanan_whitelist_layanan_create_tidak_diblokir(): void
    {
        $operatorTanpaLayanan = \App\Models\User::factory()->operator()->create();

        $this->actingAs($operatorTanpaLayanan)
             ->get(route('layanan.create'))
             ->assertStatus(200);
    }

    /**
     * Branch: admin tanpa layanan akses route admin yang diproteksi role:admin → lolos (200).
     * EnsureHasLayanan tidak apply ke admin (hanya operator yang dicek).
     */
    public function test_has_layanan_tidak_apply_ke_admin(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();

        // Admin tidak perlu layanan — cukup akses route admin
        $this->actingAs($admin)
             ->get(route('admin.review.index'))
             ->assertStatus(200);
    }

    /**
     * Condition (positive): operator dengan layanan akses route terproteksi → lolos.
     */
    public function test_has_layanan_operator_dengan_layanan_lolos(): void
    {
        ['operator' => $operator] = $this->buatOperatorDenganLayanan();

        $this->actingAs($operator)
             ->get(route('konteks.index'))
             ->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EnsureKonteksAccessible — 'konteks.access'
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Branch: admin akses konteks siapa pun → selalu lolos (200).
     */
    public function test_konteks_accessible_admin_akses_konteks_manapun(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();
        ['konteks' => $konteks] = $this->buatOperatorDenganLayanan();

        $this->actingAs($admin)
             ->get(route('konteks.form', $konteks))
             ->assertStatus(200);
    }

    /**
     * Branch: operator akses konteks miliknya sendiri → lolos (200).
     */
    public function test_konteks_accessible_operator_akses_konteks_milik_sendiri(): void
    {
        ['operator' => $operator, 'konteks' => $konteks] = $this->buatOperatorDenganLayanan();

        $this->actingAs($operator)
             ->get(route('konteks.form', $konteks))
             ->assertStatus(200);
    }

    /**
     * Branch: operator akses konteks milik operator lain → 403.
     */
    public function test_konteks_accessible_operator_ditolak_akses_konteks_lain(): void
    {
        ['konteks' => $konteksMilikB] = $this->buatOperatorDenganLayanan();
        $operatorA = \App\Models\User::factory()->operator()->create();
        // operatorA punya layanan sendiri agar tidak diblokir has.layanan
        Layanan::factory()->ownedBy($operatorA)->create(['status_layanan' => 'berjalan']);

        $this->actingAs($operatorA)
             ->get(route('konteks.form', $konteksMilikB))
             ->assertStatus(403);
    }
}
