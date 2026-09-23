<?php

namespace App\Livewire\Mpn\AlihPengetahuan;

use App\Models\MpnKonteks;
use App\Models\MpnPengetahuan;
use App\Models\MpnPengumpulan;
use App\Models\MpnAlihPengetahuan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AlihPengetahuanForm extends Component
{
    public MpnKonteks $konteks;
    public MpnPengetahuan $pengetahuan;
    public MpnPengumpulan $pengumpulan;

    public string $tanggal_kegiatan = '';
    public bool $metode_pelatihan = false;
    public bool $metode_workshop = false;
    public bool $metode_sosialisasi = false;
    public bool $metode_mentoring = false;
    public bool $metode_sharing = false;
    public bool $metode_lainnya = false;
    public string $keterangan_lainnya = '';
    public string $penerima_pengetahuan = '';
    public string $hasil_evaluasi = '';

    public function mount(MpnKonteks $konteks, MpnPengetahuan $pengetahuan, MpnPengumpulan $pengumpulan): void
    {
        $this->konteks = $konteks;
        $this->pengetahuan = $pengetahuan;
        $this->pengumpulan = $pengumpulan;
        $this->tanggal_kegiatan = date('Y-m-d');
    }

    public function saveAlihPengetahuan(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $this->validate([
            'tanggal_kegiatan'     => 'required|date',
            'penerima_pengetahuan' => 'required|string|max:191',
            'hasil_evaluasi'       => 'nullable|string',
            'keterangan_lainnya'   => 'required_if:metode_lainnya,true'
        ]);

        MpnAlihPengetahuan::create([
            'mpn_pengumpulan_id'   => $this->pengumpulan->id,
            'tanggal_kegiatan'     => $this->tanggal_kegiatan,
            'metode_pelatihan'     => $this->metode_pelatihan,
            'metode_workshop'      => $this->metode_workshop,
            'metode_sosialisasi'   => $this->metode_sosialisasi,
            'metode_mentoring'     => $this->metode_mentoring,
            'metode_sharing'       => $this->metode_sharing,
            'metode_lainnya'       => $this->metode_lainnya,
            'keterangan_lainnya'   => $this->metode_lainnya ? $this->keterangan_lainnya : null,
            'penerima_pengetahuan' => $this->penerima_pengetahuan,
            'hasil_evaluasi'       => $this->hasil_evaluasi,
        ]);

        $this->reset([
            'metode_pelatihan', 'metode_workshop', 'metode_sosialisasi', 
            'metode_mentoring', 'metode_sharing', 'metode_lainnya', 
            'keterangan_lainnya', 'penerima_pengetahuan', 'hasil_evaluasi'
        ]);
        
        session()->flash('success', 'Catatan alih pengetahuan berhasil ditambahkan.');
    }

    public function deleteAlihPengetahuan(int $id): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $alih = MpnAlihPengetahuan::where('mpn_pengumpulan_id', $this->pengumpulan->id)->findOrFail($id);
        $alih->delete();

        session()->flash('success', 'Catatan alih pengetahuan berhasil dihapus.');
    }

    public function render()
    {
        $alihList = $this->pengumpulan->alihPengetahuan()->latest()->get();

        $user = auth()->user();
        
        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.alih-pengetahuan.form', [
            'alihList'        => $alihList,
            'isEditable'      => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb'      => [
                'Manajemen Pengetahuan' => auth()->user()->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks-mpn.form', $this->konteks),
                'Pengetahuan' => route('mpn.pengetahuan.index', $this->konteks),
                $this->pengetahuan->nama_pengetahuan => route('mpn.pengetahuan.form', ['konteks' => $this->konteks, 'pengetahuan' => $this->pengetahuan]),
                'Alih Pengetahuan' => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
