<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Daftar Perubahan — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">Permohonan perubahan (F1) beserta kelengkapan formulir wajib</p>
        </div>
        @if($isEditable)
            <a href="{{ route('mpr.perubahan.create', $konteks) }}" id="mpr-buat-perubahan"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajukan Perubahan
            </a>
        @endif
    </div>

    {{-- Toast Notification (Melayang di kanan atas) --}}
    <div
        x-data="{
            show: false,
            message: '',
            title: 'Berhasil',
            timeout: null,
            init() {
                @if(session('success'))
                    this.notify(@js(session('success')));
                @endif
            },
            notify(msg, customTitle = 'Berhasil') {
                this.message = msg;
                this.title = customTitle;
                this.show = true;
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => { this.show = false; }, 4000);
            }
        }"
        x-on:perubahan-deleted.window="notify($event.detail.message, 'Berhasil Dihapus')"
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
        id="toast-perubahan-index"
    >
        <div class="w-8 h-8 rounded-full bg-emerald-500/15 text-emerald-500 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-sm font-semibold text-text-strong" x-text="title">Berhasil</p>
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
            Anda melihat daftar ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    {{-- Filter --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID atau layanan digital..."
            class="flex-1 min-w-[250px] rounded-lg border border-border bg-field text-sm text-text px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent">
        <select wire:model.live="filterKlasifikasi" class="rounded-lg border border-border bg-field text-sm text-text px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent">
            <option value="">Semua Klasifikasi</option>
            @foreach($klasifikasiRef as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterLingkup" class="rounded-lg border border-border bg-field text-sm text-text px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent">
            <option value="">Semua Lingkup</option>
            @foreach($lingkupRef as $l)
                <option value="{{ $l->id }}">{{ $l->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="rounded-xl border border-border bg-surface overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">ID</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Layanan Digital</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Klasifikasi</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Lingkup</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Kelengkapan</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($perubahanList as $p)
                    <tr class="hover:bg-surface-soft transition-colors" wire:key="perubahan-{{ $p->id }}">
                        <td class="px-5 py-4 text-text-strong font-mono text-xs">{{ $p->id_perubahan }}</td>
                        <td class="px-5 py-4 text-text font-medium">{{ $p->nama_layanan_digital }}</td>
                        <td class="px-5 py-4 text-text">{{ $p->klasifikasi?->nama ?? '-' }}</td>
                        <td class="px-5 py-4 text-text">{{ $p->lingkup?->nama ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            @php $k = $p->kelengkapan; $lengkap = $k['wajib'] > 0 && $k['terisi'] === $k['wajib']; @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $lengkap ? 'bg-success-bg text-success border-success/20' : 'bg-surface-soft text-text border-border' }}">
                                {{ $k['terisi'] }}/{{ $k['wajib'] }} formulir
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('mpr.perubahan.hub', [$konteks, $p]) }}" class="text-accent hover:text-primary text-sm font-medium transition-colors">Hub</a>
                                <a href="{{ route('mpr.perubahan.form', [$konteks, $p]) }}" class="text-accent hover:text-primary text-sm font-medium transition-colors">{{ $isEditable ? 'Edit' : 'Lihat' }}</a>
                                @if($isEditable)
                                    <button type="button"
                                        @click="$dispatch('confirm-action', {
                                            wireId: $wire.id,
                                            action: 'hapus',
                                            params: [{{ $p->id }}],
                                            title: 'Hapus Perubahan',
                                            message: 'Hapus perubahan {{ $p->id_perubahan }}?',
                                            subMessage: 'Data dipindahkan ke Tempat Sampah dan dapat dipulihkan admin.',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger'
                                        })"
                                        class="text-danger hover:opacity-80 text-sm font-medium cursor-pointer">Hapus</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-muted text-sm">Belum ada permohonan perubahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
