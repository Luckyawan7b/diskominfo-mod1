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

    // ─── Dipakai admin saja ──────────────────────────────────────────────────
    public bool $showCreateModal = false;
    public int $newTahun = 0;
    public int $newTahunPelaksanaan = 0;
    public ?int $duplicateFromId = null;
    public string $newNamaInstansi = '';
    public string $newNamaUpr = '';

    public function mount(): void
    {
        $this->newTahun           = (int) date('Y');
        $this->newTahunPelaksanaan = (int) date('Y');

        // Operator: langsung pastikan ada konteks MR untuk layanan aktif.
        // Jika belum ada, buat otomatis, lalu redirect ke form-nya.
        $user = auth()->user();
        if ($user->isOperator()) {
            // Kita hanya redirect otomatis jika datang dari layanan.dashboard
            $layananId = session('active_layanan_id');
            if ($layananId) {
                // Scoping: layanan harus milik operator ini (created_by)
                $layanan = Layanan::where('created_by', $user->id)->find($layananId);
                if ($layanan) {
                    $konteks = $this->ensureKonteksForLayanan($layanan, $user);
                    session()->forget('active_layanan_id');
                    $this->redirect(route('konteks.form', $konteks), navigate: true);
                    return;
                }
            }
        }
    }

    /**
     * Ambil atau buat MrKonteks untuk layanan yang diberikan.
     */
    public static function ensureKonteksForLayanan(Layanan $layanan, $user): MrKonteks
    {
        return MrKonteks::firstOrCreate(
            ['layanan_id' => $layanan->id],
            [
                // Tidak ada lagi desa_id atau status — langsung isi dari creator
                'nama_instansi'     => $layanan->creator?->nama_dinas ?? $layanan->unit_pelaksana ?? '',
                'nama_upr'          => $layanan->nama_layanan,
                'tahun_penilaian'   => (int) date('Y'),
                'tahun_pelaksanaan' => (int) date('Y'),
                'created_by'        => $user->id,
            ]
        );
    }

    public function render()
    {
        $user  = auth()->user();

        if ($user->isOperator()) {
            // Operator: hanya konteks dari layanan miliknya sendiri (scoping via created_by)
            $query = MrKonteks::whereHas('layanan', fn ($q) => $q->where('created_by', $user->id))
                ->with(['layanan.creator', 'risiko'])
                ->withCount('risiko');
        } else {
            // Admin: semua konteks, bisa filter by nama dinas
            $query = MrKonteks::with(['layanan.creator', 'risiko'])->withCount('risiko');
            if ($this->filterDinas) {
                $query->whereHas('layanan.creator', fn ($q) => $q->where('nama_dinas', 'like', "%{$this->filterDinas}%"));
            }
        }

        $konteks = $query->orderByDesc('tahun_penilaian')->get();

        // Untuk duplikat: opsi konteks dari layanan milik operator ini
        $previousKonteksOptions = collect();
        if ($user->isOperator()) {
            $previousKonteksOptions = MrKonteks::whereHas('layanan', fn ($q) => $q->where('created_by', $user->id))
                ->orderByDesc('tahun_penilaian')
                ->get();
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
            'breadcrumb'             => ['Manajemen Risiko' => route('konteks.index'), 'Daftar Konteks' => null],
        ]);
    }

    /**
     * Hanya dipakai Admin untuk membuat konteks tanpa relasi layanan yang ada
     * (mode manual — fallback jika layanan belum ada di sistem).
     */
    public function createKonteks(): void
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            $this->addError('newTahun', 'Operator harus membuat konteks melalui Layanan.');
            return;
        }

        $this->validate([
            'newNamaInstansi'     => 'required|string|max:255',
            'newTahunPelaksanaan' => 'required|integer|min:2020|max:2099',
            'newTahun'            => 'required|integer|min:2020|max:2099',
        ]);

        DB::beginTransaction();
        try {
            $konteks = MrKonteks::create([
                'nama_instansi'     => $this->newNamaInstansi,
                'nama_upr'          => $this->newNamaUpr ?: '',
                'tahun_penilaian'   => $this->newTahun,
                'tahun_pelaksanaan' => $this->newTahunPelaksanaan,
                'created_by'        => $user->id,
            ]);

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
