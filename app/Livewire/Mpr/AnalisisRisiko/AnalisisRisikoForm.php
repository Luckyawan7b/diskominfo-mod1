<?php

namespace App\Livewire\Mpr\AnalisisRisiko;

use App\Models\MprAnalisisRisiko;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\MrRisiko;
use App\Rules\KodeRisikoMprUnik;
use App\Services\MprAlurResolver;
use App\Services\RiskMatrixCalculator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * F2A — Analisis Risiko Perubahan (MPR02A).
 * Pola blok seperti AnalisisPrioritasForm, dengan besaran dan label 3 level (labelMpr).
 */
#[Layout('components.layouts.app')]
class AnalisisRisikoForm extends Component
{
    public MprKonteks $konteks;
    public MprPerubahan $perubahan;

    /**
     * Tiap blok: id (?int), kode_risiko, sumber_risiko ('baru'|'dari_mr'), mr_risiko_id (?int),
     * risiko, penyebab, level_kemungkinan (?int), level_dampak (?int), perencanaan_keberlangsungan.
     */
    public array $blocks = [];

    public function mount(MprKonteks $konteks, MprPerubahan $perubahan): void
    {
        abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

        $this->konteks = $konteks;
        $this->perubahan = $perubahan;
        $this->loadBlocks();
    }

    public function loadBlocks(): void
    {
        $this->blocks = $this->perubahan->analisisRisiko()
            ->orderBy('id')
            ->get()
            ->map(fn (MprAnalisisRisiko $r) => [
                'id'                          => $r->id,
                'kode_risiko'                 => $r->kode_risiko,
                'sumber_risiko'               => $r->sumber_risiko ?: 'baru',
                'mr_risiko_id'                => $r->mr_risiko_id,
                'risiko'                      => $r->risiko ?? '',
                'penyebab'                    => $r->penyebab ?? '',
                'level_kemungkinan'           => $r->level_kemungkinan,
                'level_dampak'                => $r->level_dampak,
                'perencanaan_keberlangsungan' => $r->perencanaan_keberlangsungan ?? '',
            ])
            ->all();
    }

    private function isWajib(): bool
    {
        return MprAlurResolver::untuk($this->perubahan->fresh(['klasifikasi', 'lingkup']))->perluAnalisisTeknis();
    }

    private function guardWrite(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        if (! $this->isWajib()) {
            abort(403, 'Formulir Analisis Risiko tidak wajib untuk klasifikasi dan lingkup perubahan ini.');
        }
    }

    /**
     * Ambil model risiko milik perubahan ini saja (cegah manipulasi id dari klien).
     */
    private function modelUntukBlok(int $index): ?MprAnalisisRisiko
    {
        $id = $this->blocks[$index]['id'] ?? null;

        return $id ? $this->perubahan->analisisRisiko()->find($id) : null;
    }

    public function addBlock(): void
    {
        $this->guardWrite();

        $this->blocks[] = [
            'id'                          => null,
            'kode_risiko'                 => '',
            'sumber_risiko'               => 'baru',
            'mr_risiko_id'                => null,
            'risiko'                      => '',
            'penyebab'                    => '',
            'level_kemungkinan'           => null,
            'level_dampak'                => null,
            'perencanaan_keberlangsungan' => '',
        ];
    }

