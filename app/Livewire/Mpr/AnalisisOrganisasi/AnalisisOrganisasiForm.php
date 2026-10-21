<?php

namespace App\Livewire\Mpr\AnalisisOrganisasi;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\RefAreaDampakOrg;
use App\Models\RefIndikatorAreaDampak;
use App\Models\RefUkuranKompleksitas;
use App\Services\MprAlurResolver;
use App\Services\MprKesimpulanOrganisasi;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * F2B — Analisis Dampak Perubahan Organisasi (MPR02B).
 * Empat kartu area dampak, masing-masing dengan ukuran kompleksitas & checklist indikator (Tabel 2c),
 * serta panel kesimpulan otomatis (MprKesimpulanOrganisasi::terapkan()).
 */
#[Layout('components.layouts.app')]
class AnalisisOrganisasiForm extends Component
{
    public MprKonteks $konteks;
    public MprPerubahan $perubahan;

    /**
     * Keyed by ref_area_dampak_org_id:
     * [
     *   area_id => [
     *     'ref_ukuran_kompleksitas_id' => ?int,
     *     'indikator_ids' => int[],
     *   ]
     * ]
     */
    public array $areas = [];

    public ?string $kesimpulanNama = null;
    public ?string $rekomendasi = null;

    public function mount(MprKonteks $konteks, MprPerubahan $perubahan): void
    {
        abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

        $this->konteks = $konteks;
        $this->perubahan = $perubahan;
        $this->loadAreas();
    }

    public function loadAreas(): void
    {
        $areaRefs = RefAreaDampakOrg::orderBy('urutan')->orderBy('id')->get();
        $analisis = $this->perubahan->analisisOrganisasi()
            ->with(['ukuranKompleksitas', 'areas.indikator'])
            ->first();

        $tersimpanByArea = $analisis
            ? $analisis->areas->keyBy('ref_area_dampak_org_id')
            : collect();

        $this->areas = [];
        foreach ($areaRefs as $ref) {
            $row = $tersimpanByArea->get($ref->id);
            $this->areas[$ref->id] = [
                'ref_ukuran_kompleksitas_id' => $row?->ref_ukuran_kompleksitas_id,
                'indikator_ids'              => $row
                    ? $row->indikator->pluck('id')->map(fn ($v) => (int) $v)->values()->all()
                    : [],
            ];
        }

        $this->kesimpulanNama = $analisis?->ukuranKompleksitas?->nama;
        $this->rekomendasi = $analisis?->rekomendasi;
    }

    private function isWajib(): bool
    {
        return MprAlurResolver::untuk($this->perubahan->fresh(['klasifikasi', 'lingkup']))->perluAnalisisOrganisasi();
    }

    public function save(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        if (! $this->isWajib()) {
            abort(403, 'Formulir Analisis Organisasi tidak wajib untuk klasifikasi dan lingkup perubahan ini.');
        }

        $areaRefs = RefAreaDampakOrg::orderBy('urutan')->orderBy('id')->get();

        $rules = [];
        foreach ($areaRefs as $ref) {
            $aid = $ref->id;
            $rules["areas.$aid.ref_ukuran_kompleksitas_id"] = 'nullable|integer|exists:ref_ukuran_kompleksitas,id';
            $rules["areas.$aid.indikator_ids"] = 'array';
            $rules["areas.$aid.indikator_ids.*"] = 'integer|exists:ref_indikator_area_dampak,id';
        }
        if ($rules !== []) {
            $this->validate($rules);
        }

        $analisis = $this->perubahan->analisisOrganisasi()->firstOrCreate([]);

        foreach ($areaRefs as $ref) {
            $aid = $ref->id;
            $input = $this->areas[$aid] ?? [];
            $ukuranId = filled($input['ref_ukuran_kompleksitas_id'] ?? null)
                ? (int) $input['ref_ukuran_kompleksitas_id']
                : null;

            $orgArea = $analisis->areas()->updateOrCreate(
                ['ref_area_dampak_org_id' => $aid],
                ['ref_ukuran_kompleksitas_id' => $ukuranId]
            );

            // Indikator hanya sah bila milik area dampak ini.
            $validIndikatorIds = RefIndikatorAreaDampak::where('ref_area_dampak_org_id', $aid)
                ->whereIn('id', array_map('intval', $input['indikator_ids'] ?? []))
                ->pluck('id')
                ->all();

            $orgArea->indikator()->sync($validIndikatorIds);
            $this->areas[$aid]['ref_ukuran_kompleksitas_id'] = $ukuranId;
            $this->areas[$aid]['indikator_ids'] = array_values(array_map('intval', $validIndikatorIds));
        }

        $ukuranKesimpulan = app(MprKesimpulanOrganisasi::class)->terapkan($analisis->fresh());
        $analisis->refresh();

        $this->kesimpulanNama = $ukuranKesimpulan?->nama;
        $this->rekomendasi = $analisis->rekomendasi;

        $pesan = 'Analisis dampak organisasi berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('organisasi-mpr-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function render()
    {
        $user = auth()->user();
        $wajib = $this->isWajib();
        $isEditable = $this->konteks->isEditableByOperator() && ! $user->isAdmin() && $wajib;

        $areaRefs = RefAreaDampakOrg::with('indikator')->orderBy('urutan')->orderBy('id')->get();
        $ukuranList = RefUkuranKompleksitas::orderBy('bobot')->orderBy('id')->get();

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.analisis-organisasi.form', [
            'wajib'      => $wajib,
            'isEditable' => $isEditable,
            'isAdmin'    => $user->isAdmin(),
            'areaRefs'   => $areaRefs,
            'ukuranList' => $ukuranList,
            'breadcrumb' => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                $this->perubahan->id_perubahan => route('mpr.perubahan.hub', [$this->konteks, $this->perubahan]),
                'Analisis Organisasi (2B)' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
