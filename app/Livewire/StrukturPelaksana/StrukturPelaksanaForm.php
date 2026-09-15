<?php

namespace App\Livewire\StrukturPelaksana;

use App\Models\MrKonteks;
use App\Models\MrStrukturPelaksana as StrukturModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StrukturPelaksanaForm extends Component
{
    public MrKonteks $konteks;
    public string $pemilik_risiko = '';
    public string $koordinator_risiko = '';
    public string $pengelola_risiko = '';

    public function mount(MrKonteks $konteks): void
    {
        $this->konteks = $konteks;
        $struktur = $konteks->strukturPelaksana;
        if ($struktur) {
            $this->pemilik_risiko     = $struktur->pemilik_risiko ?? '';
            $this->koordinator_risiko = $struktur->koordinator_risiko ?? '';
            $this->pengelola_risiko   = $struktur->pengelola_risiko ?? '';
        }
    }

    public function save(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }


        StrukturModel::updateOrCreate(
            ['mr_konteks_id' => $this->konteks->id],
            [
                'pemilik_risiko'     => $this->pemilik_risiko,
                'koordinator_risiko' => $this->koordinator_risiko,
                'pengelola_risiko'   => $this->pengelola_risiko,
            ]
        );
        session()->flash('success', 'Struktur pelaksana berhasil disimpan.');
    }

    public function render()
    {
        $user = auth()->user();
        
        $availableKonteks = MrKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.struktur-pelaksana.form', [
            'isEditable' => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb' => [
                'Manajemen Risiko' => auth()->user()->isAdmin()
                    ? route('admin.review.konteks', $this->konteks->layanan_id)
                    : route('konteks.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks.form', $this->konteks),
                'Struktur Pelaksana' => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks' => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
