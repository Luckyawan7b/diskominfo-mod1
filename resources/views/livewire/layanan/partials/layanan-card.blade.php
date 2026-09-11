@php
    // Hitung progres 5 modul: saat ini hanya MR yang sudah ada
    $totalModul     = 5;
    $modulTerisi    = $layanan->mr_konteks_count > 0 ? 1 : 0;
    $progressPersen = ($modulTerisi / $totalModul) * 100;

    $statusLabel = match($layanan->status_layanan) {
        'berjalan'     => ['label' => 'Berjalan',     'class' => 'bg-success-bg text-success border-success/20'],
        'direncanakan' => ['label' => 'Direncanakan', 'class' => 'bg-info-bg text-info border-info/20'],
        'dihentikan'   => ['label' => 'Dihentikan',   'class' => 'bg-danger-bg text-danger border-danger/20'],
        default        => ['label' => '-',             'class' => 'bg-surface-soft text-muted border-border'],
    };

    $cardBorder = $isPrioritas
        ? 'border-warning/30 hover:border-warning/60 hover:shadow-warning/10'
        : 'border-border hover:border-border-strong hover:shadow-border/10';
@endphp

<div class="group relative rounded-2xl border {{ $cardBorder }} bg-surface backdrop-blur-sm p-5 transition-all duration-300 hover:shadow-xl flex flex-col gap-4">

    {{-- Priority badge --}}
    @if($isPrioritas)
        <div class="absolute -top-2.5 right-4">
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warning text-white shadow-md shadow-warning/30">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Prioritas
            </span>
        </div>
    @endif

    {{-- Top Row: Nama + Status --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-text-strong text-base leading-tight truncate group-hover:text-accent transition-colors">
                {{ $layanan->nama_layanan }}
            </h3>
            @if($layanan->bidang_bagian)
                <p class="text-xs text-muted mt-0.5">{{ $layanan->bidang_bagian }}</p>
            @endif
            @if(auth()->user()->isAdmin() && $layanan->creator)
                <p class="text-xs text-muted mt-0.5">{{ $layanan->creator->nama_dinas }}</p>
            @endif
        </div>
        <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusLabel['class'] }}">
            {{ $statusLabel['label'] }}
        </span>
    </div>

    {{-- Deskripsi singkat --}}
    @if($layanan->deskripsi_layanan)
        <p class="text-sm text-text leading-relaxed line-clamp-2">{{ $layanan->deskripsi_layanan }}</p>
    @else
        <p class="text-sm text-muted italic">Belum ada deskripsi layanan.</p>
    @endif

    {{-- Progress 5 Modul --}}
    <div>
        <div class="flex items-center justify-between text-xs mb-1.5">
            <span class="text-muted">Progres Pengisian Modul</span>
            <span class="font-semibold {{ $modulTerisi > 0 ? 'text-accent' : 'text-muted' }}">
                {{ $modulTerisi }}/{{ $totalModul }} modul
            </span>
        </div>
        <div class="h-1.5 rounded-full bg-border">
            <div class="h-1.5 rounded-full bg-accent transition-all duration-500"
                 style="width: {{ $progressPersen }}%"></div>
        </div>
        {{-- Mini status indikator 5 modul --}}
        <div class="flex items-center gap-1.5 mt-2">
            @php
                $modulList = [
                    ['label' => 'MR', 'filled' => $layanan->mr_konteks_count > 0],
                    ['label' => 'MP', 'filled' => false],
                    ['label' => 'MPR', 'filled' => false],
                    ['label' => 'MK', 'filled' => false],
                    ['label' => 'MRL', 'filled' => false],
                ];
            @endphp
            @foreach($modulList as $m)
                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold {{ $m['filled'] ? 'bg-primary/20 text-primary' : 'bg-surface-soft text-muted' }}">
                    {{ $m['label'] }}
                </span>
            @endforeach
            <span class="text-[10px] text-muted ml-1">MR = Manajemen Risiko</span>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 pt-2 border-t border-border">
        <a href="{{ route('layanan.dashboard', $layanan) }}"
           class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary/10 border border-primary/20 text-sm font-medium text-primary hover:bg-primary/20 transition-colors">
            Buka 5 Modul
            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
        <a href="{{ route('layanan.edit', $layanan) }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border text-muted hover:text-text hover:border-border-strong transition-colors"
           title="Edit Deskripsi Layanan">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </a>
    </div>
</div>
