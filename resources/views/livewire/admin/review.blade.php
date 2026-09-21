<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Monitoring Layanan</h1>
            <p class="text-sm text-muted mt-1">Pantau status pengisian modul Manajemen Risiko dari seluruh OPD</p>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari layanan atau OPD..." class="flex-1 min-w-[250px] rounded-lg border border-border bg-field text-sm text-text px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent">
        
        <select wire:model.live="filterDinas" class="rounded-lg border border-border bg-field text-sm text-text px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent">
            <option value="">Semua Perangkat Daerah</option>
            @foreach($dinasList as $dinas)
                <option value="{{ $dinas }}">{{ $dinas }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-border bg-surface overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Nama Layanan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Perangkat Daerah</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Jml Dokumen (Tahun)</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Terakhir Diperbarui</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($layananList as $item)
                    <tr class="hover:bg-surface-soft transition-colors">
                        <td class="px-5 py-4">
                            <p class="text-text-strong font-medium truncate max-w-sm" title="{{ $item->nama_layanan }}">
                                {{ $item->nama_layanan }}
                            </p>
                        </td>
                        <td class="px-5 py-4 text-text text-sm">
                            {{ $item->creator?->nama_dinas ?? '-' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-surface-soft text-text text-xs font-semibold border border-border">
                                {{ $item->mr_konteks_history_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-muted text-xs">
                            {{ $item->mr_konteks_history_max_updated_at ? \Carbon\Carbon::parse($item->mr_konteks_history_max_updated_at)->format('d M Y, H:i') : '-' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.review.konteks', $item) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-soft text-text hover:bg-border border border-border text-xs font-medium transition-colors">
                                Lihat Detail
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-muted text-sm">
                            Tidak ada data yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
