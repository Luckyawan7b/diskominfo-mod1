<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Daftar Risiko — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">Formulir 5-7: Identifikasi, analisis, evaluasi, dan perlakuan risiko</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('pemantauan.form', $konteks) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Pemantauan
            </a>
            <a href="{{ route('risiko.peta', $konteks) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                Peta Risiko
            </a>
            @if($isEditable)
                <a href="{{ route('risiko.form', [$konteks, 'new']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Risiko
                </a>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-border bg-surface overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-muted uppercase">Kode</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-muted uppercase">Peristiwa Risiko</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-muted uppercase">Kategori</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-muted uppercase">K</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-muted uppercase">D</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-muted uppercase">Besaran</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-muted uppercase">Prioritas</th>
                    <th class="px-4 py-3 w-16"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($risikos as $r)
                    @php
                        $rmc = app(\App\Services\RiskMatrixCalculator::class);
                        if ($r->besaran_risiko) {
                            $besaranLabel = $rmc->label($r->besaran_risiko);
                            $besaranColor = $rmc->colorClass($besaranLabel);
                        } else {
                            $besaranColor = 'bg-surface-soft text-muted';
                        }
                    @endphp
                    <tr class="hover:bg-surface-soft transition-colors">
                        <td class="px-4 py-3 text-text-strong font-mono font-semibold">{{ $r->kode_risiko }}</td>
                        <td class="px-4 py-3 text-text max-w-xs">
                            <div class="truncate">{{ $r->peristiwa_risiko }}</div>
                        </td>
                        <td class="px-4 py-3 text-muted text-xs">{{ $r->kategori_risiko ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-text">{{ $r->level_kemungkinan ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-text">{{ $r->level_dampak ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold border {{ $besaranColor }}">
                                {{ $r->besaran_risiko ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-text">{{ $r->prioritas_risiko ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3 justify-end">
                                <a href="{{ route('risiko.form', [$konteks, $r]) }}" class="text-accent hover:text-primary transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                @if($isEditable)
                                    <button
                                        wire:click="deleteRisiko({{ $r->id }})"
                                        wire:confirm="Yakin ingin menghapus risiko {{ $r->kode_risiko }}? Item ini akan dipindahkan ke tempat sampah dan bisa dipulihkan oleh admin."
                                        class="text-danger hover:text-red-600 transition-colors cursor-pointer"
                                        title="Hapus risiko ini"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-muted text-sm">Belum ada risiko terdaftar</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
