<?php

namespace App\Livewire\Mpn\IndikatorCapaian;

use App\Models\MpnEvaluasiIndikator;
use App\Models\MpnIndikatorCapaian;
use App\Models\MpnKonteks;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IndikatorCapaianForm extends Component
{
    public MpnKonteks $konteks;

    /**
     * Array blok indikator capaian. Tiap blok:
     * [
     *   'id'             => int,       // MpnIndikatorCapaian->id
     *   'indikator'      => string,
     *   'kondisi_as_is'  => string,
     *   'kondisi_to_be'  => string,
     *   'evaluasi' => [
     *       'id'              => int|null,  // MpnEvaluasiIndikator->id
     *       'realisasi'       => string,
     *       'analisis'        => string,
     *       'tindak_lanjut'   => string,
     *       'pelaksana_terkait' => string,
     *   ]
     * ]
     */
    public array $blocks = [];

    public function mount(MpnKonteks $konteks): void
    {
        $this->konteks = $konteks;
        $this->loadBlocks();
    }

    public function loadBlocks(): void
    {
        $this->blocks = $this->konteks->indikatorCapaian()
            ->with('evaluasi')
            ->get()
            ->map(fn (MpnIndikatorCapaian $ind) => [
                'id'            => $ind->id,
                'indikator'     => $ind->indikator ?? '',
                'kondisi_as_is' => $ind->kondisi_as_is ?? '',
                'kondisi_to_be' => $ind->kondisi_to_be ?? '',
                'evaluasi'      => [
                    'id'                => $ind->evaluasi?->id,
                    'realisasi'         => (string) ($ind->evaluasi?->realisasi ?? ''),
                    'analisis'          => $ind->evaluasi?->analisis ?? '',
                    'tindak_lanjut'     => $ind->evaluasi?->tindak_lanjut ?? '',
                    'pelaksana_terkait' => $ind->evaluasi?->pelaksana_terkait ?? '',
                    'gap'               => $ind->evaluasi?->gap,
                ],
            ])
            ->toArray();
    }

    // ─── Block Actions ────────────────────────────────────────────────────────

    public function addBlock(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $ind = MpnIndikatorCapaian::create([
            'mpn_konteks_id' => $this->konteks->id,
            'urutan'         => count($this->blocks),
            'indikator'      => '',
        ]);

        $this->blocks[] = [
            'id'            => $ind->id,
            'indikator'     => '',
            'kondisi_as_is' => '',
            'kondisi_to_be' => '',
            'evaluasi'      => [
                'id'                => null,
                'realisasi'         => '',
                'analisis'          => '',
                'tindak_lanjut'     => '',
                'pelaksana_terkait' => '',
                'gap'               => null,
            ],
        ];
    }

    public function removeBlock(int $index): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $block = $this->blocks[$index] ?? null;
        if (! $block || ! $block['id']) {
            return;
        }

        MpnIndikatorCapaian::find($block['id'])?->delete();

        unset($this->blocks[$index]);
        $this->blocks = array_values($this->blocks);

        session()->flash('success', 'Indikator capaian berhasil dihapus.');
    }

    public function saveBlock(int $index): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $block = $this->blocks[$index] ?? null;
        if (! $block || ! $block['id']) {
            return;
        }

        $this->validateOnly("blocks.{$index}.indikator", [
            "blocks.{$index}.indikator" => 'required|string|max:500',
        ], [
            "blocks.{$index}.indikator.required" => 'Deskripsi indikator wajib diisi.',
        ]);

        // Simpan indikator capaian
        $ind = MpnIndikatorCapaian::find($block['id']);
        $ind?->update([
            'indikator'      => $block['indikator'],
            'kondisi_as_is'  => $block['kondisi_as_is'],
            'kondisi_to_be'  => $block['kondisi_to_be'],
        ]);

        // Simpan / buat evaluasi indikator (1:1)
        $evalData = $block['evaluasi'];
        $realisasi = filled($evalData['realisasi']) ? (float) $evalData['realisasi'] : null;

        if ($evalData['id']) {
            MpnEvaluasiIndikator::find($evalData['id'])?->update([
                'realisasi'         => $realisasi,
                'analisis'          => $evalData['analisis'],
                'tindak_lanjut'     => $evalData['tindak_lanjut'],
                'pelaksana_terkait' => $evalData['pelaksana_terkait'],
            ]);
        } else {
            // Hanya buat evaluasi jika ada data realisasi atau analisis
            $hasEvalData = filled($realisasi) || filled($evalData['analisis']) || filled($evalData['tindak_lanjut']);
            if ($hasEvalData) {
                $newEval = MpnEvaluasiIndikator::create([
                    'mpn_indikator_capaian_id' => $block['id'],
                    'realisasi'                => $realisasi,
                    'analisis'                 => $evalData['analisis'],
                    'tindak_lanjut'            => $evalData['tindak_lanjut'],
                    'pelaksana_terkait'        => $evalData['pelaksana_terkait'],
                ]);
                $this->blocks[$index]['evaluasi']['id'] = $newEval->id;
            }
        }

        // Refresh gap value dari DB
        if ($this->blocks[$index]['evaluasi']['id']) {
            $evalFresh = MpnEvaluasiIndikator::with('indikatorCapaian')->find($this->blocks[$index]['evaluasi']['id']);
            $this->blocks[$index]['evaluasi']['gap'] = $evalFresh?->gap;
        }

        session()->flash('success', 'Indikator Capaian berhasil disimpan.');
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $user = auth()->user();

        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.indikator-capaian.form', [
            'isEditable'       => $this->konteks->isEditableByOperator() && !$user->isAdmin(),
            'availableKonteks' => $availableKonteks,
            'breadcrumb'       => [
                'Manajemen Pengetahuan' => $user->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks-mpn.form', $this->konteks),
                'Indikator Capaian' => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
