<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Persetujuan Perubahan</h1>
            <p class="text-sm text-muted mt-1">Pencatatan keputusan persetujuan, PIC penyetuju, tanggal, dan catatan per tahap</p>
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
        x-on:persetujuan-saved.window="notify($event.detail.message, 'Berhasil Disimpan')"
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
        id="toast-persetujuan"
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
            Formulir Persetujuan <strong>tidak wajib</strong> untuk klasifikasi ({{ $perubahan->klasifikasi?->nama ?? '-' }}) dan lingkup ({{ $perubahan->lingkup?->nama ?? '-' }}) perubahan ini. Halaman ditampilkan dalam mode nonaktif agar alurnya tetap jelas.
        </div>
    @elseif($isAdmin)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat data ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        @foreach($tahapMeta as $tahap => $judul)
            @php
                $relevan = $tahapRelevan[$tahap] ?? false;
                $dapatDiisi = $isEditable && $relevan;
            @endphp
            <div class="rounded-xl border border-border bg-surface p-6 {{ ! $relevan ? 'opacity-60' : '' }}"
                 wire:key="persetujuan-{{ $tahap }}"
                 data-tahap="{{ $tahap }}">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4 pb-3 border-b border-border">
                    <h2 class="text-base font-bold text-text-strong">{{ $judul }}</h2>
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold {{ $relevan ? 'bg-primary/20 text-primary' : 'bg-surface-soft text-muted' }}">
                        {{ $relevan ? 'Wajib' : 'Tidak wajib untuk perubahan ini' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Status Persetujuan</label>
                        <select wire:model="items.{{ $tahap }}.status" id="status-{{ $tahap }}" @disabled(!$dapatDiisi)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            <option value="">— Pilih Status —</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Tidak Disetujui">Tidak Disetujui</option>
                        </select>
                        @error("items.$tahap.status")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">PIC Penyetuju</label>
                        <input type="text" wire:model="items.{{ $tahap }}.pic_penyetuju" maxlength="255"
                            id="pic-{{ $tahap }}" @disabled(!$dapatDiisi)
                            placeholder="Nama / Jabatan penyetuju"
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        @error("items.$tahap.pic_penyetuju")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Persetujuan</label>
                        <input type="date" wire:model="items.{{ $tahap }}.tanggal"
                            id="tanggal-{{ $tahap }}" @disabled(!$dapatDiisi)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        @error("items.$tahap.tanggal")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Catatan</label>
                    <x-textarea-auto wire:model="items.{{ $tahap }}.catatan" rows="2" :disabled="!$dapatDiisi" />
                    @error("items.$tahap.catatan")
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endforeach

        @if($isEditable)
            <div class="flex justify-end pt-4 border-t border-border">
                <button type="submit" id="simpan-persetujuan"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark disabled:opacity-50 text-sm font-semibold text-white shadow transition-all cursor-pointer">
                    <span wire:loading.remove wire:target="save">
                        Simpan Persetujuan
                    </span>
                    <span wire:loading.inline-flex wire:target="save" class="items-center gap-2">
                        <svg class="w-4 h-4 animate-spin text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>
            </div>
        @endif
    </form>
</div>
