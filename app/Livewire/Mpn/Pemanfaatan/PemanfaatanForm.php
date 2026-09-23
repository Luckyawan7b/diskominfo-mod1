<?php

namespace App\Livewire\Mpn\Pemanfaatan;

use App\Models\MpnKonteks;
use App\Models\MpnPengetahuan;
use App\Models\MpnPengumpulan;
use App\Models\MpnPemanfaatan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PemanfaatanForm extends Component
{
    public MpnKonteks $konteks;
    public MpnPengetahuan $pengetahuan;
    public MpnPengumpulan $pengumpulan;

    public string $tanggal_pemanfaatan = '';
    public string $jenis_pengguna = 'Internal';
    public string $unit_pengguna = '';
    public string $tujuan_pemanfaatan = '';
    public ?int $rating = null;

    public function mount(MpnKonteks $konteks, MpnPengetahuan $pengetahuan, MpnPengumpulan $pengumpulan): void
    {
        $this->konteks = $konteks;
        $this->pengetahuan = $pengetahuan;
        $this->pengumpulan = $pengumpulan;
        $this->tanggal_pemanfaatan = date('Y-m-d');
    }

    public function savePemanfaatan(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $this->validate([
            'tanggal_pemanfaatan' => 'required|date',
            'jenis_pengguna'      => 'required|string',
            'unit_pengguna'       => 'required|string|max:191',
            'tujuan_pemanfaatan'  => 'required|string',
            'rating'              => 'nullable|integer|min:1|max:5',
        ]);

        MpnPemanfaatan::create([
            'mpn_pengumpulan_id'  => $this->pengumpulan->id,
            'tanggal_pemanfaatan' => $this->tanggal_pemanfaatan,
            'jenis_pengguna'      => $this->jenis_pengguna,
            'unit_pengguna'       => $this->unit_pengguna,
            'tujuan_pemanfaatan'  => $this->tujuan_pemanfaatan,
            'rating'              => $this->rating,
            'created_by'          => auth()->id(),
        ]);

        $this->reset(['unit_pengguna', 'tujuan_pemanfaatan', 'rating']);
        session()->flash('success', 'Catatan pemanfaatan & rating berhasil ditambahkan.');
    }

    public function deletePemanfaatan(int $id): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $pemanfaatan = MpnPemanfaatan::where('mpn_pengumpulan_id', $this->pengumpulan->id)->findOrFail($id);
        $pemanfaatan->delete();

        session()->flash('success', 'Catatan pemanfaatan berhasil dihapus.');
    }

    public function render()
    {
        $pemanfaatanList = $this->pengumpulan->pemanfaatan()->latest()->get();

        $user = auth()->user();
        
        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.pemanfaatan.form', [
            'pemanfaatanList' => $pemanfaatanList,
            'isEditable'      => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb'      => [
                'Manajemen Pengetahuan' => auth()->user()->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks-mpn.form', $this->konteks),
                'Pengetahuan' => route('mpn.pengetahuan.index', $this->konteks),
                $this->pengetahuan->nama_pengetahuan => route('mpn.pengetahuan.form', ['konteks' => $this->konteks, 'pengetahuan' => $this->pengetahuan]),
                'Pemanfaatan' => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
