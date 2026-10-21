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

    {{-- Floating Toast Notification (Melayang di kanan atas) --}}
    <div
        x-data="{
            show: false,
            message: '',
            title: 'Berhasil Disimpan',
            timeout: null,
            init() {
                @if(session('success'))
                    this.notify(@js(session('success')));
                @endif
            },
            notify(msg, customTitle = 'Berhasil Disimpan') {
                this.message = msg;
                this.title = customTitle;
                this.show = true;
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => { this.show = false; }, 4000);
            }
        }"
        x-on:analisis-saved.window="notify($event.detail.message, 'Berhasil Disimpan')"
        x-on:analisis-deleted.window="notify($event.detail.message, 'Berhasil Dihapus')"
        x-on:notify.window="notify($event.detail.message, $event.detail.title ?? 'Notifikasi')"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-4"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
        x-transition:leave-end="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-4"
        class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full bg-surface border border-emerald-500/30 rounded-xl shadow-xl p-4 flex items-start gap-3 backdrop-blur-md"
        style="display: none;"
        role="status"
        aria-live="polite"
    >
        <div class="w-8 h-8 rounded-full bg-emerald-500/15 text-emerald-500 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-sm font-semibold text-text-strong" x-text="title">Berhasil Disimpan</p>
            <p class="text-xs text-muted mt-0.5 break-words" x-text="message">
                @if(session('success'))
                    {{ session('success') }}
                @endif
            </p>
        </div>
        <button type="button" @click="show = false" class="text-muted hover:text-text p-1 rounded-md transition-colors shrink-0" title="Tutup">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
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
                    <div class="flex items-center justify-between gap-3 mt-6 pt-4 border-t border-border">
                        <div>
                            @if($lastSavedIndex === $i && session('success'))
                                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Tersimpan
                                </span>
                            @endif
                        </div>
                        <button type="button" wire:click="saveBlock({{ $i }})"
                            wire:loading.attr="disabled"
                            wire:target="saveBlock({{ $i }})"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark disabled:opacity-50 text-sm font-semibold text-white shadow transition-all cursor-pointer">
                            <span wire:loading.remove wire:target="saveBlock({{ $i }})" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Simpan Analisis Ini
                            </span>
                            <span wire:loading.inline-flex wire:target="saveBlock({{ $i }})" class="items-center gap-2">
                                <svg class="w-4 h-4 animate-spin text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span>Menyimpan...</span>
                            </span>
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
