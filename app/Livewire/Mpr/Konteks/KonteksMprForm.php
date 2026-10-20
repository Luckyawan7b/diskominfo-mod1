<?php

namespace App\Livewire\Mpr\Konteks;

use App\Models\MprKonteks;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KonteksMprForm extends Component
{
    public MprKonteks $konteks;

    public function mount(MprKonteks $konteks): void
    {
        $this->konteks = $konteks;
    }

    public function render()
    {
        $user = auth()->user();

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.konteks.form', [
            'isEditable'       => $this->konteks->isEditableByOperator() && ! $user->isAdmin(),
            'availableKonteks' => $availableKonteks,
            'perubahanList'    => $this->konteks->perubahan()
                ->with(['klasifikasi', 'lingkup'])
                ->orderByDesc('id')
                ->get(),
            'jumlahPrioritas'  => $this->konteks->analisisPrioritas()->count(),
            'breadcrumb'       => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