    public function removeBlock(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->blocks[$index])) {
            return;
        }

        $id = $this->blocks[$index]['id'] ?? null;
        if ($id !== null) {
            $model = $this->modelUntukBlok($index);
            if (! $model) {
                return;
            }
            $model->delete(); // Soft delete
        }

        unset($this->blocks[$index]);
        $this->blocks = array_values($this->blocks);

        $pesan = 'Analisis risiko berhasil dihapus.';
        session()->flash('success', $pesan);
        $this->dispatch('risiko-mpr-deleted', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Dihapus');
    }

    /**
     * Saat sumber_risiko diubah atau mr_risiko_id dipilih, sesuaikan prefill dari MrRisiko layanan yang sama.
     */
    public function updated($name, $value): void
    {
        if (preg_match('/^blocks\.(\d+)\.sumber_risiko$/', $name, $m)) {
            $i = (int) $m[1];
            if (($this->blocks[$i]['sumber_risiko'] ?? 'baru') !== 'dari_mr') {
                $this->blocks[$i]['mr_risiko_id'] = null;
            }
        }

        if (preg_match('/^blocks\.(\d+)\.mr_risiko_id$/', $name, $m)) {
            $i = (int) $m[1];
            $mrId = filled($value) ? (int) $value : null;
            $this->blocks[$i]['mr_risiko_id'] = $mrId;

            if ($mrId && ($this->blocks[$i]['sumber_risiko'] ?? null) === 'dari_mr') {
                $mr = MrRisiko::query()
                    ->whereHas('konteks', fn ($q) => $q->where('layanan_id', $this->konteks->layanan_id))
                    ->find($mrId);

                if ($mr) {
                    $this->blocks[$i]['risiko']            = $mr->peristiwa_risiko ?? '';
                    $this->blocks[$i]['penyebab']          = $mr->penyebab ?? '';
                    $this->blocks[$i]['level_kemungkinan'] = $mr->level_kemungkinan;
                    $this->blocks[$i]['level_dampak']      = $mr->level_dampak;
                }
            }
        }
    }

    public function saveBlock(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->blocks[$index])) {
            return;
        }

        $block = $this->blocks[$index];
        $existingId = $block['id'] ?? null;
        $model = null;

        if ($existingId !== null) {
            $model = $this->modelUntukBlok($index);
            if (! $model) {
                return;
            }
        }

        $sumber = $block['sumber_risiko'] ?? 'baru';

        $mrRisikoRule = ['nullable'];
        if ($sumber === 'dari_mr') {
            $mrRisikoRule = [
                'required',
                'integer',
                function (string $attr, mixed $val, \Closure $fail) {
                    $valid = MrRisiko::query()
                        ->whereKey($val)
                        ->whereHas('konteks', fn ($q) => $q->where('layanan_id', $this->konteks->layanan_id))
                        ->exists();

                    if (! $valid) {
                        $fail('Risiko MR tidak valid untuk layanan ini.');
                    }
                },
            ];
        }

        $this->validate([
            "blocks.$index.kode_risiko"                 => [
                'required',
                'string',
                'max:100',
                new KodeRisikoMprUnik($this->konteks->layanan_id, $model?->id),
            ],
            "blocks.$index.sumber_risiko"               => ['required', Rule::in(['baru', 'dari_mr'])],
            "blocks.$index.mr_risiko_id"                => $mrRisikoRule,
            "blocks.$index.risiko"                      => 'required|string|max:5000',
            "blocks.$index.penyebab"                    => 'nullable|string|max:5000',
            "blocks.$index.level_kemungkinan"           => 'nullable|integer|min:1|max:5',
            "blocks.$index.level_dampak"                => 'nullable|integer|min:1|max:5',
            "blocks.$index.perencanaan_keberlangsungan" => 'nullable|string|max:5000',
        ], [
            "blocks.$index.kode_risiko.required"       => 'Kode risiko wajib diisi.',
            "blocks.$index.risiko.required"            => 'Pernyataan risiko wajib diisi.',
            "blocks.$index.mr_risiko_id.required"      => 'Pilih risiko dari Manajemen Risiko terlebih dahulu.',
            "blocks.$index.level_kemungkinan.min"      => 'Level kemungkinan harus antara 1 sampai 5.',
            "blocks.$index.level_kemungkinan.max"      => 'Level kemungkinan harus antara 1 sampai 5.',
            "blocks.$index.level_dampak.min"           => 'Level dampak harus antara 1 sampai 5.',
            "blocks.$index.level_dampak.max"           => 'Level dampak harus antara 1 sampai 5.',
        ]);

        $data = [
            'kode_risiko'                 => trim((string) $block['kode_risiko']),
            'sumber_risiko'               => $sumber,
            'mr_risiko_id'                => $sumber === 'dari_mr' ? (int) $block['mr_risiko_id'] : null,
            'risiko'                      => $block['risiko'],
            'penyebab'                    => filled($block['penyebab'] ?? null) ? $block['penyebab'] : null,
            'level_kemungkinan'           => filled($block['level_kemungkinan'] ?? null) ? (int) $block['level_kemungkinan'] : null,
            'level_dampak'                => filled($block['level_dampak'] ?? null) ? (int) $block['level_dampak'] : null,
            'perencanaan_keberlangsungan' => filled($block['perencanaan_keberlangsungan'] ?? null) ? $block['perencanaan_keberlangsungan'] : null,
        ];

        if ($model) {
            $model->update($data);
        } else {
            $data['created_by'] = auth()->id();
            $model = $this->perubahan->analisisRisiko()->create($data);
            $this->blocks[$index]['id'] = $model->id;
        }

        $this->blocks[$index]['kode_risiko'] = $model->kode_risiko;
        $this->blocks[$index]['mr_risiko_id'] = $model->mr_risiko_id;

        $pesan = 'Analisis risiko berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('risiko-mpr-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function render()
    {
        $user = auth()->user();
        $wajib = $this->isWajib();
        $isEditable = $this->konteks->isEditableByOperator() && ! $user->isAdmin() && $wajib;

        $calc = app(RiskMatrixCalculator::class);
        $hasilPerBlok = [];
        foreach ($this->blocks as $i => $b) {
            $k = filled($b['level_kemungkinan'] ?? null) ? (int) $b['level_kemungkinan'] : null;
            $d = filled($b['level_dampak'] ?? null) ? (int) $b['level_dampak'] : null;
            $besaran = ($k && $d) ? $calc->calculate($k, $d) : null;
            $label = $besaran !== null ? $calc->labelMpr($besaran) : null;
            $hasilPerBlok[$i] = [
                'besaran' => $besaran,
                'label'   => $label,
                'badge'   => $label ? $calc->colorClassMpr($label) : null,
            ];
        }

        $mrRisikoList = MrRisiko::query()
            ->with('konteks')
            ->whereHas('konteks', fn ($q) => $q->where('layanan_id', $this->konteks->layanan_id))
            ->orderBy('kode_risiko')
            ->get();

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.analisis-risiko.form', [
            'wajib'        => $wajib,
            'isEditable'   => $isEditable,
            'isAdmin'      => $user->isAdmin(),
            'mrRisikoList' => $mrRisikoList,
            'hasilPerBlok' => $hasilPerBlok,
            'breadcrumb'   => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                $this->perubahan->id_perubahan => route('mpr.perubahan.hub', [$this->konteks, $this->perubahan]),
                'Analisis Risiko (2A)' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
