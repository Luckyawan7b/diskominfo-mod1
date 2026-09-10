<?php
namespace App\Livewire\Risiko;

use App\Models\MrKonteks;
use App\Models\MrRisiko;
use App\Services\RiskMatrixCalculator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RisikoForm extends Component
{
    public MrKonteks $konteks;
    public ?MrRisiko $risikoModel = null;
    public bool $isNew = false;
    public int $activeTab = 1;

    // Tab 1: Identifikasi
    public int $no = 1;
    public ?int $mr_sasaran_upr_id = null;
    public ?string $indikator_kinerja_snapshot = null;
    public string $kode_risiko = '';
    public string $peristiwa_risiko = '';
    public string $penyebab = '';
    public string $dampak = '';
    public string $kategori_risiko = '';   // ← teks bebas (bukan FK)
    public string $area_dampak = '';

    // Tab 2: Analisis
    public ?int $level_kemungkinan = null;
    public ?int $level_dampak = null;

    // Tab 3: Perlakuan
    public string $keputusan_perlakuan = '';
    public string $deskripsi_detail_perlakuan = '';
    public string $waktu_rencana_perlakuan = '';
    public string $penanggung_jawab = '';

    // Tab 4: Residual
    public ?int $level_kemungkinan_residual = null;
    public ?int $level_dampak_residual = null;
    public string $keterangan_residual = '';

    // Tab 5: Kolom Tambahan (Bagian E)
    public string $layanan_pendukung = '';
    public string $layanan_prioritas = 'Instansional';
    public string $pemilik_layanan = '';
    public string $strategis_atau_operasional = 'Operasional';
    public bool $lintas_sektor = false;
    public string $ippd_terkait = '';
    public bool $membutuhkan_perubahan = false;


    public function mount(MrKonteks $konteks, $risiko = null): void
    {
        $this->konteks = $konteks;

        if ($risiko instanceof MrRisiko) {
            $this->risikoModel = $risiko->loadMissing(['perlakuan', 'residual', 'kolomTambahan', 'layananDigital']);
            $this->fillFromRisiko();
            $this->no = $konteks->risiko()->where('id', '<=', $this->risikoModel->id)->count();
        } elseif ($risiko && $risiko !== 'new') {
            $this->risikoModel = MrRisiko::with(['perlakuan', 'residual', 'kolomTambahan', 'layananDigital'])->findOrFail($risiko);
            $this->fillFromRisiko();
            $this->no = $konteks->risiko()->where('id', '<=', $this->risikoModel->id)->count();
        } else {
            $this->isNew = true;
            $count = $konteks->risiko()->count();
            $this->no = $count + 1;
            // Kode risiko: gunakan alias atau nama instansi singkat
            $namaInstansi = $konteks->nama_instansi ?: ($konteks->layanan?->creator?->alias ?? 'UPR');
            $prefix = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $namaInstansi), 0, 4));
            $this->kode_risiko = $prefix . '-R-' . ($count + 1);
        }
    }

    private function fillFromRisiko(): void
    {
        $r = $this->risikoModel;
        $this->mr_sasaran_upr_id            = $r->mr_sasaran_upr_id;
        $this->indikator_kinerja_snapshot   = $r->indikator_kinerja_snapshot ?? '';
        $this->kode_risiko                  = $r->kode_risiko ?? '';
        $this->peristiwa_risiko             = $r->peristiwa_risiko ?? '';
        $this->penyebab                     = $r->penyebab ?? '';
        $this->dampak                       = $r->dampak ?? '';
        $this->kategori_risiko              = $r->kategori_risiko ?? '';   // ← teks bebas
        $this->area_dampak                  = $r->area_dampak ?? '';
        $this->level_kemungkinan            = $r->level_kemungkinan;
        $this->level_dampak                 = $r->level_dampak;
        $this->keputusan_perlakuan          = $r->perlakuan?->keputusan_perlakuan ?? '';
        $this->deskripsi_detail_perlakuan   = $r->perlakuan?->deskripsi_detail_perlakuan ?? '';
        $this->waktu_rencana_perlakuan      = $r->perlakuan?->waktu_rencana_perlakuan ?? '';
        $this->penanggung_jawab             = $r->perlakuan?->penanggung_jawab ?? '';
        $this->level_kemungkinan_residual   = $r->residual?->level_kemungkinan;
        $this->level_dampak_residual        = $r->residual?->level_dampak;
        $this->keterangan_residual          = $r->residual?->keterangan_residual ?? '';

        // Kolom Tambahan
        if ($r->kolomTambahan) {
            $this->layanan_pendukung          = $r->kolomTambahan->layanan_pendukung ?? '';
            $this->layanan_prioritas          = $r->kolomTambahan->layanan_prioritas ?? 'Instansional';
            $this->pemilik_layanan            = $r->kolomTambahan->pemilik_layanan ?? '';
            $this->strategis_atau_operasional = $r->kolomTambahan->strategis_atau_operasional ?? 'Operasional';
            $this->lintas_sektor              = (bool) $r->kolomTambahan->lintas_sektor;
            $this->ippd_terkait               = $r->kolomTambahan->ippd_terkait ?? '';
            $this->membutuhkan_perubahan      = (bool) $r->kolomTambahan->membutuhkan_perubahan;
        }
    }

    public function updatedMrSasaranUprId($value)
    {
        $this->indikator_kinerja_snapshot = null;
    }

    public function save(): void
    {
        $this->validate([
            'kode_risiko'      => 'required|string|max:50',
            'peristiwa_risiko' => 'required|string',
            'kategori_risiko'  => 'nullable|string|max:191',  // ← teks bebas
        ]);

        $data = [
            'mr_konteks_id'              => $this->konteks->id,
            'mr_sasaran_upr_id'          => $this->mr_sasaran_upr_id,
            'indikator_kinerja_snapshot' => $this->indikator_kinerja_snapshot,
            'kode_risiko'                => $this->kode_risiko,
            'peristiwa_risiko'           => $this->peristiwa_risiko,
            'penyebab'                   => $this->penyebab,
            'dampak'                     => $this->dampak,
            'kategori_risiko'            => $this->kategori_risiko ?: null,  // ← teks bebas
            'area_dampak'                => $this->area_dampak ?: null,
            'level_kemungkinan'          => $this->level_kemungkinan,
            'level_dampak'               => $this->level_dampak,
            'created_by'                 => auth()->id(),
        ];

        if ($this->isNew) {
            $this->risikoModel = MrRisiko::create($data);
            $this->isNew = false;
        } else {
            $this->risikoModel->update($data);
        }

        // Perlakuan (Tab 3)
        if ($this->keputusan_perlakuan) {
            $this->risikoModel->perlakuan()->updateOrCreate([], [
                'keputusan_perlakuan'         => $this->keputusan_perlakuan,
                'deskripsi_detail_perlakuan'  => $this->deskripsi_detail_perlakuan,
                'waktu_rencana_perlakuan'     => $this->waktu_rencana_perlakuan,
                'penanggung_jawab'            => $this->penanggung_jawab,
            ]);
        }

        // Residual (Tab 4)
        if ($this->level_kemungkinan_residual || $this->level_dampak_residual || $this->keterangan_residual) {
            $calc = app(RiskMatrixCalculator::class);
            $besaranRes = ($this->level_kemungkinan_residual && $this->level_dampak_residual)
                ? $calc->calculate($this->level_kemungkinan_residual, $this->level_dampak_residual)
                : null;

            $this->risikoModel->residual()->updateOrCreate([], [
                'level_kemungkinan'   => $this->level_kemungkinan_residual,
                'level_dampak'        => $this->level_dampak_residual,
                'besaran_risiko'      => $besaranRes,
                'keterangan_residual' => $this->keterangan_residual ?: null,
            ]);
        }

        // Kolom Tambahan (Tab 5)
        if ($this->layanan_pendukung || $this->pemilik_layanan || $this->layanan_prioritas) {
            $this->risikoModel->kolomTambahan()->updateOrCreate([], [
                'layanan_pendukung'          => $this->layanan_pendukung ?: null,
                'layanan_prioritas'          => $this->layanan_prioritas ?: null,
                'pemilik_layanan'            => $this->pemilik_layanan ?: null,
                'strategis_atau_operasional' => $this->strategis_atau_operasional ?: null,
                'lintas_sektor'              => (bool) $this->lintas_sektor,
                'ippd_terkait'               => $this->ippd_terkait ?: null,
                'membutuhkan_perubahan'      => (bool) $this->membutuhkan_perubahan,
            ]);
        }

        session()->flash('success', 'Risiko berhasil disimpan.');
        $this->redirect(route('risiko.index', $this->konteks), navigate: true);
    }

    public function render()
    {
        $calc = app(RiskMatrixCalculator::class);
        $besaran         = ($this->level_kemungkinan && $this->level_dampak)
            ? $calc->calculate($this->level_kemungkinan, $this->level_dampak) : null;
        $besaranResidual = ($this->level_kemungkinan_residual && $this->level_dampak_residual)
            ? $calc->calculate($this->level_kemungkinan_residual, $this->level_dampak_residual) : null;

        $sasaranNasionalText = null;
        $indikatorList = collect();
        if ($this->mr_sasaran_upr_id) {
            $sasaranUpr = \App\Models\MrSasaranUpr::with(['sasaranNasional', 'indikator'])->find($this->mr_sasaran_upr_id);
            if ($sasaranUpr) {
                $sasaranNasionalText = $sasaranUpr->sasaranNasional?->teks_sasaran;
                $indikatorList = $sasaranUpr->indikator;
            }
        }

        $user = auth()->user();
        // Gunakan scope reusable untuk availableKonteks
        $availableKonteks = MrKonteks::accessibleBy($user)
            ->orderByDesc('tahun_penilaian')
            ->get();

        // Daftar kategori risiko yang pernah dipakai (sebagai UX datalist helper, bukan constraint)
        $kategoriSuggestions = MrRisiko::whereNotNull('kategori_risiko')
            ->distinct()
            ->orderBy('kategori_risiko')
            ->pluck('kategori_risiko');

        return view('livewire.risiko.form', [
            'sasaranList'         => $this->konteks->sasaranUpr()->orderBy('urutan')->get(),
            'indikatorList'       => $indikatorList,
            'sasaranNasionalText' => $sasaranNasionalText,
            'besaran'             => $besaran,
            'besaranLabel'        => $besaran ? $calc->label($besaran) : null,
            'besaranResidual'     => $besaranResidual,
            'kategoriSuggestions' => $kategoriSuggestions,
            'isEditable'          => $this->konteks->isEditableByOperator() || auth()->user()->isAdmin(),
            'breadcrumb'          => [
                'Manajemen Risiko' => route('konteks.index'),
                'Risiko'           => route('risiko.index', $this->konteks),
                ($this->isNew ? 'Baru' : $this->kode_risiko) => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks'          => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
