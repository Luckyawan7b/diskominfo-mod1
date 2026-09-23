<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Indikator Capaian — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">
                Formulir 1 (Tabel 1a) & Formulir 4: Indikator capaian beserta evaluasi realisasinya
            </p>
        </div>
        <a href="{{ route('konteks-mpn.form', $konteks) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Hub Konteks
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-success/10 border border-success/30 text-success text-sm">
            {{ session('success') }}
        </div>
    @endif

    <p class="text-sm text-muted mb-5 max-w-2xl">
        Setiap <span class="text-text-strong font-medium">Indikator Capaian</span> merekam kondisi <em>as-is</em> dan target
        <em>to-be</em>. Bagian <span class="text-text-strong font-medium">Evaluasi</span> di tiap indikator mencatat
        realisasi, analisis, dan tindak lanjut — serta menghitung <em>gap</em> secara otomatis.
    </p>

    {{-- Daftar Blok Indikator --}}
    <div class="space-y-6">
        @forelse($blocks as $i => $block)
            <div class="rounded-xl border border-border bg-surface p-6" wire:key="indikator-block-{{ $block['id'] }}">

                {{-- Header Blok --}}
                <div class="flex items-center justify-between gap-2 mb-5 pb-3 border-b border-border">
                    <div class="flex items-center gap-2.5">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-primary text-xs font-bold shrink-0">{{ $i + 1 }}</span>
                        <span class="text-sm font-bold text-text-strong tracking-wide">Indikator Capaian Ke-{{ $i + 1 }}</span>
                    </div>
                    @if($isEditable)
                        <button
                            @click="$dispatch('confirm-action', {
                                wireId: $wire.id,
                                action: 'removeBlock',
                                params: [{{ $i }}],
                                title: 'Hapus Indikator Capaian',
                                message: 'Hapus Indikator Capaian Ke-{{ $i + 1 }} beserta data evaluasinya?',
                                subMessage: 'Tindakan ini tidak bisa dibatalkan.',
                                confirmText: 'Ya, Hapus',
                                type: 'danger'
                            })"
                            class="text-xs text-danger hover:opacity-80 hover:underline cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    @endif
                </div>

                {{-- Seksi A: Definisi Indikator --}}
                <p class="text-[11px] font-semibold text-primary uppercase tracking-wider mb-2">Formulir 1 — Tabel 1a: Indikator Capaian</p>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    {{-- Deskripsi Indikator --}}
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Deskripsi Indikator <span class="text-danger">*</span>
                        </label>
                        <p class="text-xs text-muted mb-2">Tuliskan indikator capaian layanan yang akan diukur.</p>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.indikator" rows="2"
                            :disabled="!$isEditable"
                            placeholder="Contoh: Persentase dokumen pengetahuan layanan yang berhasil dikumpulkan dan didistribusikan"/>
                        @error("blocks.{$i}.indikator")
                            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kondisi As-Is --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Kondisi <em>As-Is</em> (Saat Ini)
                        </label>
                        <p class="text-xs text-muted mb-2">Nilai/kondisi yang ada saat ini (angka atau deskripsi).</p>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.kondisi_as_is" rows="2"
                            :disabled="!$isEditable"
                            placeholder="Contoh: 40% atau 'Belum ada sistem dokumentasi'"/>
                    </div>

                    {{-- Kondisi To-Be --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Kondisi <em>To-Be</em> (Target)
                        </label>
                        <p class="text-xs text-muted mb-2">Target yang ingin dicapai (angka numerik untuk hitung gap).</p>
                        <input type="number" step="any" wire:model="blocks.{{ $i }}.kondisi_to_be"
                            {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed"
                            placeholder="Contoh: 80">
                    </div>

                    {{-- Gap Indicator --}}
                    <div class="flex flex-col justify-end">
                        <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-1">Gap (Realisasi − Target)</label>
                        @php
                            $gap = $block['evaluasi']['gap'] ?? null;
                        @endphp
                        @if($gap !== null)
                            <div class="flex items-center gap-2 mt-1">
                                @if($gap >= 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-success/10 border border-success/30 text-success text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        +{{ number_format($gap, 2) }}
                                    </span>
                                    <span class="text-xs text-muted">Tercapai</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-danger/10 border border-danger/30 text-danger text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                        {{ number_format($gap, 2) }}
                                    </span>
                                    <span class="text-xs text-muted">Di Bawah Target</span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-muted italic mt-1">Isi realisasi & target numerik untuk menghitung gap</span>
                        @endif
                    </div>
                </div>

                {{-- Seksi B: Evaluasi Indikator --}}
                <div class="border-t border-border pt-5">
                    <p class="text-xs font-semibold text-text uppercase tracking-wider mb-4">Formulir 4 — Evaluasi Realisasi</p>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Realisasi --}}
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Nilai Realisasi</label>
                            <p class="text-xs text-muted mb-2">Angka realisasi aktual yang dicapai. Dipakai menghitung gap terhadap <em>to-be</em>.</p>
                            <input type="number" step="any" wire:model="blocks.{{ $i }}.evaluasi.realisasi"
                                {{ !$isEditable ? 'disabled' : '' }}
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed"
                                placeholder="Contoh: 65">
                        </div>

                        {{-- Pelaksana Terkait --}}
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Pelaksana Terkait</label>
                            <p class="text-xs text-muted mb-2">Unit/tim yang bertanggung jawab atas capaian indikator ini.</p>
                            <input type="text" wire:model="blocks.{{ $i }}.evaluasi.pelaksana_terkait"
                                {{ !$isEditable ? 'disabled' : '' }}
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed"
                                placeholder="Contoh: Bidang Informasi dan Komunikasi">
                        </div>

                        {{-- Analisis --}}
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Analisis Capaian</label>
                            <p class="text-xs text-muted mb-2">Penjelasan mengapa capaian bisa lebih tinggi/rendah dari target.</p>
                            <x-textarea-auto wire:model="blocks.{{ $i }}.evaluasi.analisis" rows="3"
                                :disabled="!$isEditable"
                                placeholder="Tuliskan analisis capaian indikator..."/>
                        </div>

                        {{-- Tindak Lanjut --}}
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tindak Lanjut</label>
                            <p class="text-xs text-muted mb-2">Langkah perbaikan atau akselerasi yang direncanakan.</p>
                            <x-textarea-auto wire:model="blocks.{{ $i }}.evaluasi.tindak_lanjut" rows="3"
                                :disabled="!$isEditable"
                                placeholder="Tuliskan rencana tindak lanjut..."/>
                        </div>
                    </div>
                </div>

                @if($isEditable)
                    <div class="flex justify-end mt-6 pt-4 border-t border-border">
                        <button type="button" wire:click="saveBlock({{ $i }})"
                            wire:loading.attr="disabled" wire:target="saveBlock({{ $i }})"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer disabled:opacity-60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span wire:loading.remove wire:target="saveBlock({{ $i }})">Simpan Indikator Ini</span>
                            <span wire:loading wire:target="saveBlock({{ $i }})">Menyimpan...</span>
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-border px-4 py-12 text-center text-muted text-sm bg-surface-soft">
                <svg class="w-12 h-12 text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="font-medium text-text mb-1">Belum ada Indikator Capaian yang ditambahkan.</p>
                <p class="text-xs text-muted">Klik tombol di bawah untuk membuat indikator pertama.</p>
            </div>
        @endforelse
    </div>

    @if($isEditable)
        <button wire:click="addBlock"
            class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-dashed border-border text-sm text-muted hover:text-text hover:border-border-strong transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Indikator Capaian
        </button>
    @endif
</div>
