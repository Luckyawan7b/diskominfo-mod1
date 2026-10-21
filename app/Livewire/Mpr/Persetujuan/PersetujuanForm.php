<?php

namespace App\Livewire\Mpr\Persetujuan;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Services\MprAlurResolver;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Persetujuan Perubahan (MPR02A, MPR02B, MPR03.1).
 * Hanya data isian (status, PIC penyetuju, tanggal, catatan) untuk tiap tahap yang relevan.
 */
#[Layout('components.layouts.app')]
class PersetujuanForm extends Component
{
    public MprKonteks $konteks;
    public MprPerubahan $perubahan;

    public const TAHAP_META = [
        'analisis_teknis'     => 'Persetujuan Analisis Dampak Teknis / Risiko (2A)',
        'analisis_organisasi' => 'Persetujuan Analisis Dampak Organisasi (2B)',
        'pelaksanaan'         => 'Persetujuan Pelaksanaan Perubahan',
    ];

    /**
     * Keyed by tahap:
     * [
     *   tahap => ['status' => string, 'pic_penyetuju' => string, 'tanggal' => string, 'catatan' => string]
     * ]
     */
    public array $items = [];

    public function mount(MprKonteks $konteks, MprPerubahan $perubahan): void
    {
        abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

        $this->konteks = $konteks;
        $this->perubahan = $perubahan;
        $this->loadItems();
    }

    public function loadItems(): void
    {
        $tersimpan = $this->perubahan->persetujuan()->get()->keyBy('tahap');

        $this->items = [];
        foreach (array_keys(self::TAHAP_META) as $tahap) {
            $row = $tersimpan->get($tahap);
            $this->items[$tahap] = [
                'status'        => $row?->status ?? '',
                'pic_penyetuju' => $row?->pic_penyetuju ?? '',
                'tanggal'       => $row?->tanggal ? $row->tanggal->format('Y-m-d') : '',
                'catatan'       => $row?->catatan ?? '',
            ];
        }
    }

    /**
     * @return array<string, bool>
     */
    private function tahapRelevan(): array
    {
        $resolver = MprAlurResolver::untuk($this->perubahan->fresh(['klasifikasi', 'lingkup']));

        return [
            'analisis_teknis'     => $resolver->perluAnalisisTeknis(),
            'analisis_organisasi' => $resolver->perluAnalisisOrganisasi(),
            'pelaksanaan'         => $resolver->perluPersetujuan(),
        ];
    }

    private function isWajib(): bool
    {
        return in_array(true, $this->tahapRelevan(), true);
    }

    public function save(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }

        $relevan = $this->tahapRelevan();
        $tahapAktif = array_keys(array_filter($relevan));

        if ($tahapAktif === []) {
            abort(403, 'Formulir Persetujuan tidak wajib untuk klasifikasi dan lingkup perubahan ini.');
        }

        $rules = [];
        $messages = [];
        foreach ($tahapAktif as $tahap) {
            $rules["items.$tahap.status"]        = ['nullable', Rule::in(['Disetujui', 'Tidak Disetujui'])];
            $rules["items.$tahap.pic_penyetuju"] = 'nullable|string|max:255';
            $rules["items.$tahap.tanggal"]       = 'nullable|date';
            $rules["items.$tahap.catatan"]       = 'nullable|string|max:5000';

            $messages["items.$tahap.status.in"]      = 'Status persetujuan tidak valid.';
            $messages["items.$tahap.tanggal.date"]   = 'Format tanggal tidak valid.';
        }

        $this->validate($rules, $messages);

        foreach ($tahapAktif as $tahap) {
            $data = $this->items[$tahap] ?? [];
            $status = filled($data['status'] ?? null) ? $data['status'] : null;
            $pic = filled($data['pic_penyetuju'] ?? null) ? trim($data['pic_penyetuju']) : null;
            $tanggal = filled($data['tanggal'] ?? null) ? $data['tanggal'] : null;
            $catatan = filled($data['catatan'] ?? null) ? $data['catatan'] : null;

            $adaIsian = $status !== null || $pic !== null || $tanggal !== null || $catatan !== null;

            if ($adaIsian) {
                $this->perubahan->persetujuan()->updateOrCreate(
                    ['tahap' => $tahap],
                    [
                        'status'        => $status,
                        'pic_penyetuju' => $pic,
                        'tanggal'       => $tanggal,
                        'catatan'       => $catatan,
                    ]
                );
            } else {
                $this->perubahan->persetujuan()->where('tahap', $tahap)->delete();
            }
        }

        $pesan = 'Data persetujuan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('persetujuan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function render()
    {
        $user = auth()->user();
        $relevan = $this->tahapRelevan();
        $wajib = in_array(true, $relevan, true);
        $isEditable = $this->konteks->isEditableByOperator() && ! $user->isAdmin() && $wajib;

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.persetujuan.form', [
            'wajib'        => $wajib,
            'tahapRelevan' => $relevan,
            'tahapMeta'    => self::TAHAP_META,
            'isEditable'   => $isEditable,
            'isAdmin'      => $user->isAdmin(),
            'breadcrumb'   => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                $this->perubahan->id_perubahan => route('mpr.perubahan.hub', [$this->konteks, $this->perubahan]),
                'Persetujuan' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
