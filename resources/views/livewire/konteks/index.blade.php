<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong flex items-center gap-2">
                Daftar Konteks Risiko
                @if($activeLayanan)
                    <span class="text-muted font-medium text-lg border-l border-border pl-2 ml-1">{{ $activeLayanan->nama_layanan }}</span>
                @endif
            </h1>
            <p class="text-sm text-muted mt-1">Kelola dokumen manajemen risiko per tahun penilaian</p>
        </div>

        @if(auth()->user()->isOperator())
            <button wire:click="$set('showCreateModal', true)"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Konteks Baru
            </button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-6">
        @if(auth()->user()->isAdmin() && $desaList->isNotEmpty())
            <select wire:model.live="filterDesa" class="rounded-lg border border-border bg-field text-sm text-text px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent">
                <option value="">Semua Desa</option>
                @foreach($desaList as $desa)
                    <option value="{{ $desa->id }}">{{ $desa->nama_desa }}</option>
                @endforeach
            </select>
        @endif
        <select wire:model.live="filterStatus" class="rounded-lg border border-border bg-field text-sm text-text px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="archived">Archived</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-border bg-surface overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Tahun Penilaian</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Tahun Pelaksanaan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Desa / Instansi</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">UPR</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Risiko</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($konteks as $item)
                    <tr class="hover:bg-surface-soft transition-colors">
                        <td class="px-5 py-4 text-text-strong font-semibold">{{ $item->tahun_penilaian }}</td>
                        <td class="px-5 py-4 text-text-strong font-semibold">{{ $item->tahun_pelaksanaan ?? '-' }}</td>
                        <td class="px-5 py-4">
                            <p class="text-text">{{ $item->desa->nama_desa ?? '-' }}</p>
                            <p class="text-xs text-muted">{{ $item->nama_instansi }}</p>
                        </td>
                        <td class="px-5 py-4 text-text">{{ $item->nama_upr ?: '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-surface-soft text-text text-sm font-medium">
                                {{ $item->risiko_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $statusColors = [
                                    'draft'     => 'bg-surface-soft text-muted border-border',
                                    'submitted' => 'bg-warning/10 text-warning border-warning/20',
                                    'approved'  => 'bg-success/10 text-success border-success/20',
                                    'rejected'  => 'bg-danger/10 text-danger border-danger/20',
                                    'archived'  => 'bg-info/10 text-info border-info/20',
                                ];
                                $statusLabels = [
                                    'draft' => 'Draft', 'submitted' => 'Menunggu Review',
                                    'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'archived' => 'Arsip',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$item->status] ?? '' }}">
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('konteks.form', $item) }}" class="inline-flex items-center gap-1 text-accent hover:text-primary text-sm font-medium transition-colors">
                                {{ $item->isEditableByOperator() && auth()->user()->isOperator() ? 'Edit' : 'Lihat' }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-muted text-sm">Belum ada konteks risiko</p>
                                <p class="text-muted opacity-75 text-xs mt-1">Klik "Buat Konteks Baru" untuk memulai</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-init="$el.querySelector('input')?.focus()">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="relative bg-surface border border-border rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-text-strong mb-4">Buat Konteks Baru</h3>
                <form wire:submit="createKonteks">
                    @if(auth()->user()->isAdmin())
                        <label class="block text-sm font-medium text-text mb-1.5">Nama Instansi</label>
                        <input wire:model="newNamaInstansi" type="text"
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        @error('newNamaInstansi')
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror

                        <label class="block text-sm font-medium text-text mb-1.5 mt-4">Nama UPR (Opsional)</label>
                        <input wire:model="newNamaUpr" type="text"
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        @error('newNamaUpr')
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                        
                        <div class="mb-4"></div>
                    @endif

                    <label class="block text-sm font-medium text-text mb-1.5">Tahun Penilaian</label>
                    <input wire:model="newTahun" type="number" min="2020" max="2099"
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                    @error('newTahun')
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror

                    <label class="block text-sm font-medium text-text mb-1.5 mt-4">Tahun Pelaksanaan</label>
                    <input wire:model="newTahunPelaksanaan" type="number" min="2020" max="2099"
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                    <p class="mt-1.5 text-xs text-muted">Diisi dengan tahun program/layanan yang akan dilaksanakan, bisa berbeda dari Tahun Penilaian di atas.</p>
                    @error('newTahunPelaksanaan')
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror

                    @if($previousKonteksOptions && $previousKonteksOptions->isNotEmpty())
                        <label class="block text-sm font-medium text-text mb-1.5 mt-4">Salin Data (Opsional)</label>
                        <select wire:model="duplicateFromId" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                            <option value="">-- Jangan Salin Apa Pun --</option>
                            @foreach($previousKonteksOptions as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->nama_upr }} — Penilaian {{ $opt->tahun_penilaian }} / Pelaksanaan {{ $opt->tahun_pelaksanaan }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-muted">Salin identitas, Sasaran UPR & Struktur Pelaksana dari konteks lain. Daftar risiko TIDAK ikut disalin.</p>
                    @endif

                    <div class="flex gap-3 mt-6">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm text-muted hover:bg-surface-soft transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="flex-1 rounded-lg bg-primary hover:bg-primary-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">Buat</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
