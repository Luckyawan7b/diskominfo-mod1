<?php

namespace App\Livewire;

use App\Livewire\Konteks\KonteksIndex;
use App\Models\Layanan;
use App\Models\MrKonteks;
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
     * Mengarahkan ke halaman Daftar Konteks Risiko (KonteksIndex).
     */
    public function openModulMR(): void
    {
        // Simpan layanan_id ke session agar KonteksIndex tahu layanan apa yang sedang aktif
        session(['active_layanan_id' => $this->layanan->id]);
        $this->redirect(route('konteks.index'), navigate: true);
    }

    public function render()
    {
        $user    = auth()->user();
        $layanan = $this->layanan;

        // Hitung badge: jumlah konteks MR layanan ini yang pending / rejected
        $mrKonteks = MrKonteks::where('layanan_id', $layanan->id)->first();

        // Tidak ada lagi alur approval/submit — badge selalu 0
        $badgeCount = 0;

        return view('livewire.dashboard', [
            'layanan'    => $layanan,
            'badgeCount' => $badgeCount,
            'modules'    => $this->getModules($layanan, $mrKonteks),
        ]);
    }

    private function getModules(Layanan $layanan, ?MrKonteks $mrKonteks): array
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
            ],
            [
                'name'        => 'Manajemen Pengetahuan',
                'description' => 'Pengelolaan dan berbagi pengetahuan organisasi',
                'icon'        => 'book-open',
                'route'       => null,
                'wireAction'  => null,
                'active'      => false,
                'filled'      => false,
                'tint'        => $moduleTint[1],
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
