<?php

namespace App\Livewire\Risiko;

use App\Models\MrKonteks;
use App\Services\RiskMatrixCalculator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PetaRisiko extends Component
{
    public MrKonteks $konteks;
    public ?int $selectedK = null;
    public ?int $selectedD = null;

    public function mount(MrKonteks $konteks): void
    {
        $this->konteks = $konteks;
    }

    public function selectCell(?int $k, ?int $d): void
    {
        if ($this->selectedK === $k && $this->selectedD === $d) {
            $this->selectedK = null;
            $this->selectedD = null;
        } else {
            $this->selectedK = $k;
            $this->selectedD = $d;
        }
    }

    public function render()
    {
        $calc = app(RiskMatrixCalculator::class);
        $risikos = $this->konteks->risiko()->get();

        // Build 5x5 matrix counts
        $matrix = [];
        for ($k = 5; $k >= 1; $k--) {
            for ($d = 1; $d <= 5; $d++) {
                $count = $risikos->where('level_kemungkinan', $k)->where('level_dampak', $d)->count();
                $besaran = $calc->calculate($k, $d);
                $isAboveThreshold = $this->konteks->selera_risiko ? ($besaran > $this->konteks->selera_risiko) : false;

                $matrix[$k][$d] = [
                    'count'             => $count,
                    'besaran'           => $besaran,
                    'label'             => $calc->label($besaran),
                    'is_above_selera'   => $isAboveThreshold,
                ];
            }
        }

        // Filtered risks if cell selected
        $filteredRisikos = $risikos;
        if ($this->selectedK && $this->selectedD) {
            $filteredRisikos = $risikos->where('level_kemungkinan', $this->selectedK)->where('level_dampak', $this->selectedD);
        }

        $user = auth()->user();
        // Gunakan scope accessibleBy: operator -> layanan miliknya; admin -> semua
        $availableKonteks = MrKonteks::accessibleBy($user)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.risiko.peta', [
            'matrix'          => $matrix,
            'filteredRisikos' => $filteredRisikos,
            'calc'            => $calc,
            'breadcrumb'      => [
                'Manajemen Risiko' => route('konteks.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks.form', $this->konteks),
                'Peta Risiko' => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks' => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
