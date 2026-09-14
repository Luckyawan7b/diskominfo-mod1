<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Struktur Pelaksana — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">Formulir 3: Pemilik, koordinator, dan pengelola risiko</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('sasaran.form', $konteks) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Sasaran
            </a>
            @if($isEditable)
                <button wire:click="save" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan
                </button>
            @endif
            <a href="{{ route('risiko.index', $konteks) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                Daftar Risiko
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>


    <div class="rounded-xl border border-border bg-surface p-6 sm:p-8 space-y-6 max-w-3xl">
        {{-- <div class="p-4 rounded-lg bg-emerald-950/20 border border-emerald-500/20 text-xs text-slate-300 leading-relaxed">
            <p class="font-semibold text-emerald-400 mb-1 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Panduan Jabatan untuk Dinas:
            </p>
            <p>Struktur pelaksana manajemen risiko di tingkat instansi umumnya diisi oleh perangkat daerah yang bertugas dalam pengambilan keputusan, koordinasi, dan pelayanan operasional.</p>
        </div> --}}

        {{-- Pemilik Risiko --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-semibold text-text">1. Pemilik Risiko</label>
                {{-- <span class="text-xs px-2 py-0.5 rounded bg-surface text-primary font-medium">Umumnya: Kepala Dinas / Instansi</span> --}}
            </div>
            <p class="text-xs text-muted mb-2 leading-relaxed">
                Diisi dengan nama dan jabatan pimpinan unit kerja pemilik risiko.
            </p>
            <input wire:model="pemilik_risiko" type="text" {{ !$isEditable ? 'disabled' : '' }}
                placeholder="Contoh: Kepala Dinas Kominfo (H. Ahmad Fauzi)"
                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed">
        </div>

        {{-- Koordinator Risiko --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-semibold text-text">2. Koordinator Risiko</label>
                {{-- <span class="text-xs px-2 py-0.5 rounded bg-surface text-warning font-medium">Umumnya: Sekretaris Dinas / Instansi</span> --}}
            </div>
            <p class="text-xs text-muted mb-2 leading-relaxed">
                Diisi dengan nama dan jabatan pegawai yang mengoordinasikan proses manajemen risiko antar unit kerja. Koordinator bertugas menyusun jadwal, mengkonsolidasikan daftar risiko dari unit kerja, dan memastikan dokumen risiko terintegrasi dalam dokumen perencanaan (RPJMDes/RPJMD, Renstra, RKPDes/RKPD).
            </p>
            <input wire:model="koordinator_risiko" type="text" {{ !$isEditable ? 'disabled' : '' }}
                placeholder="Contoh: Sekretaris Dinas (Budi Santoso, S.AP)"
                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed">
        </div>

        {{-- Pengelola Risiko --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-semibold text-text">3. Pengelola Risiko</label>
                {{-- <span class="text-xs px-2 py-0.5 rounded bg-surface text-info font-medium">Umumnya: Kepala Bidang / Tim Teknis</span> --}}
            </div>
            <p class="text-xs text-muted mb-2 leading-relaxed">
                Diisi dengan nama dan jabatan tim teknis yang sehari‑hari melaksanakan identifikasi, analisis, pemantauan, dan pelaporan risiko. Pengelola menyiapkan <em>risk register</em>, memutakhirkan profil risiko, dan menyusun laporan pengendalian risiko untuk disampaikan kepada Pemilik Risiko dan Koordinator Risiko.
            </p>
            <x-textarea-auto wire:model="pengelola_risiko" rows="4" :disabled="!$isEditable"
                placeholder="Contoh:
1. Kepala Bidang Pelayanan (Siti Aminah) - Layanan Administrasi Kependudukan
2. Kasubag Keuangan (Rudi Hartono) - Pengelolaan Keuangan & APBD
3. Operator IT / SPBE Dinas (Danang) - Pengelolaan Web & Sistem Online" />
        </div>
    </div>
</div>
