<?php

namespace App\Livewire\Mpr\Perubahan;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\RefKlasifikasiPerubahan;
use App\Models\RefLingkupPerubahan;
use App\Services\MprAlurResolver;
use App\Services\MprIdGenerator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * F1 — Permohonan Perubahan (MPR01).
 * ID perubahan dibuat otomatis oleh MprIdGenerator saat simpan pertama (bukan saat form dibuka).
 */
#[Layout('components.layouts.app')]
class PerubahanForm extends Component
{
    public MprKonteks $konteks;
    public ?MprPerubahan $perubahanModel = null;
    public bool $isNew = true;

    public string $nama_layanan_digital = '';
    public ?int $mpr_analisis_prioritas_id = null;
    public string $detail_perubahan = '';
    public string $unit_pemohon = '';
    public ?int $ref_klasifikasi_perubahan_id = null;
    public ?int $ref_lingkup_perubahan_id = null;

    /** Pengaman ubah klasifikasi/lingkup: true bila simpan diblokir menunggu konfirmasi eksplisit. */
    public bool $perluKonfirmasi = false;
    /** @var list<string> */
    public array $formulirTerisi = [];

    /** Hasil resolver setelah simpan: label formulir wajib. */
    public array $hasilAlur = [];

    public function mount(MprKonteks $konteks, ?MprPerubahan $perubahan = null): void
    {
        $this->konteks = $konteks;

        if ($perubahan && $perubahan->exists) {
            // Perubahan harus milik konteks pada URL.
            abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

            $this->perubahanModel = $perubahan;
            $this->isNew = false;

            $this->nama_layanan_digital = $perubahan->nama_layanan_digital;
            $this->mpr_analisis_prioritas_id = $perubahan->mpr_analisis_prioritas_id;
            $this->detail_perubahan = $perubahan->detail_perubahan;
            $this->unit_pemohon = $perubahan->unit_pemohon ?? '';
            $this->ref_klasifikasi_perubahan_id = $perubahan->ref_klasifikasi_perubahan_id;
            $this->ref_lingkup_perubahan_id = $perubahan->ref_lingkup_perubahan_id;

            $this->hitungHasilAlur();
        }
    }

    /**
     * Setiap perubahan pilihan klasifikasi/lingkup membatalkan peringatan sebelumnya.
     */
    public function updatedRefKlasifikasiPerubahanId(): void
    {
        $this->perluKonfirmasi = false;
    }

    public function updatedRefLingkupPerubahanId(): void
    {
        $this->perluKonfirmasi = false;
    }

    private function hitungHasilAlur(): void
    {
        $perubahan = $this->perubahanModel->fresh(['klasifikasi', 'lingkup']);

        $this->hasilAlur = collect(MprAlurResolver::untuk($perubahan)->formulir())
            ->where('wajib', true)
            ->pluck('label')
            ->values()
            ->all();
    }

    public function save(bool $konfirmasi = false): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $this->validate([
            'nama_layanan_digital'         => 'required|string|max:255',
            'mpr_analisis_prioritas_id'    => [
                'nullable',
                'integer',
                Rule::exists('mpr_analisis_prioritas', 'id')->where('mpr_konteks_id', $this->konteks->id),
            ],
            'detail_perubahan'             => 'required|string|max:5000',
            'unit_pemohon'                 => 'nullable|string|max:255',
            'ref_klasifikasi_perubahan_id' => 'required|integer|exists:ref_klasifikasi_perubahan,id',
            'ref_lingkup_perubahan_id'     => 'required|integer|exists:ref_lingkup_perubahan,id',
        ], [
            'nama_layanan_digital.required'         => 'Nama layanan digital wajib diisi.',
            'detail_perubahan.required'             => 'Detail perubahan wajib diisi.',
            'ref_klasifikasi_perubahan_id.required' => 'Klasifikasi perubahan wajib dipilih.',
            'ref_lingkup_perubahan_id.required'     => 'Lingkup perubahan wajib dipilih.',
            'mpr_analisis_prioritas_id.exists'      => 'Analisis prioritas tidak valid untuk konteks ini.',
        ]);

        // Pengaman: ubah klasifikasi/lingkup setelah formulir lanjutan terisi diblokir sampai dikonfirmasi.
        if (! $this->isNew && ! $konfirmasi && $this->klasifikasiAtauLingkupBerubah()) {
            $terisi = MprAlurResolver::untuk($this->perubahanModel)->formulirTerisi();

            if ($terisi !== []) {
                $this->formulirTerisi = $terisi;
                $this->perluKonfirmasi = true;

                return;
            }
        }

        $data = [
            'nama_layanan_digital'         => $this->nama_layanan_digital,
            'mpr_analisis_prioritas_id'    => $this->mpr_analisis_prioritas_id,
            'is_layanan_prioritas'         => $this->mpr_analisis_prioritas_id !== null,
            'detail_perubahan'             => $this->detail_perubahan,
            'unit_pemohon'                 => filled($this->unit_pemohon) ? $this->unit_pemohon : null,
            'ref_klasifikasi_perubahan_id' => $this->ref_klasifikasi_perubahan_id,
            'ref_lingkup_perubahan_id'     => $this->ref_lingkup_perubahan_id,
        ];

        if ($this->isNew) {
            $data['created_by'] = auth()->id();
            $this->perubahanModel = app(MprIdGenerator::class)->buatPerubahan($this->konteks, $data);
            $this->isNew = false;
        } else {
            $this->perubahanModel->update($data);
        }

        $this->perluKonfirmasi = false;
        $this->formulirTerisi = [];
        $this->hitungHasilAlur();

        $pesan = 'Permohonan perubahan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perubahan-saved', message: $pesan);
    }



    private function klasifikasiAtauLingkupBerubah(): bool
    {
        return (int) $this->ref_klasifikasi_perubahan_id !== (int) $this->perubahanModel->ref_klasifikasi_perubahan_id
            || (int) $this->ref_lingkup_perubahan_id !== (int) $this->perubahanModel->ref_lingkup_perubahan_id;
    }

    public function render()
    {
        $user = auth()->user();

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.perubahan.form', [
            'isEditable'      => $this->konteks->isEditableByOperator() && ! $user->isAdmin(),
            'klasifikasiRef'  => RefKlasifikasiPerubahan::orderBy('id')->get(),
            'lingkupRef'      => RefLingkupPerubahan::orderBy('id')->get(),
            'prioritasList'   => $this->konteks->analisisPrioritas()->get(),
            'klasifikasiInfo' => $this->ref_klasifikasi_perubahan_id
                ? RefKlasifikasiPerubahan::find($this->ref_klasifikasi_perubahan_id) : null,
            'lingkupInfo'     => $this->ref_lingkup_perubahan_id
                ? RefLingkupPerubahan::find($this->ref_lingkup_perubahan_id) : null,
            'breadcrumb'      => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                ($this->isNew ? 'Baru' : $this->perubahanModel->id_perubahan) => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
