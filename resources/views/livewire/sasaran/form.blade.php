<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Sasaran UPR — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">Formulir 2: Sasaran, indikator, dan target kinerja</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('konteks.form', $konteks) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Konteks
            </a>
            <a href="{{ route('struktur.form', $konteks) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                Struktur Pelaksana
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </div>


    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-success-bg border border-success/30 text-success text-sm">
            {{ session('success') }}
        </div>
    @endif    <p class="text-sm text-muted mb-5 max-w-2xl">
        Satu <span class="text-text-strong font-medium">Sasaran Pembangunan Nasional</span> mendukung pencapaian target kinerja
        <span class="text-text-strong font-medium">Sasaran UPR</span>, dan satu Sasaran UPR dapat memiliki lebih dari satu
        pasang Indikator &amp; Target Kinerja.
    </p>

    <div class="space-y-6">
        @forelse($blocks as $i => $block)
            <div class="rounded-xl border border-border bg-surface p-6" wire:key="block-{{ $block['id'] }}">

                <div class="flex items-center justify-between gap-2 mb-5 pb-3 border-b border-border">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-primary text-xs font-bold shrink-0">{{ $i + 1 }}</span>
                        <span class="text-sm font-bold text-text-strong tracking-wide">Sasaran UPR / Desa
                            Ke-{{ $i + 1 }}</span>
                    </div>
                    @if ($isEditable)
                        <button wire:click="removeBlock({{ $i }})"
                            wire:confirm="Hapus seluruh Sasaran UPR ini beserta indikatornya?"
                            class="text-xs text-danger hover:opacity-80 hover:underline cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Sasaran
                        </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    {{-- Kolom Sasaran UPR Desa --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            1. Sasaran UPR (Sasaran Desa) <span class="text-danger">*</span>
                        </label>
                        <p class="text-xs text-muted mb-2">
                            Diisi dengan sasaran UPR yang mendukung sasaran pembangunan nasional.
                        </p>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.sasaran_upr" rows="4"
                            :disabled="!$isEditable"
                            placeholder="Contoh: Meningkatnya kecepatan dan kemudahan warga dalam pengurusan surat pelayanan administrasi desa secara online." />
                    </div>

                    {{-- Kolom Sasaran Nasional --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            2. Sasaran Pembangunan Nasional
                        </label>
                        <p class="text-xs text-muted mb-2">
                            Diisi dengan sasaran pembangunan nasional yang menjadi target kinerja UPR berdasarkan dokumen perencanaan.
                        </p>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.sasaran_nasional" rows="4"
                            :disabled="!$isEditable"
                            placeholder="Contoh: Terwujudnya transformasi digital pelayanan publik dan tata kelola desa yang akuntabel." />
                    </div>
                </div>

                {{-- Indikator & Target Kinerja --}}
                <div class="border-t border-border pt-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs font-semibold text-text uppercase tracking-wider">3. Indikator &amp;
                                Target Kinerja</p>
                            <p class="text-xs text-muted">Pengukuran deskripsi capaian dan ukuran target indikator
                                kinerja.</p>
                        </div>
                    </div>

                    <div class="space-y-3 bg-surface-soft p-4 rounded-xl border border-border">
                        @foreach ($block['indikator'] as $j => $ind)
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start"
                                wire:key="ind-{{ $ind['id'] }}">
                                <div class="sm:col-span-7">
                                    <label class="block text-[11px] font-medium text-text mb-0.5">Indikator
                                        Kinerja</label>
                                    <p class="text-[10px] text-muted mb-1">Diisi dengan indikator kinerja yang
                                        mendeskripsikan pencapaian sasaran.</p>
                                    <x-textarea-auto
                                        wire:model="blocks.{{ $i }}.indikator.{{ $j }}.indikator_kinerja"
                                        rows="2" :disabled="!$isEditable"
                                        placeholder="Contoh: Persentase permohonan surat warga yang selesai dalam < 1 hari" />
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-medium text-text mb-0.5">Target
                                        Kinerja</label>
                                    <p class="text-[10px] text-muted mb-1">Diisi dengan target kinerja yang
                                        mendeskripsikan ukuran indikator kinerja.</p>
                                    <x-textarea-auto
                                        wire:model="blocks.{{ $i }}.indikator.{{ $j }}.target_kinerja"
                                        rows="2" :disabled="!$isEditable" placeholder="Contoh: Minimal 90%" />
                                </div>
                                <div class="sm:col-span-1 pt-6 flex justify-center">
                                    @if ($isEditable && count($block['indikator']) > 1)
                                        <button type="button"
                                            wire:click="removeIndikator({{ $i }}, {{ $j }})"
                                            wire:confirm="Hapus indikator ini?"
                                            class="p-2 rounded-lg text-danger hover:bg-danger-bg transition-colors cursor-pointer"
                                            title="Hapus indikator">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if ($isEditable)
                            <div class="pt-2">
                                <button type="button" wire:click="addIndikator({{ $i }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-dashed border-border bg-surface text-xs text-accent hover:bg-surface-soft transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Indikator &amp; Target Lain
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($isEditable)
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-border">
                        <button type="button" wire:click="saveBlock({{ $i }})"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Sasaran Ini
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div
                class="rounded-xl border border-dashed border-border px-4 py-12 text-center text-muted text-sm bg-surface-soft">
                <p class="font-medium text-text mb-1">Belum ada Sasaran UPR yang ditambahkan.</p>
                <p class="text-xs text-muted">Klik tombol di bawah untuk membuat sasaran desa pertama.</p>
            </div>
        @endforelse
    </div>

    @if ($isEditable)
        <button wire:click="addBlock"
            class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-dashed border-border text-sm text-muted hover:text-text hover:border-border-strong transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            + Tambah Sasaran UPR
        </button>
    @endif
</div>
