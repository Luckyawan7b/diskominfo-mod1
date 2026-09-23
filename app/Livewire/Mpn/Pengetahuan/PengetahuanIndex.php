<?php
namespace App\Livewire\Mpn\Pengetahuan;

use App\Models\MpnKonteks;
use App\Models\MpnPengetahuan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PengetahuanIndex extends Component
{
    public MpnKonteks $konteks;

    public function mount(MpnKonteks $konteks): void
    {
        $this->konteks = $konteks;
    }

    public function deletePengetahuan(int $id): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $pengetahuan = MpnPengetahuan::where('mpn_konteks_id', $this->konteks->id)->findOrFail($id);
        $pengetahuan->delete(); 

        session()->flash('success', 'Pengetahuan berhasil dihapus.');
    }

    public function render()
    {
        $pengetahuans = $this->konteks->pengetahuan()->latest()->get();

        $user = auth()->user();
        
        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.pengetahuan.index', [
            'pengetahuans' => $pengetahuans,
            'isEditable'   => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb'   => [
                'Manajemen Pengetahuan' => auth()->user()->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks-mpn.form', $this->konteks),
                'Daftar Pengetahuan' => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
