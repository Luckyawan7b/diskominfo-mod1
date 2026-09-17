<?php

namespace App\Livewire\Layanan;

use App\Models\Layanan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LayananForm extends Component
{
    public ?Layanan $layanan = null;
    
    public ?string $bidang_bagian = null;
    public string $status_layanan = 'berjalan';
    public string $nama_layanan = '';
    public ?string $deskripsi_layanan = null;
    public ?string $target_pengguna = null;
    public ?string $kl_terkait = null;
    public ?string $supplier_data = null;
    public ?string $nama_data_input = null;
    public ?string $nama_data_output = null;
    public ?string $sifat_data = null;
    public ?string $jenis_data = null;
    public ?string $validitas_data = null;
    public bool $interoperabilitas = false;
    public ?string $tujuan_integrasi = null;
    public ?string $metode_integrasi = null;
    public ?string $link_dokumen_integrasi = null;
    public ?string $nama_aplikasi = null;
    public ?string $tipe_aplikasi = null;
    public ?string $link_aplikasi = null;
    public ?string $keluaran_aplikasi = null;
    public ?string $letak_server = null;
    public ?string $link_dpa = null;
    public ?int $tahun_pembuatan = null;
    public ?string $link_sla = null;
    public ?string $link_sop = null;
    public ?string $helpdesk = null;
    public bool $is_prioritas = false;

    public function mount(?Layanan $layanan = null)
    {
        if ($layanan && $layanan->exists) {
            // Pastikan operator hanya bisa edit layanan miliknya sendiri (scoping by created_by)
            if (auth()->user()->isOperator() && $layanan->created_by !== auth()->id()) {
                abort(403, 'Unauthorized access.');
            }
            
            $this->layanan = $layanan;
            $this->bidang_bagian = $layanan->bidang_bagian ?? '';
            $this->status_layanan = $layanan->status_layanan ?? 'berjalan';
            $this->nama_layanan = $layanan->nama_layanan ?? '';
            $this->deskripsi_layanan = $layanan->deskripsi_layanan ?? '';
            $this->target_pengguna = $layanan->target_pengguna ?? '';
            $this->kl_terkait = $layanan->kl_terkait ?? '';
            $this->supplier_data = $layanan->supplier_data ?? '';
            $this->nama_data_input = $layanan->nama_data_input ?? '';
            $this->nama_data_output = $layanan->nama_data_output ?? '';
            $this->sifat_data = $layanan->sifat_data ?? '';
            $this->jenis_data = $layanan->jenis_data ?? '';
            $this->validitas_data = $layanan->validitas_data ?? '';
            $this->interoperabilitas = $layanan->interoperabilitas ?? false;
            $this->tujuan_integrasi = $layanan->tujuan_integrasi ?? '';
            $this->metode_integrasi = $layanan->metode_integrasi ?? '';
            $this->link_dokumen_integrasi = $layanan->link_dokumen_integrasi ?? '';
            $this->nama_aplikasi = $layanan->nama_aplikasi ?? '';
            $this->tipe_aplikasi = $layanan->tipe_aplikasi ?? '';
            $this->link_aplikasi = $layanan->link_aplikasi ?? '';
            $this->keluaran_aplikasi = $layanan->keluaran_aplikasi ?? '';
            $this->letak_server = $layanan->letak_server ?? '';
            $this->link_dpa = $layanan->link_dpa ?? '';
            $this->tahun_pembuatan = $layanan->tahun_pembuatan;
            $this->link_sla = $layanan->link_sla ?? '';
            $this->link_sop = $layanan->link_sop ?? '';
            $this->helpdesk = $layanan->helpdesk ?? '';
            $this->is_prioritas = $layanan->is_prioritas ?? false;
        }
    }

    public function save()
    {
        // Ubah string kosong menjadi null agar lolos validasi nullable dan tidak error tipe data di DB
        $properties = ['bidang_bagian', 'deskripsi_layanan', 'target_pengguna', 'kl_terkait', 'supplier_data', 'nama_data_input', 'nama_data_output', 'sifat_data', 'jenis_data', 'validitas_data', 'tujuan_integrasi', 'metode_integrasi', 'link_dokumen_integrasi', 'nama_aplikasi', 'tipe_aplikasi', 'link_aplikasi', 'keluaran_aplikasi', 'letak_server', 'link_dpa', 'tahun_pembuatan', 'link_sla', 'link_sop', 'helpdesk'];
        foreach ($properties as $prop) {
            if ($this->$prop === '') {
                $this->$prop = null;
            }
        }

        $validatedData = $this->validate([
            'bidang_bagian'         => 'nullable|string|max:255',
            'status_layanan'        => 'required|in:berjalan,direncanakan,dihentikan',
            'nama_layanan'          => 'required|string|max:255',
            'deskripsi_layanan'     => 'nullable|string',
            'target_pengguna'       => 'required|in:publik/masyarakat,internal pemerintahan',
            'kl_terkait'            => 'nullable|string|max:255',
            'supplier_data'         => 'nullable|string|max:255',
            'nama_data_input'       => 'nullable|string',
            'nama_data_output'      => 'nullable|string',
            'sifat_data'            => 'required|in:terbuka,terbatas,tertutup',
            'jenis_data'            => 'nullable|string|max:255',
            'validitas_data'        => 'nullable|string|max:255',
            'interoperabilitas'     => 'boolean',
            'tujuan_integrasi'      => 'nullable|string',
            'metode_integrasi'      => 'nullable|string|max:255',
            'link_dokumen_integrasi'=> 'nullable|url|max:255',
            'nama_aplikasi'         => 'nullable|string|max:255',
            'tipe_aplikasi'         => 'nullable|string|max:255',
            'link_aplikasi'         => 'nullable|url|max:255',
            'keluaran_aplikasi'     => 'nullable|string',
            'letak_server'          => 'nullable|string|max:255',
            'link_dpa'              => 'nullable|url|max:255',
            'tahun_pembuatan'       => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'link_sla'              => 'nullable|string|max:255',
            'link_sop'              => 'nullable|string|max:255',
            'helpdesk'              => 'nullable|string|max:255',
            'is_prioritas'          => 'boolean',
        ]);

        if (!$this->layanan || !$this->layanan->exists) {
            // Saat create: isi created_by dan unit_pelaksana otomatis dari profil user
            $validatedData['created_by']    = auth()->id();
            $validatedData['unit_pelaksana'] = auth()->user()->nama_dinas ?? '';
            $layanan = Layanan::create($validatedData);
            session()->flash('success', 'Layanan berhasil dibuat.');
            return redirect()->route('layanan.index');
        } else {
            $this->layanan->update($validatedData);
            session()->flash('success', 'Layanan berhasil diperbarui.');
            return redirect()->route('layanan.index');
        }
    }

    public function render()
    {
        return view('livewire.layanan.layanan-form', [
            'breadcrumb' => [
                'Layanan' => route('layanan.index'),
                $this->layanan && $this->layanan->exists ? 'Edit Layanan' : 'Layanan Baru' => null,
            ],
        ]);
    }
}
