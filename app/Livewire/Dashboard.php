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
     * Menyimpan layanan_id ke session, lalu KonteksIndex akan auto-create konteks.
     */
    public function openModulMR(): void
    {
        $mrKonteks = MrKonteks::where('layanan_id', $this->layanan->id)->first();

        if ($mrKonteks) {
            // Konteks sudah ada → langsung masuk form
            $this->redirect(route('konteks.form', $mrKonteks), navigate: true);
        } else {
            // Konteks belum ada → simpan session, biarkan KonteksIndex auto-create
            session(['active_layanan_id' => $this->layanan->id]);
            $this->redirect(route('konteks.index'), navigate: true);
        }
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
        return [
            [
                'name'        => 'Manajemen Risiko',
                'description' => 'Identifikasi, analisis, dan penanganan risiko SPBE',
                'icon'        => 'shield-check',
                'route'       => $mrKonteks ? route('konteks.form', $mrKonteks) : null,
                'wireAction'  => $mrKonteks ? null : 'openModulMR',
                'active'      => true,
                'filled'      => (bool) $mrKonteks,
                'gradient'    => 'from-emerald-500 to-teal-600',
                'shadow'      => 'shadow-emerald-500/25',
                'bg'          => 'bg-emerald-500/10',
                'text'        => 'text-emerald-400',
                'border'      => 'border-emerald-500/20',
            ],
            [
                'name'        => 'Manajemen Pengetahuan',
                'description' => 'Pengelolaan dan berbagi pengetahuan organisasi',
                'icon'        => 'book-open',
                'route'       => null,
                'active'      => false,
                'filled'      => false,
                'gradient'    => 'from-blue-500 to-indigo-600',
                'shadow'      => 'shadow-blue-500/25',
                'bg'          => 'bg-blue-500/10',
                'text'        => 'text-blue-400',
                'border'      => 'border-blue-500/20',
            ],
            [
                'name'        => 'Manajemen Perubahan',
                'description' => 'Perencanaan dan pelaksanaan perubahan organisasi',
                'icon'        => 'arrows-right-left',
                'route'       => null,
                'active'      => false,
                'filled'      => false,
                'gradient'    => 'from-amber-500 to-orange-600',
                'shadow'      => 'shadow-amber-500/25',
                'bg'          => 'bg-amber-500/10',
                'text'        => 'text-amber-400',
                'border'      => 'border-amber-500/20',
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
    }
}
