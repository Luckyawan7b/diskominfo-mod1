<?php

namespace App\Livewire\Mpr\Evaluasi;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * F5 — Evaluasi Perubahan (MPR04).
 * Menampilkan ringkasan target dan realisasi indikator keberhasilan dari F3,
 * serta menyimpan hasil evaluasi, lesson learned, dan rekomendasi solusi.
 */
#[Layout('components.layouts.app')]
class EvaluasiForm extends Component
{
    public MprKonteks $konteks;
    public MprPerubahan $perubahan;

    public string $hasil = '';
    public string $lesson_learned = '';
    public string $rekomendasi_solusi = '';

    public function mount(MprKonteks $konteks, MprPerubahan $perubahan): void
    {
        abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

        $this->konteks = $konteks;
        $this->perubahan = $perubahan;
        $this->loadEvaluasi();
    }

    public function loadEvaluasi(): void
    {
        $eval = $this->perubahan->evaluasi()->first();

        $this->hasil = $eval?->hasil ?? '';
        $this->lesson_learned = $eval?->lesson_learned ?? '';
        $this->rekomendasi_solusi = $eval?->rekomendasi_solusi ?? '';
    }

    public function save(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $this->validate([
            'hasil'              => ['nullable', Rule::in(['Berhasil', 'Tidak Berhasil'])],
            'lesson_learned'     => 'nullable|string|max:5000',
            'rekomendasi_solusi' => 'nullable|string|max:5000',
        ], [
            'hasil.in' => 'Hasil evaluasi harus dipilih antara Berhasil atau Tidak Berhasil.',
        ]);

        $this->perubahan->evaluasi()->updateOrCreate(
            [],
            [
                'hasil'              => filled($this->hasil) ? $this->hasil : null,
                'lesson_learned'     => filled($this->lesson_learned) ? $this->lesson_learned : null,
                'rekomendasi_solusi' => filled($this->rekomendasi_solusi) ? $this->rekomendasi_solusi : null,
            ]
        );

        $pesan = 'Evaluasi perubahan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('evaluasi-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function render()
    {
        $user = auth()->user();
        $isEditable = $this->konteks->isEditableByOperator() && ! $user->isAdmin();

        $indikatorList = $this->perubahan->indikatorKeberhasilan()->get();

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.evaluasi.form', [
            'isEditable'    => $isEditable,
            'isAdmin'       => $user->isAdmin(),
            'indikatorList' => $indikatorList,
            'breadcrumb'    => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                $this->perubahan->id_perubahan => route('mpr.perubahan.hub', [$this->konteks, $this->perubahan]),
                'Evaluasi (F5)' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
