{{-- Komponen Modal Konfirmasi Global --}}
{{-- Dipasang sekali di app.blade.php, dipakai ulang via event `confirm-action` dari semua komponen Livewire --}}
<div
    x-data="{
        show: false,
        wireId: null,
        action: null,
        params: [],
        title: 'Konfirmasi',
        message: '',
        subMessage: '',
        confirmText: 'Ya, Lanjutkan',
        type: 'danger',
        loading: false,
        open(detail) {
            this.wireId      = detail.wireId;
            this.action      = detail.action;
            this.params      = detail.params ?? [];
            this.title       = detail.title ?? 'Konfirmasi';
            this.message     = detail.message ?? '';
            this.subMessage  = detail.subMessage ?? '';
            this.confirmText = detail.confirmText ?? 'Ya, Lanjutkan';
            this.type        = detail.type ?? 'danger';
            this.show = true;
            $nextTick(() => this.$refs.cancelBtn?.focus());
        },
        close() {
            if (this.loading) return;
            this.show = false;
        },
        async confirm() {
            if (!this.wireId || !this.action || this.loading) return;
            this.loading = true;
            try {
                await Livewire.find(this.wireId).call(this.action, ...this.params);
            } finally {
                this.loading = false;
                this.show = false;
            }
        }
    }"
    x-on:confirm-action.window="open($event.detail)"
    x-on:keydown.escape.window="close()"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-[60] flex items-center justify-center p-4"
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

    {{-- Panel --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-surface border border-border rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4"
        role="alertdialog"
        aria-modal="true"
        :aria-label="title"
    >
        {{-- Header: Ikon + Judul --}}
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                :class="type === 'danger' ? 'bg-danger-bg text-danger' : 'bg-info-bg text-info'"
            >
                {{-- Ikon Hapus (danger) --}}
                <svg x-show="type === 'danger'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{-- Ikon Pulihkan (info) --}}
                <svg x-show="type === 'info'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-text-strong" x-text="title"></h3>
        </div>

        {{-- Pesan Utama --}}
        <p class="text-sm text-text" x-text="message"></p>

        {{-- Sub-Pesan (opsional) --}}
        <p class="text-xs text-muted" x-show="subMessage" x-text="subMessage"></p>

        {{-- Tombol Aksi --}}
        <div class="flex justify-end gap-3 pt-2">
            <button
                x-ref="cancelBtn"
                type="button"
                @click="close()"
                :disabled="loading"
                class="px-4 py-2 rounded-lg border border-border text-sm text-muted hover:bg-surface-soft disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors"
            >
                Batal
            </button>
            <button
                type="button"
                @click="confirm()"
                :disabled="loading"
                class="px-4 py-2 rounded-lg text-sm font-semibold text-white disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer transition-colors"
                :class="type === 'danger' ? 'bg-danger hover:opacity-90' : 'bg-primary hover:bg-primary-dark'"
            >
                <span x-show="!loading" x-text="confirmText"></span>
                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </div>
</div>
