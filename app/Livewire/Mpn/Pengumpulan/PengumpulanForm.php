<?php

namespace App\Livewire\Mpn\Pengumpulan;

use App\Models\MpnKonteks;
use App\Models\MpnPengetahuan;
use App\Models\MpnPengumpulan;
use App\Models\RefMetodePengolahan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PengumpulanForm extends Component
{
    public MpnKonteks $konteks;
    public MpnPengetahuan $pengetahuan;
    public ?MpnPengumpulan $pengumpulanModel = null;
    public bool $isNew = false;

    // Dropdown options untuk Lokasi Penyimpanan Lain (sumber kebenaran dari dokumen Excel)
    public array $lokasiPenyimpananOptions = [
        'Database Manajemen Risiko',
        'Database Manajemen Keberlangsungan',
        'Database Manajemen Relasi Pengguna',
        'Database Manajemen Perubahan',
        'SIMPAN',
        'Lainnya',
    ];

    // Fields
    public ?int $revisi_dari_id = null;
    public string $id_pengetahuan = '';
    public string $tanggal_pengumpulan = '';
    public string $unit_pengumpulan = '';
    public string $lokasi_penyimpanan_lain = '';
    public string $keterangan_lokasi_lainnya = '';
    public string $status_publikasi_simpan = 'Draft';       // Alur kerja SIMPAN
    public ?string $visibilitas_dokumen = null;              // Audiens/visibilitas dokumen
    public ?int $ref_metode_pengolahan_id = null;
    public string $deskripsi_pengolahan = '';
    public string $tanggal_update_terakhir = '';
    public string $penulis = '';
    public string $label_tags = '';
    public string $kontributor = '';
    public string $url = '';

    public $metodeList;
    public $revisiList;

    public function mount(MpnKonteks $konteks, MpnPengetahuan $pengetahuan, $pengumpulan = null): void
    {
        $this->konteks = $konteks;
        $this->pengetahuan = $pengetahuan;
        $this->metodeList = RefMetodePengolahan::all();

        $this->revisiList = $this->pengetahuan->pengumpulan()
            ->latest()
            ->get();

        if ($pengumpulan instanceof MpnPengumpulan) {
            $this->pengumpulanModel = $pengumpulan;
            $this->fillForm();
        } elseif ($pengumpulan && $pengumpulan !== 'new') {
            $this->pengumpulanModel = MpnPengumpulan::findOrFail($pengumpulan);
            $this->fillForm();
        } else {
            $this->isNew = true;
            $this->tanggal_pengumpulan = date('Y-m-d');

            // Auto-select latest revision if exists
            if ($this->revisiList->count() > 0) {
                $this->revisi_dari_id = $this->revisiList->first()->id;
            }

            $this->generateIdPengetahuan($this->revisi_dari_id);
        }
    }

    public function updatedRevisiDariId($value): void
    {
        if ($this->isNew) {
            $this->generateIdPengetahuan($value);
        }
    }

    private function generateIdPengetahuan($revisiId = null): void
    {
        if (empty($revisiId)) {
            // Generate Auto ID Pengetahuan with formula: MRP-{alias}-{tahun}-001
            $alias = strtoupper(auth()->user()->alias ?: 'USER');
            $tahun = $this->konteks->tahun_penilaian;
            $prefix = "MRP-{$alias}-{$tahun}-";

            // Get the latest sequence for this prefix (including soft-deleted) that is NOT a revision
            $latestPengumpulan = MpnPengumpulan::withTrashed()
                ->where('id_pengetahuan', 'like', $prefix . '%')
                ->where('id_pengetahuan', 'not like', '%-REV%')
                ->orderBy('id_pengetahuan', 'desc')
                ->first();

            if ($latestPengumpulan) {
                $parts = explode('-', $latestPengumpulan->id_pengetahuan);
                $lastSeq = (int) end($parts);
                $newSeq = $lastSeq + 1;
            } else {
                $newSeq = 1;
            }
            $this->id_pengetahuan = $prefix . str_pad($newSeq, 3, '0', STR_PAD_LEFT);
        } else {
            // Ini adalah dokumen revisi, gunakan ID dasar dokumen asli ditambah -REV
            $revisiTarget = MpnPengumpulan::withTrashed()->find($revisiId);
            if ($revisiTarget) {
                // Ekstrak ID dasar (buang teks -REV jika ada)
                $baseId = explode('-REV', $revisiTarget->id_pengetahuan)[0];

                // Cari semua dokumen yang berawalan BaseID-REV
                $latestRev = MpnPengumpulan::withTrashed()
                    ->where('id_pengetahuan', 'like', $baseId . '-REV%')
                    ->orderByRaw("LENGTH(id_pengetahuan) DESC, id_pengetahuan DESC") // Agar -REV10 > -REV9
                    ->first();

                if ($latestRev) {
                    // Ekstrak angka revisi terakhir
                    $parts = explode('-REV', $latestRev->id_pengetahuan);
                    $lastRevNum = (int) end($parts);
                    $newRevNum = $lastRevNum + 1;
                } else {
                    $newRevNum = 1;
                }
                
                $this->id_pengetahuan = $baseId . '-REV' . $newRevNum;
            }
        }
    }

    private function fillForm(): void
    {
        $p = $this->pengumpulanModel;
        $this->revisi_dari_id = $p->revisi_dari_id;
        $this->id_pengetahuan = $p->id_pengetahuan ?? '';
        $this->tanggal_pengumpulan = $p->tanggal_pengumpulan ?? '';
        $this->unit_pengumpulan = $p->unit_pengumpulan ?? '';
        $this->lokasi_penyimpanan_lain = $p->lokasi_penyimpanan_lain ?? '';
        $this->keterangan_lokasi_lainnya = $p->keterangan_lokasi_lainnya ?? '';
        $this->status_publikasi_simpan = $p->status_publikasi_simpan ?? 'Draft';
        $this->visibilitas_dokumen = $p->visibilitas_dokumen ?? null;
        $this->ref_metode_pengolahan_id = $p->ref_metode_pengolahan_id;
        $this->deskripsi_pengolahan = $p->deskripsi_pengolahan ?? '';
        $this->tanggal_update_terakhir = $p->tanggal_update_terakhir ?? '';
        $this->penulis = $p->penulis ?? '';
        $this->label_tags = $p->label_tags ?? '';
        $this->kontributor = $p->kontributor ?? '';
        $this->url = $p->url ?? '';
    }

    /**
     * Lifecycle hook: otomatis hapus keterangan jika pilihan bukan "Lainnya".
     * Ini mencegah data lama/nyangkut tersimpan saat pengguna mengganti pilihan dropdown.
     */
    public function updatedLokasiPenyimpananLain(string $value): void
    {
        if ($value !== 'Lainnya') {
            $this->keterangan_lokasi_lainnya = '';
        }
    }

    public function save(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $this->validate([
            'id_pengetahuan'            => 'required|string|max:100',
            'tanggal_pengumpulan'       => 'required|date',
            'unit_pengumpulan'          => 'required|string|max:191',
            'lokasi_penyimpanan_lain'   => ['nullable', 'in:' . implode(',', $this->lokasiPenyimpananOptions)],
            'keterangan_lokasi_lainnya' => 'required_if:lokasi_penyimpanan_lain,Lainnya|nullable|string|max:500',
            'status_publikasi_simpan'   => 'required|in:Draft,Ditolak,Dipublikasikan,Diarsipkan',
            'visibilitas_dokumen'       => 'nullable|in:Publik,Internal',
        ]);

        $data = [
            'mpn_pengetahuan_id' => $this->pengetahuan->id,
            'revisi_dari_id' => $this->revisi_dari_id ?: null,
            'id_pengetahuan' => $this->id_pengetahuan,
            'tanggal_pengumpulan' => $this->tanggal_pengumpulan,
            'unit_pengumpulan' => $this->unit_pengumpulan,
            'lokasi_penyimpanan_lain'   => $this->lokasi_penyimpanan_lain ?: null,
            // Proteksi ganda: pastikan keterangan tidak tersimpan jika pilihan bukan "Lainnya"
            'keterangan_lokasi_lainnya' => ($this->lokasi_penyimpanan_lain === 'Lainnya')
                ? ($this->keterangan_lokasi_lainnya ?: null)
                : null,
            'status_publikasi_simpan' => $this->status_publikasi_simpan,
            'visibilitas_dokumen'     => $this->visibilitas_dokumen ?: null,
            'ref_metode_pengolahan_id' => $this->ref_metode_pengolahan_id ?: null,
            'deskripsi_pengolahan' => $this->deskripsi_pengolahan ?: null,
            'tanggal_update_terakhir' => $this->tanggal_update_terakhir ?: null,
            'penulis' => $this->penulis ?: null,
            'label_tags' => $this->label_tags ?: null,
            'kontributor' => $this->kontributor ?: null,
            'url' => $this->url ?: null,
        ];

        if ($this->isNew) {
            MpnPengumpulan::create($data);
        } else {
            $this->pengumpulanModel->update($data);
        }

        session()->flash('success', 'Data pengumpulan/riwayat revisi berhasil disimpan.');
        $this->redirect(route('mpn.pengumpulan.index', ['konteks' => $this->konteks, 'pengetahuan' => $this->pengetahuan]), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();

        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.pengumpulan.form', [
            'isEditable' => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb' => [
                'Manajemen Pengetahuan' => auth()->user()->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks-mpn.form', $this->konteks),
                'Pengetahuan' => route('mpn.pengetahuan.index', $this->konteks),
                $this->pengetahuan->nama_pengetahuan => route('mpn.pengetahuan.form', ['konteks' => $this->konteks, 'pengetahuan' => $this->pengetahuan]),
                'Riwayat' => route('mpn.pengumpulan.index', ['konteks' => $this->konteks, 'pengetahuan' => $this->pengetahuan]),
                ($this->isNew ? 'Baru' : 'Edit') => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks' => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
