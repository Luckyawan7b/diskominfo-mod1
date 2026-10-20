<?php

namespace App\Livewire\Mpr\Perubahan;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\RefKlasifikasiPerubahan;
use App\Models\RefLingkupPerubahan;
use App\Services\MprAlurResolver;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PerubahanIndex extends Component
{
    public MprKonteks $konteks;

    public string $search = '';
    public string $filterKlasifikasi = '';
    public string $filterLingkup = '';

    public function mount(MprKonteks $konteks): void
    {
        $this->konteks = $konteks;
    }

    public function hapus(int $id): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        // Soft delete; hanya perubahan milik konteks ini.
        $this->konteks->perubahan()->findOrFail($id)->delete();

        session()->flash('success', 'Perubahan berhasil dihapus.');
    }

    public function render()
    {
        $user = auth()->user();

        $withCount = collect(MprAlurResolver::FORMULIR)->pluck('relasi')->all();

        $perubahan = $this->konteks->perubahan()
            ->with(['klasifikasi', 'lingkup'])
            ->withCount($withCount)
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(fn ($qq) => $qq->where('id_perubahan', 'like', $term)
                    ->orWhere('nama_layanan_digital', 'like', $term));
            })
            ->when($this->filterKlasifikasi !== '', fn ($q) => $q->where('ref_klasifikasi_perubahan_id', $this->filterKlasifikasi))
            ->when($this->filterLingkup !== '', fn ($q) => $q->where('ref_lingkup_perubahan_id', $this->filterLingkup))
            ->orderByDesc('id')
            ->get()
            ->each(fn (MprPerubahan $p) => $p->setAttribute('kelengkapan', MprAlurResolver::untuk($p)->kelengkapan()));

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.perubahan.index', [
            'perubahanList'  => $perubahan,
            'klasifikasiRef' => RefKlasifikasiPerubahan::orderBy('id')->get(),
            'lingkupRef'     => RefLingkupPerubahan::orderBy('id')->get(),
            'isEditable'     => $this->konteks->isEditableByOperator() && ! $user->isAdmin(),
            'breadcrumb'     => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
