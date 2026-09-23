<?php

namespace App\Livewire\Mpn\Konteks;

use App\Models\Layanan;
use App\Models\MpnKonteks;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KonteksMpnIndex extends Component
{
    public ?Layanan $activeLayanan = null;

    // ─── State modal buat konteks baru ────────────────────────────────────────
    public bool $showCreateModal = false;
    public int $newTahun         = 0;
    public int $newTahunPelaksanaan = 0;

    public function mount(?Layanan $layanan = null): void
    {
        $this->newTahun            = (int) date('Y');
        $this->newTahunPelaksanaan = (int) date('Y');

        $user = auth()->user();

        if ($layanan && $layanan->exists) {
            $this->activeLayanan = $layanan;
            return;
        }

        if ($user->isAdmin() && !$layanan) {
            abort(403, 'Admin hanya dapat mengakses konteks MPN melalui menu Monitoring per Layanan.');
        }

        if ($user->isOperator()) {
            $layananId = session('active_layanan_id');
            if ($layananId) {
                $this->activeLayanan = Layanan::where('created_by', $user->id)->find($layananId);
            }
        }
    }

    public function render()
    {
        $user = auth()->user();

        if ($user->isOperator()) {
            $query = MpnKonteks::whereHas('layanan', fn ($q) => $q->where('created_by', $user->id))
                ->with(['layanan.creator'])
                ->withCount('pengetahuan');

            if ($this->activeLayanan) {
                $query->where('layanan_id', $this->activeLayanan->id);
            }
        } else {
            // Admin: konteks berdasarkan layanan yang sedang di-review
            $query = MpnKonteks::with(['layanan.creator'])->withCount('pengetahuan');
            if ($this->activeLayanan) {
                $query->where('layanan_id', $this->activeLayanan->id);
            }
        }

        $konteks = $query->orderByDesc('tahun_penilaian')->get();

        return view('livewire.mpn.konteks.index', [
            'konteks'   => $konteks,
            'breadcrumb' => [
                'Manajemen Pengetahuan' => $user->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->activeLayanan?->id ?? 0)
                    : route('konteks-mpn.index'),
                ($this->activeLayanan ? $this->activeLayanan->nama_layanan : 'Daftar Konteks') => null,
            ],
        ]);
    }

    public function createKonteks(): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            abort(403, 'Admin tidak dapat membuat konteks MPN secara langsung.');
        }

        if (!$this->activeLayanan) {
            $this->addError('newTahun', 'Silakan pilih layanan terlebih dahulu melalui Dashboard.');
            return;
        }

        $this->validate([
            'newTahunPelaksanaan' => 'required|integer|min:2020|max:2099',
            'newTahun'            => [
                'required',
                'integer',
                'min:2020',
                'max:2099',
                Rule::unique('mpn_konteks', 'tahun_penilaian')
                    ->where('layanan_id', $this->activeLayanan->id),
            ],
        ], [
            'newTahun.unique' => 'Konteks Manajemen Pengetahuan untuk Tahun Penilaian ini sudah ada.',
        ]);

        $konteks = MpnKonteks::create([
            'layanan_id'        => $this->activeLayanan->id,
            'tahun_penilaian'   => $this->newTahun,
            'tahun_pelaksanaan' => $this->newTahunPelaksanaan,
            'created_by'        => $user->id,
        ]);

        $this->showCreateModal = false;
        $this->redirect(route('konteks-mpn.form', $konteks), navigate: true);
    }
}
