<?php

namespace App\Livewire\Mpr\Konteks;

use App\Models\Layanan;
use App\Models\MprKonteks;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KonteksMprIndex extends Component
{
    public ?Layanan $activeLayanan = null;

    // ─── State modal buat konteks baru ────────────────────────────────────────
    public bool $showCreateModal = false;
    public int $newTahun = 0;
    public string $newPeriode = '';

    public function mount(?Layanan $layanan = null): void
    {
        $this->newTahun = (int) date('Y');

        $user = auth()->user();

        if ($layanan && $layanan->exists) {
            $this->activeLayanan = $layanan;
            return;
        }

        if ($user->isAdmin() && ! $layanan) {
            abort(403, 'Admin hanya dapat mengakses konteks MPR melalui menu Monitoring per Layanan.');
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
            $query = MprKonteks::whereHas('layanan', fn ($q) => $q->where('created_by', $user->id))
                ->with(['layanan.creator'])
                ->withCount('perubahan');
        } else {
            $query = MprKonteks::with(['layanan.creator'])->withCount('perubahan');
        }

        if ($this->activeLayanan) {
            $query->where('layanan_id', $this->activeLayanan->id);
        }

        $konteks = $query->orderByDesc('tahun_penilaian')->get();

        return view('livewire.mpr.konteks.index', [
            'konteks'    => $konteks,
            'breadcrumb' => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->activeLayanan?->id ?? 0)
                    : route('konteks-mpr.index'),
                ($this->activeLayanan ? $this->activeLayanan->nama_layanan : 'Daftar Konteks') => null,
            ],
        ]);
    }

    public function createKonteks(): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            abort(403, 'Admin tidak dapat membuat konteks MPR secara langsung.');
        }

        if (! $this->activeLayanan) {
            $this->addError('newTahun', 'Silakan pilih layanan terlebih dahulu melalui Dashboard.');
            return;
        }

        $this->validate([
            'newTahun' => [
                'required',
                'integer',
                'min:2020',
                'max:2099',
                Rule::unique('mpr_konteks', 'tahun_penilaian')
                    ->where('layanan_id', $this->activeLayanan->id),
            ],
            'newPeriode' => 'nullable|string|max:100',
        ], [
            'newTahun.unique' => 'Konteks Manajemen Perubahan untuk Tahun Penilaian ini sudah ada.',
        ]);

        $konteks = MprKonteks::create([
            'layanan_id'           => $this->activeLayanan->id,
            'nama_instansi'        => $this->activeLayanan->creator?->nama_dinas ?? '',
            'tahun_penilaian'      => $this->newTahun,
            'periode_perencanaan'  => filled($this->newPeriode) ? trim($this->newPeriode) : null,
            'created_by'           => $user->id,
        ]);

        $this->showCreateModal = false;
        $this->redirect(route('konteks-mpr.form', $konteks), navigate: true);
    }
}
