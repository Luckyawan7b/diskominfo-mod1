<?php

namespace App\Livewire\Mpn\Pengetahuan;

use App\Models\MpnKonteks;
use App\Models\MpnPengetahuan;
use App\Models\RefAspekPemdi;
use App\Models\RefIndikatorPemdi;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PengetahuanForm extends Component
{
    public MpnKonteks $konteks;
    public ?MpnPengetahuan $pengetahuanModel = null;
    public bool $isNew = false;
    public int $activeTab = 1;

    // Tab 1: Identifikasi Pengetahuan
    public string $nama_sub_fitur = '';
    public string $layanan_prioritas = '';
    public string $nama_pengetahuan = '';
    public string $sudah_terdokumentasi = '';
    public ?int $ref_aspek_pemdi_id = null;
    public ?int $ref_indikator_pemdi_id = null;
    public bool $apakah_terdokumentasi = true; 

    // Tab 2: Rencana Dokumentasi (Hanya jika apakah_terdokumentasi == false)
    public ?string $target_tahun_ini = null;
    public ?string $pemilik_pengetahuan = null;
    public bool $tipe_teks = false;
    public bool $tipe_gambar = false;
    public bool $tipe_audio = false;
    public bool $tipe_video = false;
    public ?string $penanggung_jawab = null;
    public ?string $target_waktu_dokumentasi = null;

    public $aspekList;
    public $indikatorList;

    public function mount(MpnKonteks $konteks, $pengetahuan = null): void
    {
        $this->konteks = $konteks;
        $this->aspekList = RefAspekPemdi::orderBy('id')->get();
        $this->indikatorList = collect();

        if ($pengetahuan instanceof MpnPengetahuan) {
            $this->pengetahuanModel = $pengetahuan->loadMissing('rencanaDokumentasi');
            $this->fillForm();
        } elseif ($pengetahuan && $pengetahuan !== 'new') {
            $this->pengetahuanModel = MpnPengetahuan::with('rencanaDokumentasi')->findOrFail($pengetahuan);
            $this->fillForm();
        } else {
            $this->isNew = true;
            $this->apakah_terdokumentasi = true;
        }
    }

    private function fillForm(): void
    {
        $p = $this->pengetahuanModel;
        
        $this->nama_sub_fitur = $p->nama_sub_fitur ?? '';
        $this->layanan_prioritas = $p->layanan_prioritas ?? '';
        $this->nama_pengetahuan = $p->nama_pengetahuan ?? '';
        $this->sudah_terdokumentasi = $p->sudah_terdokumentasi ?? '';
        $this->ref_aspek_pemdi_id = $p->ref_aspek_pemdi_id;
        $this->ref_indikator_pemdi_id = $p->ref_indikator_pemdi_id;
        $this->apakah_terdokumentasi = (bool) $p->apakah_terdokumentasi;

        if ($this->ref_aspek_pemdi_id) {
            $this->indikatorList = RefIndikatorPemdi::where('ref_aspek_pemdi_id', $this->ref_aspek_pemdi_id)->get();
        }

        if ($p->rencanaDokumentasi) {
            $r = $p->rencanaDokumentasi;
            $this->target_tahun_ini = $r->target_tahun_ini;
            $this->pemilik_pengetahuan = $r->pemilik_pengetahuan;
            $this->tipe_teks = (bool) $r->tipe_teks;
            $this->tipe_gambar = (bool) $r->tipe_gambar;
            $this->tipe_audio = (bool) $r->tipe_audio;
            $this->tipe_video = (bool) $r->tipe_video;
            $this->penanggung_jawab = $r->penanggung_jawab;
            $this->target_waktu_dokumentasi = $r->target_waktu_dokumentasi;
        }
    }

    public function updatedRefAspekPemdiId($value)
    {
        $this->ref_indikator_pemdi_id = null;
        if ($value) {
            $this->indikatorList = RefIndikatorPemdi::where('ref_aspek_pemdi_id', $value)->get();
        } else {
            $this->indikatorList = collect();
        }
    }

    public function updatedApakahTerdokumentasi($value)
    {
        // Jika diubah menjadi "Ya", reset form rencana dokumentasi
        if ($value) {
            $this->target_tahun_ini = null;
            $this->pemilik_pengetahuan = null;
            $this->tipe_teks = false;
            $this->tipe_gambar = false;
            $this->tipe_audio = false;
            $this->tipe_video = false;
            $this->penanggung_jawab = null;
            $this->target_waktu_dokumentasi = null;
            $this->activeTab = 1; // Kembali ke tab 1 jika tab 2 disembunyikan
        }
    }

    public function switchTab(int $tab)
    {
        // Cegah pindah ke tab 2 jika apakah_terdokumentasi = Ya (true)
        if ($tab === 2 && $this->apakah_terdokumentasi) {
            return;
        }
        $this->activeTab = $tab;
    }

    public function save(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $this->validate([
            'nama_pengetahuan' => 'required|string|max:255',
            'ref_aspek_pemdi_id' => 'required|exists:ref_aspek_pemdi,id',
            'ref_indikator_pemdi_id' => 'required|exists:ref_indikator_pemdi,id',
        ], [
            'nama_pengetahuan.required' => 'Nama pengetahuan wajib diisi.',
            'ref_aspek_pemdi_id.required' => 'Aspek PEMDI wajib dipilih.',
            'ref_indikator_pemdi_id.required' => 'Indikator PEMDI wajib dipilih.',
        ]);

        $data = [
            'mpn_konteks_id' => $this->konteks->id,
            'nama_sub_fitur' => $this->nama_sub_fitur ?: null,
            'layanan_prioritas' => $this->layanan_prioritas ?: null,
            'nama_pengetahuan' => $this->nama_pengetahuan,
            'sudah_terdokumentasi' => $this->sudah_terdokumentasi ?: null,
            'ref_aspek_pemdi_id' => $this->ref_aspek_pemdi_id,
            'ref_indikator_pemdi_id' => $this->ref_indikator_pemdi_id,
            'apakah_terdokumentasi' => $this->apakah_terdokumentasi,
        ];

        if ($this->isNew) {
            $data['created_by'] = auth()->id();
            $this->pengetahuanModel = MpnPengetahuan::create($data);
            $this->isNew = false;
        } else {
            $this->pengetahuanModel->update($data);
        }

        // Observer MpnPengetahuanObserver akan menangani pembuatan baris mpn_rencana_dokumentasi 
        // jika apakah_terdokumentasi == false. 
        // Setelah baris tersebut di-create oleh observer, kita tinggal meng-update nilainya:
        if (!$this->apakah_terdokumentasi && $this->pengetahuanModel->rencanaDokumentasi) {
            $this->pengetahuanModel->rencanaDokumentasi->update([
                'target_tahun_ini' => $this->target_tahun_ini,
                'pemilik_pengetahuan' => $this->pemilik_pengetahuan,
                'tipe_teks' => $this->tipe_teks,
                'tipe_gambar' => $this->tipe_gambar,
                'tipe_audio' => $this->tipe_audio,
                'tipe_video' => $this->tipe_video,
                'penanggung_jawab' => $this->penanggung_jawab,
                'target_waktu_dokumentasi' => $this->target_waktu_dokumentasi,
            ]);
        }

        session()->flash('success', 'Data Pengetahuan berhasil disimpan.');
        $this->redirect(route('mpn.pengetahuan.index', $this->konteks), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();
        
        $availableKonteks = MpnKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpn.pengetahuan.form', [
            'isEditable' => $this->konteks->isEditableByOperator() && !auth()->user()->isAdmin(),
            'breadcrumb' => [
                'Manajemen Pengetahuan' => auth()->user()->isAdmin()
                    ? route('admin.review.mpn.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpn.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks-mpn.form', $this->konteks),
                'Pengetahuan' => route('mpn.pengetahuan.index', $this->konteks),
                ($this->isNew ? 'Baru' : 'Edit') => null,
            ],
        ])->layout('components.layouts.app', [
            'mpnKonteks' => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
