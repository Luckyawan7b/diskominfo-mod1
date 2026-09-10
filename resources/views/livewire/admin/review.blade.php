<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Monitoring Layanan</h1>
            <p class="text-sm text-slate-400 mt-1">Pantau status pengisian modul Manajemen Risiko dari seluruh OPD</p>
        </div>
    </div>

    {{-- Filter berdasarkan Nama Dinas (bukan Desa) --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <select wire:model.live="filterDinas" class="rounded-lg border border-slate-600 bg-slate-800 text-sm text-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">Semua Perangkat Daerah</option>
            @foreach($dinasList as $dinas)
                <option value="{{ $dinas }}">{{ $dinas }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-slate-700/50 bg-slate-800/50 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700/50">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Perangkat Daerah / Layanan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">UPR</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tahun</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Risiko</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Diperbarui</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/30">
                @forelse($konteksList as $item)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-5 py-4">
                            {{-- Tampilkan nama instansi / creator->nama_dinas --}}
                            <p class="text-white font-medium">
                                {{ $item->nama_instansi ?: ($item->layanan?->creator?->nama_dinas ?? '-') }}
                            </p>
                            @if($item->layanan)
                                <p class="text-xs text-slate-500 mt-0.5 truncate max-w-xs" title="{{ $item->layanan->nama_layanan }}">
                                    {{ $item->layanan->nama_layanan }}
                                </p>
                            @else
                                <p class="text-xs text-slate-600 italic mt-0.5">Layanan tidak ditemukan</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-300 text-sm">{{ $item->nama_upr ?: '-' }}</td>
                        <td class="px-5 py-4 text-center text-slate-400 text-sm">{{ $item->tahun_penilaian }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-slate-700/50 text-slate-300 text-xs font-semibold">
                                {{ $item->risiko_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-400 text-xs">
                            {{ $item->updated_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.review.detail', $item) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 text-xs font-medium transition-colors">
                                Lihat Detail
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500 text-sm">
                            Tidak ada data yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
