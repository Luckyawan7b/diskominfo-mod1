<?php

namespace App\Livewire\LayananDigital;

use App\Models\MrKonteks;
use App\Models\MrLayananDigital;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LayananDigitalIndex extends Component
{
    public MrKonteks $konteks;
    public array $items = [];

    public function mount(MrKonteks $konteks): void
    {
        $this->konteks = $konteks;
        $this->loadItems();
    }

    public function loadItems(): void
    {
        $risikos = $this->konteks->risiko()
            ->whereHas('kolomTambahan', fn ($q) => $q->where('layanan_prioritas', 'Prioritas'))
            ->with(['kolomTambahan', 'layananDigital'])
            ->orderBy('kode_risiko')
            ->get();

        $this->items = [];
        foreach ($risikos as $risiko) {
            $ld = $risiko->layananDigital;
            $this->items[$risiko->id] = [
                'mr_risiko_id' => $risiko->id,
                'kode_risiko' => $risiko->kode_risiko,
                'besaran_risiko' => $risiko->besaran_risiko,
                'layanan_prioritas' => $risiko->kolomTambahan?->layanan_pendukung ?? '',
                'perlu_mkb' => $ld ? (bool) $ld->perlu_mkb : false,
                'pic' => $ld ? $ld->pic : '',
                'target_waktu_penyusunan' => $ld ? $ld->target_waktu_penyusunan : '',
            ];
        }
    }

    public function saveAll(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        if (!$this->isEditable()) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->items as $risikoId => $item) {
                MrLayananDigital::updateOrCreate(
                    ['mr_risiko_id' => $risikoId],
                    [
                        'perlu_mkb' => $item['perlu_mkb'],
                        'pic' => $item['pic'] ?: null,
                        'target_waktu_penyusunan' => $item['target_waktu_penyusunan'] ?: null,
                    ]
                );
            }
        });

        session()->flash('success', 'Data Layanan Digital Prioritas berhasil disimpan.');
        $this->loadItems();
    }

    public function isEditable(): bool
    {
        return $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin();
    }

    public function render()
    {
        $user = auth()->user();
        
        $availableKonteks = MrKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.layanan-digital.index', [
            'isEditable' => $this->isEditable(),
            'breadcrumb'      => [
                'Manajemen Risiko' => auth()->user()->isAdmin()
                    ? route('admin.review.konteks', $this->konteks->layanan_id)
                    : route('konteks.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks.form', $this->konteks),
                'Layanan Digital' => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks' => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
