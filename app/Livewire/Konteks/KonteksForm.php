<?php

namespace App\Livewire\Konteks;

use App\Models\MrKonteks;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KonteksForm extends Component
{
    public MrKonteks $konteks;

    public string $nama_instansi = '';
    public string $nama_upr = '';
    public string $tugas_upr = '';
    public string $fungsi_upr = '';
    public int $selera_risiko = 16;

    public function mount(MrKonteks $konteks): void
    {
        $this->konteks       = $konteks;
        $this->nama_instansi = $konteks->nama_instansi ?? '';
        $this->nama_upr      = $konteks->nama_upr ?? '';
        $this->tugas_upr     = $konteks->tugas_upr ?? '';
        $this->fungsi_upr    = $konteks->fungsi_upr ?? '';
        $this->selera_risiko = $konteks->selera_risiko ?? 16;
    }

    public function save(): void
    {
        $this->validate([
            'nama_instansi' => 'required|string|max:255',
            'nama_upr'      => 'required|string|max:255',
            'tugas_upr'     => 'nullable|string',
            'fungsi_upr'    => 'nullable|string',
            'selera_risiko' => 'required|integer|min:1|max:25',
        ]);

        $this->konteks->update([
            'nama_instansi' => $this->nama_instansi,
            'nama_upr'      => $this->nama_upr,
            'tugas_upr'     => $this->tugas_upr,
            'fungsi_upr'    => $this->fungsi_upr,
            'selera_risiko' => $this->selera_risiko,
        ]);

        session()->flash('success', 'Konteks berhasil disimpan.');
    }

    public function render()
    {
        $riskLabel = app(\App\Services\RiskMatrixCalculator::class)->label($this->selera_risiko);

        $user = auth()->user();
        // Gunakan scope accessibleBy: operator -> layanan miliknya; admin -> semua
        $availableKonteks = MrKonteks::accessibleBy($user)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.konteks.form', [
            'riskLabel'   => $riskLabel,
            'isEditable'  => $this->konteks->isEditableByOperator() || auth()->user()->isAdmin(),
            'breadcrumb'  => [
                'Manajemen Risiko' => route('konteks.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks' => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
