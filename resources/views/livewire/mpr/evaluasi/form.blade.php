<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Evaluasi Perubahan (F5)</h1>
            <p class="text-sm text-muted mt-1">MPR04: ringkasan capaian indikator keberhasilan, hasil akhir perubahan, lesson learned, dan rekomendasi solusi</p>
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
        x-on:evaluasi-saved.window="notify($event.detail.message, 'Berhasil Disimpan')"
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
        id="toast-evaluasi"
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

    @if($isAdmin)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat data ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    {{-- Ringkasan Indikator Keberhasilan dari F3 --}}
    <div class="mb-6 rounded-xl border border-border bg-surface overflow-hidden" id="ringkasan-indikator-f3">
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-border">
            <div>
                <h2 class="text-base font-semibold text-text-strong">Ringkasan Target & Realisasi Indikator Keberhasilan (F3)</h2>
                <p class="text-xs text-muted mt-0.5">Diambil dari perencanaan Indikator Keberhasilan pada Formulir 3.</p>
            </div>
            <a href="{{ route('mpr.perencanaan.form', [$konteks, $perubahan, 'tab' => 'indikator_keberhasilan']) }}"
                class="text-xs font-medium text-accent hover:text-primary transition-colors shrink-0">
                {{ $isEditable ? 'Kelola di F3' : 'Lihat di F3' }} &rarr;
            </a>
        </div>

        @if($indikatorList->isNotEmpty())
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-soft">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">No</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Indikator Keberhasilan</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Satuan</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Target</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($indikatorList as $ik)
                        <tr>
                            <td class="px-5 py-3 text-muted text-xs">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3 text-text font-medium">{{ $ik->indikator }}</td>
                            <td class="px-5 py-3 text-text">{{ $ik->satuan ?: '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono text-text">{{ $ik->target !== null ? rtrim(rtrim((string) $ik->target, '0'), '.') : '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono text-text-strong font-semibold">{{ $ik->realisasi !== null ? rtrim(rtrim((string) $ik->realisasi, '0'), '.') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-5 py-8 text-center text-xs text-muted">
                Belum ada indikator keberhasilan yang diisi pada F3 Perencanaan.
            </div>
        @endif
    </div>

    <form wire:submit="save" class="rounded-xl border border-border bg-surface p-6 space-y-5">
        <div class="max-w-sm">
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Hasil Evaluasi Perubahan</label>
            <select wire:model="hasil" id="hasil" @disabled(!$isEditable)
                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                <option value="">— Pilih Hasil —</option>
                <option value="Berhasil">Berhasil</option>
                <option value="Tidak Berhasil">Tidak Berhasil</option>
            </select>
            @error('hasil') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Lesson Learned (Pembelajaran)</label>
            <x-textarea-auto wire:model="lesson_learned" rows="4" :disabled="!$isEditable" />
            @error('lesson_learned') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Rekomendasi / Solusi Tindak Lanjut</label>
            <x-textarea-auto wire:model="rekomendasi_solusi" rows="4" :disabled="!$isEditable" />
            @error('rekomendasi_solusi') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        @if($isEditable)
            <div class="flex justify-end pt-4 border-t border-border">
                <button type="submit" id="simpan-evaluasi"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark disabled:opacity-50 text-sm font-semibold text-white shadow transition-all cursor-pointer">
                    <span wire:loading.remove wire:target="save">
                        Simpan Evaluasi
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
