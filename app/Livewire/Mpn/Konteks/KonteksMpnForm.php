<?php

namespace App\Livewire\Mpn\Konteks;

use App\Models\MpnKonteks;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KonteksMpnForm extends Component
{
    public MpnKonteks $konteks;

    public function mount(MpnKonteks $konteks): void
    {
        $this->konteks = $konteks;
    }

    public function render()
    {
        $user = auth()->user();

        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.konteks.form', [
            'isEditable'       => $this->konteks->isEditableByOperator() && !$user->isAdmin(),
            'availableKonteks' => $availableKonteks,
            'breadcrumb'       => [
                'Manajemen Pengetahuan' => $user->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
