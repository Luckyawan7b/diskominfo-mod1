<?php

namespace App\Livewire\Konteks;

use App\Models\Layanan;
use App\Models\MrKonteks;
use App\Models\MrSasaranUpr;
use App\Models\MrStrukturPelaksana;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KonteksIndex extends Component
{
    public string $filterDinas = '';
    public ?Layanan $activeLayanan = null;

    // ─── Dipakai admin saja ──────────────────────────────────────────────────
    public bool $showCreateModal = false;
    public int $newTahun = 0;
    public int $newTahunPelaksanaan = 0;
    public ?int $duplicateFromId = null;
    public string $newNamaInstansi = '';
    public string $newNamaUpr = '';

    public function mount(?Layanan $layanan = null): void
    {
        $this->newTahun           = (int) date('Y');
        $this->newTahunPelaksanaan = (int) date('Y');

        $user = auth()->user();

        if ($layanan && $layanan->exists) {
            $this->activeLayanan = $layanan;
            return;
        }

        if ($user->isAdmin() && !$layanan) {
            abort(403, 'Admin hanya dapat mengakses konteks melalui menu Monitoring per Layanan.');
        }

        if ($user->isOperator()) {
            $layananId = session('active_layanan_id');
            if ($layananId) {
                // Scoping: layanan harus milik operator ini (created_by)
                $this->activeLayanan = Layanan::where('created_by', $user->id)->find($layananId);
            }
        }
    }


    public function render()
    {
        $user  = auth()->user();

        if ($user->isOperator()) {
            // Operator: hanya konteks dari layanan aktif jika ada
            $query = MrKonteks::whereHas('layanan', fn ($q) => $q->where('created_by', $user->id))
                ->with(['layanan.creator', 'risiko'])
                ->withCount('risiko');
                
            if ($this->activeLayanan) {
                $query->where('layanan_id', $this->activeLayanan->id);
            }
        } else {
            // Admin: konteks berdasarkan layanan yang sedang di-review
            $query = MrKonteks::with(['layanan.creator', 'risiko'])->withCount('risiko');
            if ($this->activeLayanan) {
                $query->where('layanan_id', $this->activeLayanan->id);
            }
        }

        $konteks = $query->orderByDesc('tahun_penilaian')->get();

        // Untuk duplikat: opsi konteks dari layanan milik operator ini
        $previousKonteksOptions = collect();
        if ($user->isOperator()) {
            $prevQuery = MrKonteks::whereHas('layanan', fn ($q) => $q->where('created_by', $user->id));
            if ($this->activeLayanan) {
                $prevQuery->where('layanan_id', $this->activeLayanan->id);
            }
            $previousKonteksOptions = $prevQuery->orderByDesc('tahun_penilaian')->get();
        }

        // Ambil daftar dinas unik untuk filter admin
        $dinasList = collect();
        if ($user->isAdmin()) {
            $dinasList = \App\Models\User::whereNotNull('nama_dinas')
                ->distinct()
                ->orderBy('nama_dinas')
                ->pluck('nama_dinas');
        }

        return view('livewire.konteks.index', [
            'konteks'                => $konteks,
            'dinasList'              => $dinasList,
            'previousKonteksOptions' => $previousKonteksOptions,
            'breadcrumb'             => [
                'Manajemen Risiko' => $user->isAdmin() ? route('admin.review.index') : route('konteks.index'),
                ($this->activeLayanan ? $this->activeLayanan->nama_layanan : 'Daftar Konteks') => null
            ],
        ]);
    }

    /**
     * Membuat konteks MR baru (dapat dilakukan oleh Admin dan Operator)
     */
    public function createKonteks(): void
    {
        $user = auth()->user();

        $rules = [
            'newTahunPelaksanaan' => 'required|integer|min:2020|max:2099',
            'newTahun'            => 'required|integer|min:2020|max:2099',
        ];

        if ($user->isAdmin()) {
            $rules['newNamaInstansi'] = 'required|string|max:255';
        }

        $this->validate($rules);

        if ($user->isOperator() && !$this->activeLayanan) {
            $this->addError('newTahun', 'Silakan pilih layanan terlebih dahulu melalui Dashboard.');
            return;
        }

        DB::beginTransaction();
        try {
            $data = [
                'tahun_penilaian'   => $this->newTahun,
                'tahun_pelaksanaan' => $this->newTahunPelaksanaan,
                'created_by'        => $user->id,
            ];

            if ($user->isOperator() && $this->activeLayanan) {
                $data['layanan_id']    = $this->activeLayanan->id;
                $data['nama_instansi'] = $this->activeLayanan->creator?->nama_dinas ?? $this->activeLayanan->unit_pelaksana ?? '';
                $data['nama_upr']      = $this->activeLayanan->nama_layanan;
            } else {
                $data['nama_instansi'] = $this->newNamaInstansi;
                $data['nama_upr']      = $this->newNamaUpr ?: '';
            }

            $konteks = MrKonteks::create($data);

            if ($this->duplicateFromId) {
                $source = MrKonteks::find($this->duplicateFromId);
                if ($source) {
                    $this->duplicateNonRiskData($source, $konteks);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('newTahun', 'Terjadi kesalahan: ' . $e->getMessage());
            return;
        }

        $this->showCreateModal = false;
        $this->redirect(route('konteks.form', $konteks), navigate: true);
    }

    private function duplicateNonRiskData(MrKonteks $source, MrKonteks $target): void
    {
        $target->update([
            'nama_instansi' => $source->nama_instansi,
            'nama_upr'      => $source->nama_upr,
            'tugas_upr'     => $source->tugas_upr,
            'fungsi_upr'    => $source->fungsi_upr,
            'selera_risiko' => $source->selera_risiko,
        ]);

        foreach ($source->sasaranUpr as $sasaran) {
            $newSasaran = $target->sasaranUpr()->create([
                'urutan'                  => $sasaran->urutan,
                'ref_sasaran_nasional_id' => $sasaran->ref_sasaran_nasional_id,
                'sasaran_upr'             => $sasaran->sasaran_upr,
            ]);
            foreach ($sasaran->indikator as $indikator) {
                $newSasaran->indikator()->create([
                    'urutan'            => $indikator->urutan,
                    'indikator_kinerja' => $indikator->indikator_kinerja,
                    'target_kinerja'    => $indikator->target_kinerja,
                ]);
            }
        }

        if ($source->strukturPelaksana) {
            $target->strukturPelaksana()->create([
                'pemilik_risiko'     => $source->strukturPelaksana->pemilik_risiko,
                'koordinator_risiko' => $source->strukturPelaksana->koordinator_risiko,
                'pengelola_risiko'   => $source->strukturPelaksana->pengelola_risiko,
            ]);
        }
    }
}
