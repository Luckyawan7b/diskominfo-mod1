<div class="py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-10">
            {{-- Breadcrumb back --}}
            <a href="{{ route('layanan.index') }}" class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-text transition-colors mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Daftar Layanan
            </a>

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        @if($layanan->is_prioritas)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-warning text-white">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Prioritas
                            </span>
                        @endif
                        <span class="text-xs text-muted font-medium uppercase tracking-wider">Layanan</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-text-strong truncate">{{ $layanan->nama_layanan }}</h1>
                    <p class="text-muted mt-1 text-sm">
                        @if($layanan->bidang_bagian) {{ $layanan->bidang_bagian }} · @endif
                        Pilih modul yang ingin Anda kelola
                    </p>
                </div>

                {{-- Overall progress + Submit --}}
                @php
                    $totalModul  = count($modules);
                    $filledModul = collect($modules)->where('filled', true)->count();
                    $progPct     = $totalModul > 0 ? round(($filledModul / $totalModul) * 100) : 0;
                @endphp
                <div class="flex flex-col gap-3 shrink-0 items-end">
                    <div class="rounded-xl border border-border bg-surface p-4 min-w-[160px] text-center w-full">
                        <div class="text-2xl font-bold text-text-strong">{{ $filledModul }}/{{ $totalModul }}</div>
                        <div class="text-xs text-muted mt-0.5">Modul Terisi</div>
                        <div class="h-1.5 rounded-full bg-border mt-2">
                            <div class="h-1.5 rounded-full bg-accent transition-all"
                                 style="width: {{ $progPct }}%"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Module grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($modules as $index => $module)
                @if($module['active'])
                    @php
                        $tag      = isset($module['wireAction']) && $module['wireAction']
                            ? 'button'
                            : 'a';
                        $attrStr  = $tag === 'a'
                            ? 'href="' . $module['route'] . '"'
                            : 'wire:click="' . $module['wireAction'] . '" type="button"';
                    @endphp
                    {{-- Active module card --}}
                    <{{ $tag }} {!! $attrStr !!}
                       class="group relative rounded-2xl border border-border bg-module-bg p-6 transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl cursor-pointer text-left w-full">

                        {{-- Badge count --}}
                        @if($index === 0 && $badgeCount > 0)
                            <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-danger text-white text-xs font-bold flex items-center justify-center shadow-lg animate-pulse">
                                {{ $badgeCount }}
                            </div>
                        @endif

                        {{-- Filled indicator --}}
                        @if($module['filled'])
                            <div class="absolute top-3 right-3">
                                <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif

                        {{-- Icon --}}
                        <div class="w-14 h-14 rounded-xl bg-{{ $module['tint'] }} flex items-center justify-center shadow-lg mb-4 group-hover:scale-110 transition-transform duration-300">
                            @include('partials.icons.' . $module['icon'], ['class' => 'w-7 h-7 text-white'])
                        </div>

                        <h3 class="text-lg font-semibold text-text-strong mb-1">{{ $module['name'] }}</h3>
                        <p class="text-sm text-muted leading-relaxed">{{ $module['description'] }}</p>

                        {{-- Arrow indicator --}}
                        <div class="mt-4 flex items-center gap-1 text-text group-hover:text-{{ $module['tint'] }} text-sm font-medium transition-colors duration-300">
                            <span>{{ $module['filled'] ? 'Lihat / Edit' : 'Mulai Isi' }}</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </div>
                    </{{ $tag }}>
                @else
                    {{-- Coming soon card --}}
                    <div class="relative rounded-2xl border border-border bg-surface-soft p-6 opacity-50 cursor-not-allowed">
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-surface text-muted border border-border">
                                Segera Hadir
                            </span>
                        </div>
                        <div class="w-14 h-14 rounded-xl bg-surface-raised flex items-center justify-center mb-4">
                            @include('partials.icons.' . $module['icon'], ['class' => 'w-7 h-7 text-muted'])
                        </div>
                        <h3 class="text-lg font-semibold text-muted mb-1">{{ $module['name'] }}</h3>
                        <p class="text-sm text-muted leading-relaxed">{{ $module['description'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- User summary bar --}}
        <div class="mt-10 text-center">
            <p class="text-sm text-muted">
                Masuk sebagai <span class="text-text font-medium">{{ auth()->user()->name }}</span>
                <span class="text-border-strong mx-1">•</span>
                <span class="text-muted">{{ auth()->user()->role->label }}</span>
                @if(auth()->user()->nama_dinas)
                    <span class="text-border-strong mx-1">•</span>
                    <span class="text-muted">{{ auth()->user()->nama_dinas }}</span>
                @endif
            </p>
        </div>

    </div>
</div>
