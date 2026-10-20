<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Analisis Prioritas — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">F0: kondisi yang perlu berubah, as-is, to-be, prioritas, dan aspek/indikator PEMDI</p>
        </div>
        <a href="{{ route('konteks-mpr.form', $konteks) }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
            Hub Konteks
        </a>
    </div>

    @if(! $isEditable)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat data ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    <div class="space-y-6">
        @forelse($blocks as $i => $block)
            <div class="rounded-xl border border-border bg-surface p-6" wire:key="prioritas-{{ $block['id'] }}">

                <div class="flex items-center justify-between gap-2 mb-5 pb-3 border-b border-border">
                    <div class="flex items-center gap-2.5">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-primary text-xs font-bold shrink-0">{{ $i + 1 }}</span>
                        <span class="text-sm font-bold text-text-strong tracking-wide">Analisis Prioritas Ke-{{ $i + 1 }}</span>
                    </div>
                    @if ($isEditable)
                        <button type="button"
                            @click="$dispatch('confirm-action', {
                                wireId: $wire.id,
                                action: 'removeBlock',
                                params: [{{ $i }}],
                                title: 'Hapus Analisis Prioritas',
                                message: 'Hapus analisis prioritas Ke-{{ $i + 1 }} ini?',
                                subMessage: 'Perubahan yang terkait tidak ikut terhapus, hanya tautannya dilepas.',
                                confirmText: 'Ya, Hapus',
                                type: 'danger'
                            })"
                            class="text-xs text-danger hover:opacity-80 hover:underline cursor-pointer flex items-center gap-1">
                            Hapus
                        </button>
                    @endif
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Nama Layanan Prioritas</label>
                    <p class="text-sm text-text-strong rounded-lg border border-border bg-surface-soft px-4 py-2.5">{{ $block['nama_layanan_prioritas'] ?: '-' }}</p>
                    <p class="text-[11px] text-muted mt-1">Diisi otomatis dari layanan pada konteks ini.</p>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                        Kondisi yang Perlu Berubah <span class="text-danger">*</span>
                    </label>
                    <x-textarea-auto wire:model="blocks.{{ $i }}.kondisi_perlu_berubah" rows="3" :disabled="!$isEditable" />
                    @error("blocks.$i.kondisi_perlu_berubah")
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">As-Is (Kondisi Saat Ini)</label>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.as_is" rows="3" :disabled="!$isEditable" />
                        @error("blocks.$i.as_is")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">To-Be (Kondisi yang Diharapkan)</label>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.to_be" rows="3" :disabled="!$isEditable" />
                        @error("blocks.$i.to_be")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-5 max-w-xs">
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Prioritas</label>
                    <input type="number" min="1" max="999" wire:model="blocks.{{ $i }}.prioritas"
                        id="prioritas-{{ $i }}" @disabled(!$isEditable)
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                    <p class="text-[11px] text-muted mt-1">Diisi manual (angka lebih kecil = lebih prioritas).</p>
                    @error("blocks.$i.prioritas")
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 border-t border-border pt-5">
                    <div>
                        <p class="text-xs font-semibold text-text uppercase tracking-wider mb-2">Aspek PEMDI</p>
                        <div class="space-y-1.5 rounded-xl border border-border bg-surface-soft p-3">
                            @foreach($aspekList as $aspek)
                                <label class="flex items-start gap-2 text-sm text-text" wire:key="asp-{{ $block['id'] }}-{{ $aspek->id }}">
                                    <input type="checkbox" value="{{ $aspek->id }}"
                                        wire:model.live="blocks.{{ $i }}.aspek_ids" @disabled(!$isEditable)
                                        class="mt-0.5 rounded border-border">
                                    <span>{{ $aspek->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-text uppercase tracking-wider mb-2">Indikator PEMDI</p>
                        <div class="space-y-1.5 rounded-xl border border-border bg-surface-soft p-3">
                            @forelse($indikatorPerBlok[$i] as $ind)
                                <label class="flex items-start gap-2 text-sm text-text" wire:key="ind-{{ $block['id'] }}-{{ $ind->id }}">
                                    <input type="checkbox" value="{{ $ind->id }}"
                                        wire:model="blocks.{{ $i }}.indikator_ids" @disabled(!$isEditable)
                                        class="mt-0.5 rounded border-border">
                                    <span>{{ $ind->nama }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-muted italic">Pilih aspek terlebih dahulu untuk menampilkan indikator.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if ($isEditable)
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-border">
                        <button type="button" wire:click="saveBlock({{ $i }})"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                            Simpan Analisis Ini
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-border px-4 py-12 text-center text-muted text-sm bg-surface-soft">
                <p class="font-medium text-text mb-1">Belum ada analisis prioritas.</p>
                @if($isEditable)
                    <p class="text-xs text-muted">Klik tombol di bawah untuk menambahkan yang pertama.</p>
                @endif
            </div>
        @endforelse
    </div>

    @if ($isEditable)
        <button wire:click="addBlock" id="mpr-tambah-prioritas"
            class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-dashed border-border text-sm text-muted hover:text-text hover:border-border-strong transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Analisis Prioritas
        </button>
    @endif
</div>
