<?php

namespace App\Livewire;

use App\Livewire\Konteks\KonteksIndex;
use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MpnKonteks;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.hub')]
class Dashboard extends Component
{
    public Layanan $layanan;

    public function mount(Layanan $layanan): void
    {
        $user = auth()->user();

        // Pastikan operator hanya bisa akses layanan miliknya (scoping by created_by)
        if ($user->isOperator() && $layanan->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke layanan ini.');
        }

        $this->layanan = $layanan;
    }

    /**
     * Dipanggil saat user klik kartu Manajemen Risiko.
     */
    public function openModulMR(): void
    {
        session(['active_layanan_id' => $this->layanan->id]);
        $this->redirect(route('konteks.index'), navigate: true);
    }

    /**
     * Dipanggil saat user klik kartu Manajemen Pengetahuan.
     */
    public function openModulMPN(): void
    {
        session(['active_layanan_id' => $this->layanan->id]);
        $this->redirect(route('konteks-mpn.index'), navigate: true);
    }

    public function render()
    {
        $user    = auth()->user();
        $layanan = $this->layanan;

        $mrKonteks = MrKonteks::where('layanan_id', $layanan->id)->first();
        $mpnKonteks = MpnKonteks::where('layanan_id', $layanan->id)->first();

        $mrBadgeCount = 0; // Replace with actual logic for MR if needed
        $mpnBadgeCount = 0;
        // $mpnBadgeCount = $mpnKonteks ? $mpnKonteks->pengetahuan()->where('apakah_terdokumentasi', false)->count() : 0;

        return view('livewire.dashboard', [
            'layanan'    => $layanan,
            'modules'    => $this->getModules($layanan, $mrKonteks, $mpnKonteks, $mrBadgeCount, $mpnBadgeCount),
        ]);
    }

    private function getModules(Layanan $layanan, ?MrKonteks $mrKonteks, ?MpnKonteks $mpnKonteks, int $mrBadgeCount, int $mpnBadgeCount): array
    {
        $moduleTint = ['module-1', 'module-2', 'module-3', 'module-4', 'module-5'];

        $items = [
            [
                'name'        => 'Manajemen Risiko',
                'description' => 'Identifikasi, analisis, dan penanganan risiko SPBE',
                'icon'        => 'shield-check',
                'route'       => null,
                'wireAction'  => 'openModulMR',
                'active'      => true,
                'filled'      => (bool) $mrKonteks,
                'tint'        => $moduleTint[0],
                'badge_count' => $mrBadgeCount,
            ],
            [
                'name'        => 'Manajemen Pengetahuan',
                'description' => 'Pengelolaan dan berbagi pengetahuan organisasi',
                'icon'        => 'book-open',
                'route'       => null,
                'wireAction'  => 'openModulMPN',
                'active'      => true,
                'filled'      => (bool) $mpnKonteks,
                'tint'        => $moduleTint[1],
                'badge_count' => $mpnBadgeCount,
            ],
            [
                'name'        => 'Manajemen Perubahan',
                'description' => 'Perencanaan dan pelaksanaan perubahan organisasi',
                'icon'        => 'arrows-right-left',
                'route'       => null,
                'wireAction'  => null,
                'active'      => false,
                'filled'      => false,
                'tint'        => $moduleTint[2],
            ],
            [
                'name'        => 'Manajemen Keberlangsungan',
                'description' => 'Jaminan kelangsungan layanan dan operasional',
                'icon'        => 'arrow-path',
                'route'       => null,
                'active'      => false,
                'filled'      => false,
                'gradient'    => 'from-violet-500 to-purple-600',
                'shadow'      => 'shadow-violet-500/25',
                'bg'          => 'bg-violet-500/10',
                'text'        => 'text-violet-400',
                'border'      => 'border-violet-500/20',
            ],
            [
                'name'        => 'Manajemen Relasi',
                'description' => 'Pengelolaan hubungan dengan pemangku kepentingan',
                'icon'        => 'users',
                'route'       => null,
                'active'      => false,
                'filled'      => false,
                'gradient'    => 'from-rose-500 to-pink-600',
                'shadow'      => 'shadow-rose-500/25',
                'bg'          => 'bg-rose-500/10',
                'text'        => 'text-rose-400',
                'border'      => 'border-rose-500/20',
            ],
        ];

        return $items;
    }
}
