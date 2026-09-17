<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Peta Risiko (Heatmap 5×5) — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">Visualisasi sebaran risiko SPBE berdasarkan level kemungkinan dan dampak</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('risiko.index', $konteks) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Daftar Risiko
            </a>
        </div>
    </div>


    {{-- Selera Risiko Info Card --}}
    <div class="mb-6 p-4 rounded-xl border border-border bg-surface flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold">
                {{ $konteks->selera_risiko }}
            </div>
            <div>
                <p class="text-sm font-semibold text-text-strong">Batas Selera Risiko: {{ $konteks->selera_risiko }}</p>
                <p class="text-xs text-muted">Risiko dengan besaran > {{ $konteks->selera_risiko }} memerlukan rencana mitigasi prioritas.</p>
            </div>
        </div>

        @php $rmc = app(\App\Services\RiskMatrixCalculator::class); @endphp
        <div class="flex items-center gap-4 text-xs">
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-risk-low"></span><span class="text-text">Rendah (1-4)</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-risk-medium"></span><span class="text-text">Sedang (5-9)</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-risk-high"></span><span class="text-text">Tinggi (10-16)</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-risk-critical"></span><span class="text-text">Sangat Tinggi (17-25)</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Matrix 5x5 --}}
        <div class="lg:col-span-7 rounded-xl border border-border bg-surface p-6">
            <div class="relative">
                {{-- Y-Axis Label --}}
                <div class="absolute -left-11 top-1/2 -translate-y-1/2 -rotate-90 text-xs font-semibold text-muted uppercase tracking-wider">
                    Kemungkinan →
                </div>

                <div class="pl-4">
                    {{-- 5x5 Matrix Rows --}}
                    <div class="space-y-2">
                        @foreach([5, 4, 3, 2, 1] as $k)
                            <div class="flex items-center gap-2">
                                <span class="w-6 text-xs font-bold text-muted text-right">{{ $k }}</span>
                                <div class="grid grid-cols-5 gap-2 flex-1">
                                    @foreach([1, 2, 3, 4, 5] as $d)
                                        @php
                                            $cell = $matrix[$k][$d];
                                            $isSelected = ($selectedK === $k && $selectedD === $d);
                                            $cellLabel = $rmc->label($cell['besaran']);
                                            $colorBg = $rmc->colorClass($cellLabel);
                                        @endphp
                                        <button wire:click="selectCell({{ $k }}, {{ $d }})"
                                            class="h-16 rounded-xl border p-2 flex flex-col items-center justify-between transition-all cursor-pointer {{ $colorBg }} {{ $isSelected ? 'ring-2 ring-white scale-105 shadow-xl' : '' }}">
                                            <span class="text-[10px] opacity-70 font-mono font-medium">{{ $cell['besaran'] }}</span>
                                            @if($cell['count'] > 0)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-surface border border-border text-text-strong font-bold text-xs shadow-sm">
                                                    {{ $cell['count'] }}
                                                </span>
                                            @else
                                                <span class="text-xs opacity-20">-</span>
                                            @endif
                                            <span></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- X-Axis Labels --}}
                    <div class="flex items-center gap-2 mt-3">
                        <span class="w-6"></span>
                        <div class="grid grid-cols-5 gap-2 flex-1 text-center">
                            @foreach([1, 2, 3, 4, 5] as $d)
                                <span class="text-xs font-bold text-muted">{{ $d }}</span>
                            @endforeach
                        </div>
                    </div>
                    <p class="text-center text-xs font-semibold text-muted uppercase tracking-wider mt-2">Dampak →</p>
                </div>
            </div>
        </div>

        {{-- Detail List of Selected Risks --}}
        <div class="lg:col-span-5 rounded-xl border border-border bg-surface p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-text-strong">
                    @if($selectedK && $selectedD)
                        Risiko di K={{ $selectedK }}, D={{ $selectedD }} (Besaran: {{ $matrix[$selectedK][$selectedD]['besaran'] }})
                    @else
                        Semua Risiko ({{ $filteredRisikos->count() }})
                    @endif
                </h3>
                @if($selectedK && $selectedD)
                    <button wire:click="selectCell(null, null)" class="text-xs text-accent hover:underline cursor-pointer">
                        Reset Filter
                    </button>
                @endif
            </div>

            <div class="space-y-3 overflow-y-auto max-h-96 flex-1 pr-1">
                @forelse($filteredRisikos as $item)
                    <a href="{{ route('risiko.form', [$konteks, $item]) }}" class="block p-3.5 rounded-lg border border-border bg-surface-soft hover:bg-surface-raised transition-colors">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-mono font-bold text-primary">{{ $item->kode_risiko }}</span>
                            <span class="text-xs text-muted">Besaran: <strong class="text-text-strong">{{ $item->besaran_risiko }}</strong></span>
                        </div>
                        <p class="text-sm text-text line-clamp-2 leading-relaxed">{{ $item->peristiwa_risiko }}</p>
                        <div class="mt-2 flex items-center justify-between text-[11px] text-muted">
                            <span>{{ $item->kategori_risiko ?? 'Tanpa kategori' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-12 text-muted text-sm">
                        Tidak ada risiko pada sel matriks ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
