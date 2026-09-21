<?php
namespace App\Livewire\Risiko;

use App\Models\MrKonteks;
use App\Models\MrRisiko;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RisikoIndex extends Component
{
    public MrKonteks $konteks;

    public function mount(MrKonteks $konteks): void { $this->konteks = $konteks; }

    public function deleteRisiko(int $id): void
    {
        // Admin = read-only untuk data operasional
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        // WAJIB scope ke konteks milik operator ini — cegah IDOR
        $risiko = MrRisiko::where('mr_konteks_id', $this->konteks->id)->findOrFail($id);
        $risiko->delete(); // soft delete — hanya baris ini yang terhapus

        session()->flash('success', 'Risiko ' . $risiko->kode_risiko . ' berhasil dihapus.');
    }

    public function render()
    {
        // Hapus filterStatus (kolom tidak ada), hapus kategoriRisiko relasi (sudah jadi teks)
        $risikos = $this->konteks->risiko()->orderBy('prioritas_risiko')->get();

        $user = auth()->user();
        
        $availableKonteks = MrKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.risiko.index', [
            'risikos'    => $risikos,
            'isEditable' => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb' => [
                'Manajemen Risiko' => auth()->user()->isAdmin()
                    ? route('admin.review.konteks', $this->konteks->layanan_id)
                    : route('konteks.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks.form', $this->konteks),
                'Daftar Risiko' => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks'          => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
