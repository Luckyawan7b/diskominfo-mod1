<?php

namespace App\Livewire\Admin;

use App\Models\MrKonteks;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Monitoring Detail Admin — Halaman ini bersifat read-only.
 * Tidak ada tombol Approve/Reject. Admin hanya melihat data risiko yang diisi operator.
 */
#[Layout('components.layouts.app')]
class ReviewDetail extends Component
{
    public MrKonteks $konteks;

    public function mount(MrKonteks $konteks): void
    {
        $this->konteks = $konteks->load('layanan.creator');
    }

    public function render()
    {
        $risikos = $this->konteks->risiko()
            ->with(['sasaran', 'perlakuan', 'residual', 'kolomTambahan', 'layananDigital'])
            ->orderBy('prioritas_risiko')
            ->get();

        // Nama instansi: dari nama_instansi pada konteks, atau fallback ke creator->nama_dinas
        $namaInstansi = $this->konteks->nama_instansi
            ?: ($this->konteks->layanan?->creator?->nama_dinas ?? 'Perangkat Daerah');

        return view('livewire.admin.review-detail', [
            'risikos'      => $risikos,
            'namaInstansi' => $namaInstansi,
            'breadcrumb'   => [
                'Admin'      => null,
                'Monitoring' => route('admin.review.index'),
                $namaInstansi . ' — ' . ($this->konteks->layanan?->nama_layanan ?? 'Layanan') => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks' => $this->konteks,
        ]);
    }
}
