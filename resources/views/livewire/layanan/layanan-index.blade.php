<div class="py-10 sm:py-14">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-text-strong">Daftar Layanan</h1>
                <p class="text-muted mt-1">
                    {{ $totalLayanan }} layanan terdaftar · Pilih layanan untuk mengisi 5 modul manajemen
                </p>
            </div>
            <a href="{{ route('layanan.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Layanan Baru
            </a>
        </div>

        {{-- Flash messages --}}
        @if(session('warning'))
            <div class="mb-6 rounded-xl border border-warning/30 bg-warning-bg p-4 text-warning text-sm flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-success/30 bg-success-bg p-4 text-success text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Empty state --}}
        @if($totalLayanan === 0)
            <div class="rounded-2xl border border-border bg-surface-soft p-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-surface-raised flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-text mb-2">Belum ada layanan</h2>
                <p class="text-muted text-sm mb-6">Mulailah dengan menambahkan deskripsi layanan pertama Anda.</p>
                <a href="{{ route('layanan.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-sm font-semibold text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Layanan Pertama
                </a>
            </div>
        @else

            {{-- ═══ LAYANAN PRIORITAS ════════════════════════════════════ --}}
            @if($prioritas->count() > 0)
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-warning animate-pulse"></span>
                        <h2 class="text-sm font-semibold text-warning uppercase tracking-wider">Layanan Prioritas</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($prioritas as $layanan)
                            @include('livewire.layanan.partials.layanan-card', ['layanan' => $layanan, 'isPrioritas' => true])
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ═══ LAYANAN REGULER ══════════════════════════════════════ --}}
            @if($reguler->count() > 0)
                <div>
                    @if($prioritas->count() > 0)
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-2 h-2 rounded-full bg-border-strong"></span>
                            <h2 class="text-sm font-semibold text-muted uppercase tracking-wider">Layanan Lainnya</h2>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($reguler as $layanan)
                            @include('livewire.layanan.partials.layanan-card', ['layanan' => $layanan, 'isPrioritas' => false])
                        @endforeach
                    </div>
                </div>
            @endif

        @endif

        {{-- User summary bar --}}
        <div class="mt-12 text-center">
            <p class="text-sm text-muted">
                Masuk sebagai <span class="text-text font-medium">{{ auth()->user()->name }}</span>
                <span class="text-border-strong mx-1">•</span>
                <span class="text-muted">{{ auth()->user()->role->label }}</span>
                @if(auth()->user()->desa)
                    <span class="text-border-strong mx-1">•</span>
                    <span class="text-muted">{{ auth()->user()->desa->nama_desa }}</span>
                @endif
            </p>
        </div>

    </div>
</div>
