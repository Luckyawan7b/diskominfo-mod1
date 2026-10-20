<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong flex items-center gap-2">
                Daftar Konteks Perubahan
                @if($activeLayanan)
                    <span class="text-muted font-medium text-lg border-l border-border pl-2 ml-1">{{ $activeLayanan->nama_layanan }}</span>
                @endif
            </h1>
            <p class="text-sm text-muted mt-1">Kelola permohonan dan perencanaan perubahan per tahun penilaian</p>
        </div>

        @if(auth()->user()->isOperator())
            <button wire:click="$set('showCreateModal', true)" id="mpr-buat-konteks"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Konteks Baru
            </button>
        @endif
    </div>

    @if(auth()->user()->isAdmin())
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat daftar konteks ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    {{-- Tabel Konteks --}}
    <div class="rounded-xl border border-border bg-surface overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Tahun Penilaian</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Periode Perencanaan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Layanan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Instansi</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Perubahan</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($konteks as $item)
                    <tr class="hover:bg-surface-soft transition-colors">
                        <td class="px-5 py-4 text-text-strong font-semibold">{{ $item->tahun_penilaian }}</td>
                        <td class="px-5 py-4 text-text-strong">{{ $item->periode_perencanaan ?? '-' }}</td>
                        <td class="px-5 py-4 text-text font-medium">{{ $item->layanan->nama_layanan ?? '-' }}</td>
                        <td class="px-5 py-4 text-text">{{ $item->nama_instansi ?: ($item->layanan?->creator?->nama_dinas ?? '-') }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-surface-soft text-text text-sm font-medium">
                                {{ $item->perubahan_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('konteks-mpr.form', $item) }}"
                               class="inline-flex items-center gap-1 text-accent hover:text-primary text-sm font-medium transition-colors">
                                {{ $item->isEditableByOperator() && auth()->user()->isOperator() ? 'Edit' : 'Lihat' }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <p class="text-muted text-sm">Belum ada konteks manajemen perubahan</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Buat Konteks Baru --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-init="$el.querySelector('input')?.focus()">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="relative bg-surface border border-border rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-text-strong mb-4">Buat Konteks MPR Baru</h3>
                <form wire:submit="createKonteks">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-text mb-1.5">Tahun Penilaian <span class="text-danger">*</span></label>
                            <input wire:model="newTahun" type="number" min="2020" max="2099" id="mpr-new-tahun"
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                            @error('newTahun')
                                <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-text mb-1.5">Periode Perencanaan</label>
                            <input wire:model="newPeriode" type="text" maxlength="100" id="mpr-new-periode" placeholder="Contoh: 2026–2027"
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                            @error('newPeriode')
                                <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm text-muted hover:bg-surface-soft transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-primary hover:bg-primary-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                            Buat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
