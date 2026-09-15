<?php

namespace App\Livewire\Admin;

use App\Models\Layanan;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Monitoring Admin — Halaman ini berfungsi sebagai monitoring read-only.
 * Tidak ada lagi alur approval/rejection — admin hanya melihat data yang telah diisi operator.
 */
#[Layout('components.layouts.app')]
class ReviewIndex extends Component
{
    public string $search = '';
    public string $filterDinas = '';

    public function render()
    {
        $query = Layanan::with('creator')
            ->withCount('mrKonteksHistory')
            ->withMax('mrKonteksHistory', 'updated_at');

        $query->when($this->search, function ($q) {
            $term = "%{$this->search}%";
            $q->where(function ($qq) use ($term) {
                $qq->where('nama_layanan', 'like', $term)
                   ->orWhereHas('creator', fn ($c) => $c->where('nama_dinas', 'like', $term));
            });
        });

        $query->when($this->filterDinas, function ($q) {
            $q->whereHas('creator', fn ($c) => $c->where('nama_dinas', $this->filterDinas));
        });

        $layananList = $query->orderByDesc('updated_at')->get();

        $dinasList = User::whereNotNull('nama_dinas')->distinct()->orderBy('nama_dinas')->pluck('nama_dinas');

        return view('livewire.admin.review', [
            'layananList' => $layananList,
            'dinasList'   => $dinasList,
            'breadcrumb'  => [
                'Admin'      => null,
                'Monitoring' => null,
            ],
        ]);
    }
}
