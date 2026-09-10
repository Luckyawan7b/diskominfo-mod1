<?php

namespace App\Livewire\Admin;

use App\Models\Layanan;
use App\Models\MrKonteks;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Monitoring Admin — Halaman ini berfungsi sebagai monitoring read-only.
 * Tidak ada lagi alur approval/rejection — admin hanya melihat data yang telah diisi operator.
 */
#[Layout('components.layouts.app')]
class ReviewIndex extends Component
{
    public string $filterDinas = '';

    public function render()
    {
        $query = MrKonteks::with(['layanan.creator', 'risiko'])->withCount('risiko');

        if ($this->filterDinas) {
            $query->whereHas('layanan.creator', fn ($q) => $q->where('nama_dinas', 'like', "%{$this->filterDinas}%"));
        }

        $konteksList = $query->orderByDesc('updated_at')->get();

        // Daftar dinas unik untuk filter dropdown
        $dinasList = \App\Models\User::whereNotNull('nama_dinas')
            ->distinct()
            ->orderBy('nama_dinas')
            ->pluck('nama_dinas');

        return view('livewire.admin.review', [
            'konteksList' => $konteksList,
            'dinasList'   => $dinasList,
            'breadcrumb'  => [
                'Admin'      => null,
                'Monitoring' => null,
            ],
        ]);
    }
}
