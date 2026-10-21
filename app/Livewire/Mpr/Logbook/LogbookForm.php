<?php

namespace App\Livewire\Mpr\Logbook;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * F4 — Pelaksanaan / Logbook Perubahan (MPR05).
 * Menyimpan informasi pelaksanaan 1:1 dan mendukung unggah bukti dukung polimorfik ke MrLampiran (disk public, maks 10 MB).
 */
#[Layout('components.layouts.app')]
class LogbookForm extends Component
{
    use WithFileUploads;

    public MprKonteks $konteks;
    public MprPerubahan $perubahan;

    public string $unit_pelaksana = '';
    public string $pic_pelaksana = '';
    public string $tanggal_mulai = '';
    public string $tanggal_selesai = '';
    public string $catatan = '';
    public string $bukti_url = '';
    public string $status_perubahan = '';

    public $file_bukti = null;

    public function mount(MprKonteks $konteks, MprPerubahan $perubahan): void
    {
        abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

        $this->konteks = $konteks;
        $this->perubahan = $perubahan;
        $this->loadLogbook();
    }

    public function loadLogbook(): void
    {
        $log = $this->perubahan->logbook()->first();

        $this->unit_pelaksana = $log?->unit_pelaksana ?? '';
        $this->pic_pelaksana = $log?->pic_pelaksana ?? '';
        $this->tanggal_mulai = $log?->tanggal_mulai ? $log->tanggal_mulai->format('Y-m-d') : '';
        $this->tanggal_selesai = $log?->tanggal_selesai ? $log->tanggal_selesai->format('Y-m-d') : '';
        $this->catatan = $log?->catatan ?? '';
        $this->bukti_url = $log?->bukti_url ?? '';
        $this->status_perubahan = $log?->status_perubahan ?? '';
    }

    private function guardWrite(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }
    }

    public function save(): void
    {
        $this->guardWrite();

        $rules = [
            'unit_pelaksana'   => 'nullable|string|max:255',
            'pic_pelaksana'    => 'nullable|string|max:255',
            'tanggal_mulai'    => 'nullable|date',
            'tanggal_selesai'  => 'nullable|date',
            'catatan'          => 'nullable|string|max:5000',
            'bukti_url'        => 'nullable|url|max:255',
            'status_perubahan' => ['nullable', Rule::in(['Berhasil', 'Gagal', 'Dibatalkan'])],
            'file_bukti'       => 'nullable|file|max:10240', // maks 10 MB
        ];

        if (filled($this->tanggal_mulai)) {
            $rules['tanggal_selesai'] = 'nullable|date|after_or_equal:tanggal_mulai';
        }

        $this->validate($rules, [
            'tanggal_mulai.date'             => 'Tanggal mulai harus berupa tanggal yang valid.',
            'tanggal_selesai.date'           => 'Tanggal selesai harus berupa tanggal yang valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'bukti_url.url'                  => 'URL bukti harus berupa tautan URL yang valid (mis. https://...).',
            'status_perubahan.in'            => 'Status perubahan tidak valid.',
            'file_bukti.max'                 => 'Ukuran berkas lampiran maksimal 10 MB.',
        ]);

        $logbook = $this->perubahan->logbook()->updateOrCreate(
            [],
            [
                'unit_pelaksana'   => filled($this->unit_pelaksana) ? trim($this->unit_pelaksana) : null,
                'pic_pelaksana'    => filled($this->pic_pelaksana) ? trim($this->pic_pelaksana) : null,
                'tanggal_mulai'    => filled($this->tanggal_mulai) ? $this->tanggal_mulai : null,
                'tanggal_selesai'  => filled($this->tanggal_selesai) ? $this->tanggal_selesai : null,
                'catatan'          => filled($this->catatan) ? $this->catatan : null,
                'bukti_url'        => filled($this->bukti_url) ? trim($this->bukti_url) : null,
                'status_perubahan' => filled($this->status_perubahan) ? $this->status_perubahan : null,
            ]
        );

        if ($this->file_bukti) {
            $path = $this->file_bukti->store('lampiran', 'public');
            $logbook->lampiran()->create([
                'nama_file'   => $this->file_bukti->getClientOriginalName(),
                'path_file'   => $path,
                'mime_type'   => $this->file_bukti->getMimeType(),
                'ukuran_kb'   => (int) round($this->file_bukti->getSize() / 1024),
                'uploaded_by' => auth()->id(),
            ]);
            $this->file_bukti = null;
        }

        $pesan = 'Logbook pelaksanaan perubahan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('logbook-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function deleteLampiran(int $lampiranId): void
    {
        $this->guardWrite();

        $logbook = $this->perubahan->logbook()->first();
        if (! $logbook) {
            return;
        }

        $lampiran = $logbook->lampiran()->findOrFail($lampiranId);
        if ($lampiran->path_file) {
            Storage::disk('public')->delete($lampiran->path_file);
        }
        $lampiran->forceDelete();

        $pesan = 'Lampiran bukti pelaksanaan berhasil dihapus.';
        session()->flash('success', $pesan);
        $this->dispatch('lampiran-deleted', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Dihapus');
    }

    public function render()
    {
        $user = auth()->user();
        $isEditable = $this->konteks->isEditableByOperator() && ! $user->isAdmin();

        $logbook = $this->perubahan->logbook()->with('lampiran')->first();
        $lampiranList = $logbook ? $logbook->lampiran : collect();
        $persetujuanPelaksanaan = $this->perubahan->persetujuan()->where('tahap', 'pelaksanaan')->first();

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.logbook.form', [
            'isEditable'             => $isEditable,
            'isAdmin'                => $user->isAdmin(),
            'lampiranList'           => $lampiranList,
            'persetujuanPelaksanaan' => $persetujuanPelaksanaan,
            'breadcrumb'             => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                $this->perubahan->id_perubahan => route('mpr.perubahan.hub', [$this->konteks, $this->perubahan]),
                'Pelaksanaan (Logbook)' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
