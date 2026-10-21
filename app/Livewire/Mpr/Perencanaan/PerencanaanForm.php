<?php

namespace App\Livewire\Mpr\Perencanaan;

use App\Models\MprIndikatorKeberhasilan;
use App\Models\MprKomunikasi;
use App\Models\MprKonteks;
use App\Models\MprPelatihan;
use App\Models\MprPerubahan;
use App\Models\MprStakeholder;
use App\Models\MprSumberDaya;
use App\Models\RefJenisResistensi;
use App\Models\RefKategoriStakeholder;
use App\Models\RefStrategiIt;
use App\Models\RefStrategiOrganisasi;
use App\Models\RefTingkatResistensi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * F3 — Perencanaan Perubahan (MPR03).
 * Tujuh bagian dalam satu halaman berbasis tab, masing-masing menyimpan secara independen.
 */
#[Layout('components.layouts.app')]
class PerencanaanForm extends Component
{
    public MprKonteks $konteks;
    public MprPerubahan $perubahan;

    public const TABS = [
        'implementasi'           => '3.1 Implementasi',
        'strategi'               => '3.2 Strategi',
        'sumber_daya'            => '3.1 Sumber Daya',
        'indikator_keberhasilan' => '3.1 Indikator Keberhasilan',
        'stakeholder'            => '3.3 Stakeholder',
        'komunikasi'             => '3.4 Komunikasi',
        'pelatihan'              => '3.5 Pelatihan',
    ];

    #[Url(as: 'tab')]
    public string $activeTab = 'implementasi';

    // ─── Tab 1: Implementasi (1:1) ───────────────────────────────────────────
    public string $tanggal_mulai = '';
    public string $tanggal_selesai = '';
    public string $jangka_waktu = '';
    public string $pic_implementasi = '';

    // ─── Tab 2: Strategi (1:1) ───────────────────────────────────────────────
    public ?int $ref_strategi_it_id = null;
    public string $deskripsi_strategi_it = '';
    public string $rollback_plan = '';
    public ?int $ref_strategi_organisasi_id = null;
    public string $detail_strategi_organisasi = '';

    // ─── Tab 3–7: Baris berulang (1:N) ──────────────────────────────────────
    public array $sumberDayaBlocks = [];
    public array $indikatorBlocks = [];
    public array $stakeholderBlocks = [];
    public array $komunikasiBlocks = [];
    public array $pelatihanBlocks = [];

