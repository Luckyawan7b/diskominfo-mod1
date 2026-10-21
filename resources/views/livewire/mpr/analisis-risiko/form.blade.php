<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Analisis Risiko Perubahan (F2A)</h1>
            <p class="text-sm text-muted mt-1">MPR02A: identifikasi risiko akibat perubahan, besaran risiko (skala 3 level MPR), dan perencanaan keberlangsungan</p>
        </div>
        <a href="{{ route('mpr.perubahan.hub', [$konteks, $perubahan]) }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
            Hub Perubahan
        </a>
    </div>

    {{-- Toast Notification (Melayang di kanan atas) --}}
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
        x-on:risiko-mpr-saved.window="notify($event.detail.message, 'Berhasil Disimpan')"
        x-on:risiko-mpr-deleted.window="notify($event.detail.message, 'Berhasil Dihapus')"
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
        id="toast-analisis-risiko"
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

    @if(! $wajib)
        <div class="mb-6 rounded-lg bg-warning/10 border border-warning/30 px-4 py-3 text-sm text-text" id="banner-tidak-wajib">
            Formulir Analisis Risiko (2A) <strong>tidak wajib</strong> untuk klasifikasi ({{ $perubahan->klasifikasi?->nama ?? '-' }}) dan lingkup ({{ $perubahan->lingkup?->nama ?? '-' }}) perubahan ini. Halaman ditampilkan dalam mode nonaktif agar alurnya tetap jelas.
        </div>
    @elseif($isAdmin)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat data ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    <div class="space-y-6">
        @forelse($blocks as $i => $block)
            <div class="rounded-xl border border-border bg-surface p-6" wire:key="risiko-mpr-{{ $block['id'] ?? 'new-'.$i }}">
                <div class="flex items-center justify-between gap-2 mb-5 pb-3 border-b border-border">
                    <div class="flex items-center gap-2.5">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-primary text-xs font-bold shrink-0">{{ $i + 1 }}</span>
                        <span class="text-sm font-bold text-text-strong tracking-wide">
                            Risiko Ke-{{ $i + 1 }}
                            @if(! empty($block['kode_risiko']))
                                &mdash; <span class="font-mono text-xs">{{ $block['kode_risiko'] }}</span>
                            @endif
                        </span>
                    </div>
                    @if($isEditable)
                        <button type="button"
                            @click="$dispatch('confirm-action', {
                                wireId: $wire.id,
                                action: 'removeBlock',
                                params: [{{ $i }}],
                                title: 'Hapus Analisis Risiko',
                                message: 'Hapus risiko Ke-{{ $i + 1 }} ini?',
                                subMessage: 'Data yang sudah tersimpan dipindahkan ke Tempat Sampah.',
                                confirmText: 'Ya, Hapus',
                                type: 'danger'
                            })"
                            class="text-xs text-danger hover:opacity-80 hover:underline cursor-pointer flex items-center gap-1">
                            Hapus
                        </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Kode Risiko <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="blocks.{{ $i }}.kode_risiko" maxlength="100"
                            id="kode-risiko-{{ $i }}" @disabled(!$isEditable)
                            placeholder="Contoh: R-PR-001"
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text font-mono text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        @error("blocks.$i.kode_risiko")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Sumber Risiko <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="blocks.{{ $i }}.sumber_risiko" id="sumber-risiko-{{ $i }}" @disabled(!$isEditable)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            <option value="baru">Risiko Baru (Akibat Perubahan)</option>
                            <option value="dari_mr">Dari Manajemen Risiko (MR)</option>
                        </select>
                        @error("blocks.$i.sumber_risiko")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if(($block['sumber_risiko'] ?? 'baru') === 'dari_mr')
                    <div class="mb-5 p-4 rounded-lg border border-border bg-surface-soft">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Pilih Risiko dari Manajemen Risiko (Layanan Sama) <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="blocks.{{ $i }}.mr_risiko_id" id="mr-risiko-{{ $i }}" @disabled(!$isEditable)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            <option value="">— Pilih Risiko MR —</option>
                            @foreach($mrRisikoList as $mr)
                                <option value="{{ $mr->id }}">
                                    {{ $mr->kode_risiko }} ({{ $mr->konteks?->tahun_penilaian }}) — {{ \Illuminate\Support\Str::limit($mr->peristiwa_risiko, 70) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-muted mt-1">Memilih risiko MR akan mengisi otomatis pernyataan risiko, penyebab, dan level kemungkinan/dampak. Kode risiko MPR tetap harus unik.</p>
                        @error("blocks.$i.mr_risiko_id")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Risiko <span class="text-danger">*</span>
                        </label>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.risiko" rows="3" :disabled="!$isEditable" />
                        @error("blocks.$i.risiko")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Penyebab</label>
                        <x-textarea-auto wire:model="blocks.{{ $i }}.penyebab" rows="3" :disabled="!$isEditable" />
                        @error("blocks.$i.penyebab")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Level Kemungkinan (1–5)</label>
                        <select wire:model.live="blocks.{{ $i }}.level_kemungkinan" id="level-kemungkinan-{{ $i }}" @disabled(!$isEditable)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            <option value="">— Pilih —</option>
                            <option value="1">1 — Hampir Tidak Terjadi</option>
                            <option value="2">2 — Jarang Terjadi</option>
                            <option value="3">3 — Kadang Terjadi</option>
                            <option value="4">4 — Sering Terjadi</option>
                            <option value="5">5 — Hampir Pasti</option>
                        </select>
                        @error("blocks.$i.level_kemungkinan")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Level Dampak (1–5)</label>
                        <select wire:model.live="blocks.{{ $i }}.level_dampak" id="level-dampak-{{ $i }}" @disabled(!$isEditable)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            <option value="">— Pilih —</option>
                            <option value="1">1 — Tidak Signifikan</option>
                            <option value="2">2 — Kecil / Minor</option>
                            <option value="3">3 — Sedang / Moderat</option>
                            <option value="4">4 — Besar / Signifikan</option>
                            <option value="5">5 — Sangat Besar / Katastropik</option>
                        </select>
                        @error("blocks.$i.level_dampak")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Besaran & Level Risiko (MPR)</label>
                        @php $h = $hasilPerBlok[$i] ?? ['besaran' => null, 'label' => null, 'badge' => null]; @endphp
                        <div class="rounded-lg border border-border bg-surface-soft px-4 py-2 flex items-center justify-between min-h-[42px]" data-hasil-risiko="{{ $i }}">
                            <span class="text-lg font-bold text-text-strong">{{ $h['besaran'] ?? '-' }}</span>
                            @if($h['label'])
                                <span class="px-2.5 py-0.5 rounded-full border text-xs font-semibold {{ $h['badge'] }}">
                                    {{ $h['label'] }}
                                </span>
                            @else
                                <span class="text-xs text-muted">Pilih level</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-muted mt-1">Skala 3 level MPR: 1–5 Rendah, 6–16 Sedang, &ge;17 Tinggi.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Perencanaan Keberlangsungan</label>
                    <x-textarea-auto wire:model="blocks.{{ $i }}.perencanaan_keberlangsungan" rows="2" :disabled="!$isEditable" />
                    @error("blocks.$i.perencanaan_keberlangsungan")
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                @if($isEditable)
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-border">
                        <button type="button" wire:click="saveBlock({{ $i }})" id="simpan-risiko-{{ $i }}"
                            wire:loading.attr="disabled"
                            wire:target="saveBlock({{ $i }})"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark disabled:opacity-50 text-sm font-semibold text-white shadow transition-all cursor-pointer">
                            <span wire:loading.remove wire:target="saveBlock({{ $i }})">
                                Simpan Risiko Ini
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
                <p class="font-medium text-text mb-1">Belum ada analisis risiko untuk perubahan ini.</p>
                @if($isEditable)
                    <p class="text-xs text-muted">Klik tombol di bawah untuk menambahkan risiko pertama.</p>
                @endif
            </div>
        @endforelse
    </div>

    @if($isEditable)
        <button wire:click="addBlock" id="mpr-tambah-risiko"
            class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-dashed border-border text-sm text-muted hover:text-text hover:border-border-strong transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Risiko
        </button>
    @endif
</div>
