<?php

namespace App\Livewire\Mpn\Pengumpulan;

use App\Models\MpnKonteks;
use App\Models\MpnPengetahuan;
use App\Models\MpnPengumpulan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PengumpulanIndex extends Component
{
    public MpnKonteks $konteks;
    public MpnPengetahuan $pengetahuan;

    public function mount(MpnKonteks $konteks, MpnPengetahuan $pengetahuan): void
    {
        $this->konteks = $konteks;
        $this->pengetahuan = $pengetahuan;
    }

    public function deletePengumpulan(int $id): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $pengumpulan = MpnPengumpulan::where('mpn_pengetahuan_id', $this->pengetahuan->id)->findOrFail($id);
        
        // Prevent deleting if it has revisions (children)
        if ($pengumpulan->revisi()->count() > 0) {
            session()->flash('error', 'Tidak dapat menghapus riwayat ini karena memiliki revisi turunan.');
            return;
        }

        $pengumpulan->delete();
        session()->flash('success', 'Data pengumpulan berhasil dihapus.');
    }

    public function render()
    {
        // Get all pengumpulan for this pengetahuan, order by latest
        $pengumpulanList = $this->pengetahuan->pengumpulan()
            ->with(['revisiDari', 'metodePengolahan'])
            ->latest()
            ->get();

        $user = auth()->user();
        
        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.pengumpulan.index', [
            'pengumpulanList' => $pengumpulanList,
            'isEditable'      => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb'      => [
                'Manajemen Pengetahuan' => auth()->user()->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks-mpn.form', $this->konteks),
                'Pengetahuan' => route('mpn.pengetahuan.index', $this->konteks),
                $this->pengetahuan->nama_pengetahuan => route('mpn.pengetahuan.form', ['konteks' => $this->konteks, 'pengetahuan' => $this->pengetahuan]),
                'Riwayat Revisi' => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
