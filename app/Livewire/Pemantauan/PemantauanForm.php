<?php

namespace App\Livewire\Pemantauan;

use App\Models\MrKonteks;
use App\Models\MrPemantauanRisiko;
use App\Models\MrRisiko;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class PemantauanForm extends Component
{
    use WithFileUploads;

    public MrKonteks $konteks;
    public ?int $selectedRisikoId = null;

    public string $periode = 'semester_1';
    public int $tahun = 2026;
    public string $hasil_pelaksanaan = '';
    public string $data_dukung_catatan = '';
    public $file_bukti = null;

    public function mount(MrKonteks $konteks): void
    {
        $this->konteks = $konteks;
        $this->tahun = $konteks->tahun_penilaian;
        $firstRisiko = $konteks->risiko()->first();
        if ($firstRisiko) {
            $this->selectedRisikoId = $firstRisiko->id;
        }
    }

    public function savePemantauan(): void
    {
        $this->validate([
            'selectedRisikoId'  => 'required|exists:mr_risiko,id',
            'periode'           => 'required|string',
            'tahun'             => 'required|integer',
            'hasil_pelaksanaan' => 'required|string',
            'file_bukti'        => 'nullable|file|max:10240', // max 10MB
        ]);

        $pemantauan = MrPemantauanRisiko::create([
            'mr_risiko_id'        => $this->selectedRisikoId,
            'periode'             => $this->periode,
            'tahun'               => $this->tahun,
            'hasil_pelaksanaan'   => $this->hasil_pelaksanaan,
            'data_dukung_catatan' => $this->data_dukung_catatan,
            'created_by'          => auth()->id(),
        ]);

        if ($this->file_bukti) {
            $path = $this->file_bukti->store('lampiran', 'public');
            $pemantauan->lampiran()->create([
                'nama_file'   => $this->file_bukti->getClientOriginalName(),
                'path_file'   => $path,
                'mime_type'   => $this->file_bukti->getMimeType(),
                'ukuran_kb'   => round($this->file_bukti->getSize() / 1024),
                'uploaded_by' => auth()->id(),
            ]);
            $this->file_bukti = null;
        }

        $this->reset(['hasil_pelaksanaan', 'data_dukung_catatan']);
        session()->flash('success', 'Catatan pemantauan berhasil ditambahkan.');
    }

    public function deletePemantauan(int $id): void
    {
        $pemantauan = MrPemantauanRisiko::findOrFail($id);
        $pemantauan->delete();
        session()->flash('success', 'Catatan pemantauan dihapus.');
    }

    public function render()
    {
        $risikos = $this->konteks->risiko()->orderBy('prioritas_risiko')->get();
        $selectedRisiko = $this->selectedRisikoId ? MrRisiko::with('pemantauan.lampiran')->find($this->selectedRisikoId) : null;
        $pemantauanList = $selectedRisiko ? $selectedRisiko->pemantauan()->with('lampiran')->latest()->get() : collect();

        $user = auth()->user();
        // Gunakan scope accessibleBy: operator -> layanan miliknya; admin -> semua
        $availableKonteks = MrKonteks::accessibleBy($user)
            ->orderByDesc('tahun_penilaian')
            ->get();

        return view('livewire.pemantauan.form', [
            'risikos'         => $risikos,
            'selectedRisiko'  => $selectedRisiko,
            'pemantauanList'  => $pemantauanList,
            'isEditable'      => $this->konteks->isEditableByOperator() || auth()->user()->isAdmin(),
            'breadcrumb'      => [
                'Manajemen Risiko' => route('konteks.index'),
                'Konteks ' . $this->konteks->tahun_penilaian . ' / ' . $this->konteks->tahun_pelaksanaan => route('konteks.form', $this->konteks),
                'Pemantauan Risiko' => null,
            ],
        ])->layout('components.layouts.app', [
            'konteks' => $this->konteks,
            'availableKonteks' => $availableKonteks,
        ]);
    }
}