    public function mount(MprKonteks $konteks, MprPerubahan $perubahan): void
    {
        abort_unless($perubahan->mpr_konteks_id === $konteks->id, 404);

        $this->konteks = $konteks;
        $this->perubahan = $perubahan;

        if (! array_key_exists($this->activeTab, self::TABS)) {
            $this->activeTab = 'implementasi';
        }

        $this->loadAll();
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, self::TABS)) {
            $this->activeTab = $tab;
        }
    }

    public function loadAll(): void
    {
        $imp = $this->perubahan->implementasi()->first();
        $this->tanggal_mulai = $imp?->tanggal_mulai ? $imp->tanggal_mulai->format('Y-m-d') : '';
        $this->tanggal_selesai = $imp?->tanggal_selesai ? $imp->tanggal_selesai->format('Y-m-d') : '';
        $this->jangka_waktu = $imp?->jangka_waktu ?? '';
        $this->pic_implementasi = $imp?->pic_implementasi ?? '';

        $str = $this->perubahan->strategi()->first();
        $this->ref_strategi_it_id = $str?->ref_strategi_it_id;
        $this->deskripsi_strategi_it = $str?->deskripsi_strategi_it ?? '';
        $this->rollback_plan = $str?->rollback_plan ?? '';
        $this->ref_strategi_organisasi_id = $str?->ref_strategi_organisasi_id;
        $this->detail_strategi_organisasi = $str?->detail_strategi_organisasi ?? '';

        $this->sumberDayaBlocks = $this->perubahan->sumberDaya()
            ->get()
            ->map(fn (MprSumberDaya $s) => [
                'id'               => $s->id,
                'jenis'            => $s->jenis ?? '',
                'deskripsi'        => $s->deskripsi ?? '',
                'alokasi_anggaran' => $s->alokasi_anggaran !== null ? (string) $s->alokasi_anggaran : '',
                'skema_pembiayaan' => $s->skema_pembiayaan ?? '',
                'pic'              => $s->pic ?? '',
            ])
            ->all();

        $this->indikatorBlocks = $this->perubahan->indikatorKeberhasilan()
            ->get()
            ->map(fn (MprIndikatorKeberhasilan $ik) => [
                'id'        => $ik->id,
                'indikator' => $ik->indikator ?? '',
                'satuan'    => $ik->satuan ?? '',
                'target'    => $ik->target !== null ? (string) $ik->target : '',
                'realisasi' => $ik->realisasi !== null ? (string) $ik->realisasi : '',
            ])
            ->all();

        $this->stakeholderBlocks = $this->perubahan->stakeholder()
            ->orderBy('id')
            ->get()
            ->map(fn (MprStakeholder $st) => [
                'id'                          => $st->id,
                'nama_stakeholder'            => $st->nama_stakeholder ?? '',
                'ref_kategori_stakeholder_id' => $st->ref_kategori_stakeholder_id,
                'ref_jenis_resistensi_id'     => $st->ref_jenis_resistensi_id,
                'ref_tingkat_resistensi_id'   => $st->ref_tingkat_resistensi_id,
            ])
            ->all();

        $this->komunikasiBlocks = $this->perubahan->komunikasi()
            ->orderBy('id')
            ->get()
            ->map(fn (MprKomunikasi $k) => [
                'id'                 => $k->id,
                'informasi'          => $k->informasi ?? '',
                'pengirim'           => $k->pengirim ?? '',
                'target_audiens'     => $k->target_audiens ?? '',
                'media_komunikasi'   => $k->media_komunikasi ?? '',
                'periode_komunikasi' => $k->periode_komunikasi ?? '',
            ])
            ->all();

        $this->pelatihanBlocks = $this->perubahan->pelatihan()
            ->orderBy('id')
            ->get()
            ->map(fn (MprPelatihan $p) => [
                'id'                  => $p->id,
                'nama_pelatihan'      => $p->nama_pelatihan ?? '',
                'target_peserta'      => $p->target_peserta ?? '',
                'target_jumlah'       => $p->target_jumlah !== null ? (string) $p->target_jumlah : '',
                'metode_pelatihan'    => $p->metode_pelatihan ?? '',
                'tanggal_pelaksanaan' => $p->tanggal_pelaksanaan ? $p->tanggal_pelaksanaan->format('Y-m-d') : '',
            ])
            ->all();
    }

    private function guardWrite(): void
    {
        if (auth()->user()->isAdmin()) {
            abort(403, 'Admin hanya memiliki akses lihat (read-only).');
        }
    }

    // ─── Tab 1: Implementasi ─────────────────────────────────────────────────

    public function saveImplementasi(): void
    {
        $this->guardWrite();

        $rules = [
            'tanggal_mulai'    => 'nullable|date',
            'tanggal_selesai'  => 'nullable|date',
            'jangka_waktu'     => 'nullable|string|max:255',
            'pic_implementasi' => 'nullable|string|max:255',
        ];
        if (filled($this->tanggal_mulai)) {
            $rules['tanggal_selesai'] = 'nullable|date|after_or_equal:tanggal_mulai';
        }

        $this->validate($rules, [
            'tanggal_mulai.date'             => 'Tanggal mulai harus berupa tanggal yang valid.',
            'tanggal_selesai.date'           => 'Tanggal selesai harus berupa tanggal yang valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $this->perubahan->implementasi()->updateOrCreate(
            [],
            [
                'tanggal_mulai'    => filled($this->tanggal_mulai) ? $this->tanggal_mulai : null,
                'tanggal_selesai'  => filled($this->tanggal_selesai) ? $this->tanggal_selesai : null,
                'jangka_waktu'     => filled($this->jangka_waktu) ? trim($this->jangka_waktu) : null,
                'pic_implementasi' => filled($this->pic_implementasi) ? trim($this->pic_implementasi) : null,
            ]
        );

        $pesan = 'Perencanaan implementasi berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    // ─── Tab 2: Strategi ─────────────────────────────────────────────────────

    public function saveStrategi(): void
    {
        $this->guardWrite();

        $this->validate([
            'ref_strategi_it_id'         => 'nullable|integer|exists:ref_strategi_it,id',
            'deskripsi_strategi_it'      => 'nullable|string|max:5000',
            'rollback_plan'              => 'nullable|string|max:5000',
            'ref_strategi_organisasi_id' => 'nullable|integer|exists:ref_strategi_organisasi,id',
            'detail_strategi_organisasi' => 'nullable|string|max:5000',
        ]);

        $this->perubahan->strategi()->updateOrCreate(
            [],
            [
                'ref_strategi_it_id'         => $this->ref_strategi_it_id ?: null,
                'deskripsi_strategi_it'      => filled($this->deskripsi_strategi_it) ? $this->deskripsi_strategi_it : null,
                'rollback_plan'              => filled($this->rollback_plan) ? $this->rollback_plan : null,
                'ref_strategi_organisasi_id' => $this->ref_strategi_organisasi_id ?: null,
                'detail_strategi_organisasi' => filled($this->detail_strategi_organisasi) ? $this->detail_strategi_organisasi : null,
            ]
        );

        $pesan = 'Strategi perubahan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    // ─── Tab 3: Sumber Daya ──────────────────────────────────────────────────

    public function addSumberDaya(): void
    {
        $this->guardWrite();

        $this->sumberDayaBlocks[] = [
            'id'               => null,
            'jenis'            => '',
            'deskripsi'        => '',
            'alokasi_anggaran' => '',
            'skema_pembiayaan' => '',
            'pic'              => '',
        ];
    }

    public function saveSumberDaya(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->sumberDayaBlocks[$index])) {
            return;
        }

        $block = $this->sumberDayaBlocks[$index];
        $existingId = $block['id'] ?? null;
        $model = null;

        if ($existingId !== null) {
            $model = $this->perubahan->sumberDaya()->find($existingId);
            if (! $model) {
                return;
            }
        }

        $this->validate([
            "sumberDayaBlocks.$index.jenis"            => 'required|string|max:255',
            "sumberDayaBlocks.$index.deskripsi"        => 'required|string|max:5000',
            "sumberDayaBlocks.$index.alokasi_anggaran" => 'nullable|numeric|min:0',
            "sumberDayaBlocks.$index.skema_pembiayaan" => 'nullable|string|max:255',
            "sumberDayaBlocks.$index.pic"              => 'nullable|string|max:255',
        ], [
            "sumberDayaBlocks.$index.jenis.required"           => 'Jenis sumber daya wajib diisi.',
            "sumberDayaBlocks.$index.deskripsi.required"       => 'Deskripsi sumber daya wajib diisi.',
            "sumberDayaBlocks.$index.alokasi_anggaran.numeric" => 'Alokasi anggaran harus berupa angka.',
            "sumberDayaBlocks.$index.alokasi_anggaran.min"     => 'Alokasi anggaran tidak boleh negatif.',
        ]);

        $data = [
            'jenis'            => trim($block['jenis']),
            'deskripsi'        => $block['deskripsi'],
            'alokasi_anggaran' => filled($block['alokasi_anggaran'] ?? null) ? $block['alokasi_anggaran'] : null,
            'skema_pembiayaan' => filled($block['skema_pembiayaan'] ?? null) ? trim($block['skema_pembiayaan']) : null,
            'pic'              => filled($block['pic'] ?? null) ? trim($block['pic']) : null,
            'urutan'           => $index,
        ];

        if ($model) {
            $model->update($data);
        } else {
            $model = $this->perubahan->sumberDaya()->create($data);
            $this->sumberDayaBlocks[$index]['id'] = $model->id;
        }

        $pesan = 'Sumber daya berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function removeSumberDaya(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->sumberDayaBlocks[$index])) {
            return;
        }

        $id = $this->sumberDayaBlocks[$index]['id'] ?? null;
        if ($id !== null) {
            $model = $this->perubahan->sumberDaya()->find($id);
            if (! $model) {
                return;
            }
            $model->delete();
        }

        unset($this->sumberDayaBlocks[$index]);
        $this->sumberDayaBlocks = array_values($this->sumberDayaBlocks);
        $this->persistUrutanSumberDaya();

        $pesan = 'Sumber daya berhasil dihapus.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-deleted', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Dihapus');
    }

    public function moveSumberDayaUp(int $index): void
    {
        $this->guardWrite();
        $this->swapBlocks($this->sumberDayaBlocks, $index, $index - 1);
        $this->persistUrutanSumberDaya();
    }

    public function moveSumberDayaDown(int $index): void
    {
        $this->guardWrite();
        $this->swapBlocks($this->sumberDayaBlocks, $index, $index + 1);
        $this->persistUrutanSumberDaya();
    }

    private function persistUrutanSumberDaya(): void
    {
        foreach ($this->sumberDayaBlocks as $urutan => $b) {
            if (! empty($b['id'])) {
                $this->perubahan->sumberDaya()->whereKey($b['id'])->update(['urutan' => $urutan]);
            }
        }
    }

    // ─── Tab 4: Indikator Keberhasilan ───────────────────────────────────────

    public function addIndikator(): void
    {
        $this->guardWrite();

        $this->indikatorBlocks[] = [
            'id'        => null,
            'indikator' => '',
            'satuan'    => '',
            'target'    => '',
            'realisasi' => '',
        ];
    }

    public function saveIndikator(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->indikatorBlocks[$index])) {
            return;
        }

        $block = $this->indikatorBlocks[$index];
        $existingId = $block['id'] ?? null;
        $model = null;

        if ($existingId !== null) {
            $model = $this->perubahan->indikatorKeberhasilan()->find($existingId);
            if (! $model) {
                return;
            }
        }

        $this->validate([
            "indikatorBlocks.$index.indikator" => 'required|string|max:5000',
            "indikatorBlocks.$index.satuan"    => 'nullable|string|max:100',
            "indikatorBlocks.$index.target"    => 'nullable|numeric',
            "indikatorBlocks.$index.realisasi" => 'nullable|numeric',
        ], [
            "indikatorBlocks.$index.indikator.required" => 'Indikator keberhasilan wajib diisi.',
            "indikatorBlocks.$index.target.numeric"     => 'Target harus berupa angka.',
            "indikatorBlocks.$index.realisasi.numeric"  => 'Realisasi harus berupa angka.',
        ]);

        $data = [
            'indikator' => $block['indikator'],
            'satuan'    => filled($block['satuan'] ?? null) ? trim($block['satuan']) : null,
            'target'    => filled($block['target'] ?? null) ? $block['target'] : null,
            'realisasi' => filled($block['realisasi'] ?? null) ? $block['realisasi'] : null,
            'urutan'    => $index,
        ];

        if ($model) {
            $model->update($data);
        } else {
            $model = $this->perubahan->indikatorKeberhasilan()->create($data);
            $this->indikatorBlocks[$index]['id'] = $model->id;
        }

        $pesan = 'Indikator keberhasilan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function removeIndikator(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->indikatorBlocks[$index])) {
            return;
        }

        $id = $this->indikatorBlocks[$index]['id'] ?? null;
        if ($id !== null) {
            $model = $this->perubahan->indikatorKeberhasilan()->find($id);
            if (! $model) {
                return;
            }
            $model->delete();
        }

        unset($this->indikatorBlocks[$index]);
        $this->indikatorBlocks = array_values($this->indikatorBlocks);
        $this->persistUrutanIndikator();

        $pesan = 'Indikator keberhasilan berhasil dihapus.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-deleted', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Dihapus');
    }

    public function moveIndikatorUp(int $index): void
    {
        $this->guardWrite();
        $this->swapBlocks($this->indikatorBlocks, $index, $index - 1);
        $this->persistUrutanIndikator();
    }

    public function moveIndikatorDown(int $index): void
    {
        $this->guardWrite();
        $this->swapBlocks($this->indikatorBlocks, $index, $index + 1);
        $this->persistUrutanIndikator();
    }

    private function persistUrutanIndikator(): void
    {
        foreach ($this->indikatorBlocks as $urutan => $b) {
            if (! empty($b['id'])) {
                $this->perubahan->indikatorKeberhasilan()->whereKey($b['id'])->update(['urutan' => $urutan]);
            }
        }
    }

    // ─── Tab 5: Stakeholder ──────────────────────────────────────────────────

    public function addStakeholder(): void
    {
        $this->guardWrite();

        $this->stakeholderBlocks[] = [
            'id'                          => null,
            'nama_stakeholder'            => '',
            'ref_kategori_stakeholder_id' => null,
            'ref_jenis_resistensi_id'     => null,
            'ref_tingkat_resistensi_id'   => null,
        ];
    }

    public function saveStakeholder(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->stakeholderBlocks[$index])) {
            return;
        }

        $block = $this->stakeholderBlocks[$index];
        $existingId = $block['id'] ?? null;
        $model = null;

        if ($existingId !== null) {
            $model = $this->perubahan->stakeholder()->find($existingId);
            if (! $model) {
                return;
            }
        }

        $this->validate([
            "stakeholderBlocks.$index.nama_stakeholder"            => 'required|string|max:255',
            "stakeholderBlocks.$index.ref_kategori_stakeholder_id" => 'nullable|integer|exists:ref_kategori_stakeholder,id',
            "stakeholderBlocks.$index.ref_jenis_resistensi_id"     => 'nullable|integer|exists:ref_jenis_resistensi,id',
            "stakeholderBlocks.$index.ref_tingkat_resistensi_id"   => 'nullable|integer|exists:ref_tingkat_resistensi,id',
        ], [
            "stakeholderBlocks.$index.nama_stakeholder.required" => 'Nama pemangku kepentingan wajib diisi.',
        ]);

        $data = [
            'nama_stakeholder'            => trim($block['nama_stakeholder']),
            'ref_kategori_stakeholder_id' => filled($block['ref_kategori_stakeholder_id'] ?? null) ? (int) $block['ref_kategori_stakeholder_id'] : null,
            'ref_jenis_resistensi_id'     => filled($block['ref_jenis_resistensi_id'] ?? null) ? (int) $block['ref_jenis_resistensi_id'] : null,
            'ref_tingkat_resistensi_id'   => filled($block['ref_tingkat_resistensi_id'] ?? null) ? (int) $block['ref_tingkat_resistensi_id'] : null,
        ];

        if ($model) {
            $model->update($data);
        } else {
            $model = $this->perubahan->stakeholder()->create($data);
            $this->stakeholderBlocks[$index]['id'] = $model->id;
        }

        $pesan = 'Data pemangku kepentingan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function removeStakeholder(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->stakeholderBlocks[$index])) {
            return;
        }

        $id = $this->stakeholderBlocks[$index]['id'] ?? null;
        if ($id !== null) {
            $model = $this->perubahan->stakeholder()->find($id);
            if (! $model) {
                return;
            }
            $model->delete();
        }

        unset($this->stakeholderBlocks[$index]);
        $this->stakeholderBlocks = array_values($this->stakeholderBlocks);

        $pesan = 'Data pemangku kepentingan berhasil dihapus.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-deleted', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Dihapus');
    }

    // ─── Tab 6: Komunikasi ───────────────────────────────────────────────────

    public function addKomunikasi(): void
    {
        $this->guardWrite();

        $this->komunikasiBlocks[] = [
            'id'                 => null,
            'informasi'          => '',
            'pengirim'           => '',
            'target_audiens'     => '',
            'media_komunikasi'   => '',
            'periode_komunikasi' => '',
        ];
    }

    public function saveKomunikasi(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->komunikasiBlocks[$index])) {
            return;
        }

        $block = $this->komunikasiBlocks[$index];
        $existingId = $block['id'] ?? null;
        $model = null;

        if ($existingId !== null) {
            $model = $this->perubahan->komunikasi()->find($existingId);
            if (! $model) {
                return;
            }
        }

        $this->validate([
            "komunikasiBlocks.$index.informasi"          => 'required|string|max:5000',
            "komunikasiBlocks.$index.pengirim"           => 'nullable|string|max:255',
            "komunikasiBlocks.$index.target_audiens"     => 'nullable|string|max:255',
            "komunikasiBlocks.$index.media_komunikasi"   => 'nullable|string|max:255',
            "komunikasiBlocks.$index.periode_komunikasi" => 'nullable|string|max:255',
        ], [
            "komunikasiBlocks.$index.informasi.required" => 'Informasi komunikasi wajib diisi.',
        ]);

        $data = [
            'informasi'          => $block['informasi'],
            'pengirim'           => filled($block['pengirim'] ?? null) ? trim($block['pengirim']) : null,
            'target_audiens'     => filled($block['target_audiens'] ?? null) ? trim($block['target_audiens']) : null,
            'media_komunikasi'   => filled($block['media_komunikasi'] ?? null) ? trim($block['media_komunikasi']) : null,
            'periode_komunikasi' => filled($block['periode_komunikasi'] ?? null) ? trim($block['periode_komunikasi']) : null,
        ];

        if ($model) {
            $model->update($data);
        } else {
            $model = $this->perubahan->komunikasi()->create($data);
            $this->komunikasiBlocks[$index]['id'] = $model->id;
        }

        $pesan = 'Rencana komunikasi berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function removeKomunikasi(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->komunikasiBlocks[$index])) {
            return;
        }

        $id = $this->komunikasiBlocks[$index]['id'] ?? null;
        if ($id !== null) {
            $model = $this->perubahan->komunikasi()->find($id);
            if (! $model) {
                return;
            }
            $model->delete();
        }

        unset($this->komunikasiBlocks[$index]);
        $this->komunikasiBlocks = array_values($this->komunikasiBlocks);

        $pesan = 'Rencana komunikasi berhasil dihapus.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-deleted', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Dihapus');
    }

    // ─── Tab 7: Pelatihan ────────────────────────────────────────────────────

    public function addPelatihan(): void
    {
        $this->guardWrite();

        $this->pelatihanBlocks[] = [
            'id'                  => null,
            'nama_pelatihan'      => '',
            'target_peserta'      => '',
            'target_jumlah'       => '',
            'metode_pelatihan'    => '',
            'tanggal_pelaksanaan' => '',
        ];
    }

    public function savePelatihan(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->pelatihanBlocks[$index])) {
            return;
        }

        $block = $this->pelatihanBlocks[$index];
        $existingId = $block['id'] ?? null;
        $model = null;

        if ($existingId !== null) {
            $model = $this->perubahan->pelatihan()->find($existingId);
            if (! $model) {
                return;
            }
        }

        $this->validate([
            "pelatihanBlocks.$index.nama_pelatihan"      => 'required|string|max:255',
            "pelatihanBlocks.$index.target_peserta"      => 'nullable|string|max:255',
            "pelatihanBlocks.$index.target_jumlah"       => 'nullable|integer|min:1',
            "pelatihanBlocks.$index.metode_pelatihan"    => 'nullable|string|max:255',
            "pelatihanBlocks.$index.tanggal_pelaksanaan" => 'nullable|date',
        ], [
            "pelatihanBlocks.$index.nama_pelatihan.required" => 'Nama pelatihan wajib diisi.',
            "pelatihanBlocks.$index.target_jumlah.integer"   => 'Target jumlah peserta harus berupa angka bulat.',
            "pelatihanBlocks.$index.target_jumlah.min"       => 'Target jumlah peserta minimal 1.',
        ]);

        $data = [
            'nama_pelatihan'      => trim($block['nama_pelatihan']),
            'target_peserta'      => filled($block['target_peserta'] ?? null) ? trim($block['target_peserta']) : null,
            'target_jumlah'       => filled($block['target_jumlah'] ?? null) ? (int) $block['target_jumlah'] : null,
            'metode_pelatihan'    => filled($block['metode_pelatihan'] ?? null) ? trim($block['metode_pelatihan']) : null,
            'tanggal_pelaksanaan' => filled($block['tanggal_pelaksanaan'] ?? null) ? $block['tanggal_pelaksanaan'] : null,
        ];

        if ($model) {
            $model->update($data);
        } else {
            $model = $this->perubahan->pelatihan()->create($data);
            $this->pelatihanBlocks[$index]['id'] = $model->id;
        }

        $pesan = 'Rencana pelatihan berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-saved', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Disimpan');
    }

    public function removePelatihan(int $index): void
    {
        $this->guardWrite();

        if (! isset($this->pelatihanBlocks[$index])) {
            return;
        }

        $id = $this->pelatihanBlocks[$index]['id'] ?? null;
        if ($id !== null) {
            $model = $this->perubahan->pelatihan()->find($id);
            if (! $model) {
                return;
            }
            $model->delete();
        }

        unset($this->pelatihanBlocks[$index]);
        $this->pelatihanBlocks = array_values($this->pelatihanBlocks);

        $pesan = 'Rencana pelatihan berhasil dihapus.';
        session()->flash('success', $pesan);
        $this->dispatch('perencanaan-deleted', message: $pesan);
        $this->dispatch('notify', message: $pesan, title: 'Berhasil Dihapus');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function swapBlocks(array &$blocks, int $from, int $to): void
    {
        if (! isset($blocks[$from], $blocks[$to])) {
            return;
        }

        $tmp = $blocks[$from];
        $blocks[$from] = $blocks[$to];
        $blocks[$to] = $tmp;
    }

    public function render()
    {
        $user = auth()->user();
        $isEditable = $this->konteks->isEditableByOperator() && ! $user->isAdmin();

        $strategiItList = RefStrategiIt::orderBy('id')->get();
        $strategiOrgList = RefStrategiOrganisasi::with('kegiatan')->orderBy('id')->get();

        $selectedStrategiIt = $this->ref_strategi_it_id
            ? $strategiItList->firstWhere('id', (int) $this->ref_strategi_it_id)
            : null;
        $selectedStrategiOrg = $this->ref_strategi_organisasi_id
            ? $strategiOrgList->firstWhere('id', (int) $this->ref_strategi_organisasi_id)
            : null;

        $availableKonteks = MprKonteks::where('layanan_id', $this->konteks->layanan_id)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.mpr.perencanaan.form', [
            'tabs'                => self::TABS,
            'isEditable'          => $isEditable,
            'isAdmin'             => $user->isAdmin(),
            'strategiItList'      => $strategiItList,
            'strategiOrgList'     => $strategiOrgList,
            'selectedStrategiIt'  => $selectedStrategiIt,
            'selectedStrategiOrg' => $selectedStrategiOrg,
            'kategoriStakeholder' => RefKategoriStakeholder::orderBy('id')->get(),
            'jenisResistensi'     => RefJenisResistensi::orderBy('id')->get(),
            'tingkatResistensi'   => RefTingkatResistensi::orderBy('id')->get(),
            'breadcrumb'          => [
                'Manajemen Perubahan' => $user->isAdmin()
                    ? route('admin.review.mpr.konteks', $this->konteks->layanan_id)
                    : route('konteks-mpr.index'),
                'Konteks ' . $this->konteks->tahun_penilaian => route('konteks-mpr.form', $this->konteks),
                'Perubahan' => route('mpr.perubahan.index', $this->konteks),
                $this->perubahan->id_perubahan => route('mpr.perubahan.hub', [$this->konteks, $this->perubahan]),
                'Perencanaan (F3)' => null,
            ],
        ])->layout('components.layouts.app', [
            'mprKonteks'       => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
