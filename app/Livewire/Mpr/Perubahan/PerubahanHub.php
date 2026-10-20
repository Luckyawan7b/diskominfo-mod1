<?php

namespace App\Livewire\Mpr\Perubahan;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Services\MprAlurResolver;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Hub per perubahan: header, stepper, dan checklist formulir wajib/tidak wajib dari MprAlurResolver.
 */
#[Layout('components.layouts.app')]
class PerubahanHub extends Component
{
    public MprKonteks $konteks;
    public MprPerubahan $perubahan;

    public function mount(MprKonteks $konteks, MprPerubahan $perubahan): void
    {
        abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

        $this->konteks = $konteks;
        $this->perubahan = $perubahan;
    }

    public function render()
    {
        $user = auth()->user();

        $perubahan = $this->perubahan->fresh(['klasifikasi', 'lingkup']);
        $resolver = MprAlurResolver::untuk($perubahan);

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.perubahan.hub', [
            'p'                => $perubahan,
            'formulir'         => $resolver->formulir(),
            'kelengkapan'      => $resolver->kelengkapan(),
            'isEditable'       => $this->konteks->isEditableByOperator() && ! $user->isAdmin(),
            'breadcrumb'       => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                $perubahan->id_perubahan => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
