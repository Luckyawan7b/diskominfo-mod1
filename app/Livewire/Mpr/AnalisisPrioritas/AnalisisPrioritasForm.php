<?php

namespace App\Livewire\Mpr\AnalisisPrioritas;

use App\Models\MprAnalisisPrioritas;
use App\Models\MprKonteks;
use App\Models\RefAspekPemdi;
use App\Models\RefIndikatorPemdi;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * F0 — Analisis Prioritas. Pola blok seperti SasaranForm.
 * `nama_layanan_prioritas` read-only (diisi otomatis oleh model, aturan K5).
 */
#[Layout('components.layouts.app')]
class AnalisisPrioritasForm extends Component
{
    public MprKonteks $konteks;

    /**
     * Tiap blok: id, nama_layanan_prioritas, kondisi_perlu_berubah, as_is, to_be,
     * prioritas, aspek_ids[], indikator_ids[].
     */
    public array $blocks = [];

    public function mount(MprKonteks $konteks): void
    {
        $this->konteks = $konteks;
        $this->loadBlocks();
    }

    public function loadBlocks(): void
    {
        $this->blocks = $this->konteks->analisisPrioritas()
            ->with(['aspek', 'indikator'])
            ->get()
            ->map(fn (MprAnalisisPrioritas $p) => [
                'id'                     => $p->id,
                'nama_layanan_prioritas' => $p->nama_layanan_prioritas,
                'kondisi_perlu_berubah'  => $p->kondisi_perlu_berubah ?? '',
                'as_is'                  => $p->as_is ?? '',
                'to_be'                  => $p->to_be ?? '',
                'prioritas'              => $p->prioritas,
                'aspek_ids'              => $p->aspek->pluck('id')->map(fn ($v) => (int) $v)->all(),
                'indikator_ids'          => $p->indikator->pluck('id')->map(fn ($v) => (int) $v)->all(),
            ])
            ->all();
    }

    private function guardWrite(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }
    }

    /**
     * Ambil model dari konteks ini saja (cegah manipulasi id dari klien).
     */
    private function modelUntukBlok(int $index): ?MprAnalisisPrioritas
    {
        $id = $this->blocks[$index]['id'] ?? null;

        return $id ? $this->konteks->analisisPrioritas()->find($id) : null;
    }

    public function addBlock(): void
    {
        $this->guardWrite();

        $p = $this->konteks->analisisPrioritas()->create([
            'kondisi_perlu_berubah' => '',
            'urutan'                => count($this->blocks),
        ]);

        $this->blocks[] = [
            'id'                     => $p->id,
            'nama_layanan_prioritas' => $p->nama_layanan_prioritas,
            'kondisi_perlu_berubah'  => '',
            'as_is'                  => '',
            'to_be'                  => '',
            'prioritas'              => null,
            'aspek_ids'              => [],
            'indikator_ids'          => [],
        ];
    }

    public function removeBlock(int $index): void
    {
        $this->guardWrite();

        $model = $this->modelUntukBlok($index);
        if (! $model) {
            return;
        }

        // Baris anak (pivot aspek/indikator) ikut terhapus; perubahan terkait di-null-kan oleh FK.
        $model->delete();

        unset($this->blocks[$index]);
        $this->blocks = array_values($this->blocks);

        session()->flash('success', 'Analisis prioritas berhasil dihapus.');
    }

    /**
     * Saat aspek berubah, buang indikator yang bukan milik aspek terpilih.
     */
    public function updated($name): void
    {
        if (preg_match('/^blocks\.(\d+)\.aspek_ids/', $name, $m)) {
            $i = (int) $m[1];
            $this->blocks[$i]['aspek_ids'] = array_values(array_map('intval', $this->blocks[$i]['aspek_ids'] ?? []));

            $valid = RefIndikatorPemdi::whereIn('ref_aspek_pemdi_id', $this->blocks[$i]['aspek_ids'])
                ->pluck('id')->map(fn ($v) => (int) $v)->all();

            $this->blocks[$i]['indikator_ids'] = array_values(array_intersect(
                array_map('intval', $this->blocks[$i]['indikator_ids'] ?? []),
                $valid
            ));
        }
    }

    public function saveBlock(int $index): void
    {
        $this->guardWrite();

        $model = $this->modelUntukBlok($index);
        if (! $model) {
            return;
        }

        $this->validate([
            "blocks.$index.kondisi_perlu_berubah" => 'required|string|max:5000',
            "blocks.$index.as_is"                 => 'nullable|string|max:5000',
            "blocks.$index.to_be"                 => 'nullable|string|max:5000',
            "blocks.$index.prioritas"             => 'nullable|integer|min:1|max:999',
            "blocks.$index.aspek_ids"             => 'array',
            "blocks.$index.aspek_ids.*"           => 'integer|exists:ref_aspek_pemdi,id',
            "blocks.$index.indikator_ids"         => 'array',
            "blocks.$index.indikator_ids.*"       => 'integer|exists:ref_indikator_pemdi,id',
        ], [
            "blocks.$index.kondisi_perlu_berubah.required" => 'Kondisi yang perlu berubah wajib diisi.',
            "blocks.$index.prioritas.integer"              => 'Prioritas harus berupa angka.',
        ]);

        $block = $this->blocks[$index];

        $aspekIds = array_values(array_unique(array_map('intval', $block['aspek_ids'] ?? [])));
        // Indikator hanya sah jika milik salah satu aspek terpilih.
        $indikatorIds = RefIndikatorPemdi::whereIn('ref_aspek_pemdi_id', $aspekIds)
            ->whereIn('id', array_map('intval', $block['indikator_ids'] ?? []))
            ->pluck('id')->all();

        $model->update([
            'kondisi_perlu_berubah' => $block['kondisi_perlu_berubah'],
            'as_is'                 => filled($block['as_is'] ?? null) ? $block['as_is'] : null,
            'to_be'                 => filled($block['to_be'] ?? null) ? $block['to_be'] : null,
            'prioritas'             => filled($block['prioritas'] ?? null) ? (int) $block['prioritas'] : null,
        ]);
        $model->aspek()->sync($aspekIds);
        $model->indikator()->sync($indikatorIds);

        $this->blocks[$index]['indikator_ids'] = array_map('intval', $indikatorIds);

        session()->flash('success', 'Analisis prioritas berhasil disimpan.');
    }

    public function render()
    {
        $user = auth()->user();

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        $aspekList = RefAspekPemdi::orderBy('id')->get();

        // Indikator difilter dari aspek terpilih di tiap blok.
        $indikatorPerBlok = [];
        foreach ($this->blocks as $i => $block) {
            $indikatorPerBlok[$i] = empty($block['aspek_ids'])
                ? collect()
                : RefIndikatorPemdi::whereIn('ref_aspek_pemdi_id', $block['aspek_ids'])->orderBy('id')->get();
        }

        return view('livewire.mpr.analisis-prioritas.form', [
            'isEditable'       => $this->konteks->isEditableByOperator() && ! $user->isAdmin(),
            'aspekList'        => $aspekList,
            'indikatorPerBlok' => $indikatorPerBlok,
            'breadcrumb'       => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Analisis Prioritas' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
