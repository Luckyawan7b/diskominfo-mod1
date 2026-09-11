<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Manajemen Risiko SPBE Perangkat Daerah">
    <title>{{ $title ?? 'Manajemen Risiko' }} — SPBE Diskominfo</title>
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-page antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 z-30 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-64 bg-surface backdrop-blur-sm border-r border-border transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col">
        {{-- Sidebar header --}}
        <div class="h-16 flex items-center gap-3 px-5 border-b border-border">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <span class="text-text-strong font-semibold text-sm">Manajemen Risiko</span>
                <p class="text-muted text-xs">SPBE Perangkat Daerah</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="p-3 space-y-1 overflow-y-auto flex-1">
            {{-- Global Links (Selalu ada) --}}
            <a href="{{ route('layanan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-muted hover:text-text-strong hover:bg-surface-soft transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Daftar Layanan
            </a>
            
            <div class="border-t border-border my-2"></div>

            @if(isset($konteks) && $konteks)
                {{-- MODE B: Ada Konteks Aktif --}}
                <div class="mb-4">
                    <div class="px-3 py-2 mx-1 rounded-lg bg-primary/10 border border-primary/20">
                        <div class="text-sm font-semibold text-primary leading-tight">
                            {{ $konteks->nama_instansi ?: ($konteks->layanan?->creator?->nama_dinas ?? 'Perangkat Daerah') }} — {{ $konteks->nama_upr ?: 'Tanpa UPR' }}
                        </div>
                        <div class="text-[11px] text-muted mt-1">
                            Penilaian {{ $konteks->tahun_penilaian }} / Pelaksanaan {{ $konteks->tahun_pelaksanaan }}
                        </div>
                        <a href="{{ route('konteks.index') }}" class="inline-block mt-2 text-[10px] text-muted hover:text-text uppercase tracking-wider font-semibold transition-colors">
                            &larr; Ganti Konteks
                        </a>
                        
                        @if(isset($availableKonteks) && $availableKonteks->count() > 1)
                            <div class="mt-2 pt-2 border-t border-emerald-500/10" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center justify-between w-full text-left text-xs text-text hover:text-text-strong transition-colors cursor-pointer">
                                    <span>Context Switcher</span>
                                    <svg :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-cloak class="mt-2 space-y-1">
                                    @foreach($availableKonteks as $ak)
                                        <a href="{{ route('konteks.form', $ak) }}" class="block px-2 py-1.5 rounded-md text-[11px] {{ $ak->id === $konteks->id ? 'bg-primary/20 text-primary' : 'text-muted hover:bg-surface-soft hover:text-text' }}">
                                            {{ $ak->nama_upr }} (Pnl. {{ $ak->tahun_penilaian }} / Plk. {{ $ak->tahun_pelaksanaan }})
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('konteks.form', $konteks) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('konteks.form') || request()->routeIs('sasaran.form') || request()->routeIs('struktur.form') ? 'bg-primary/10 text-primary font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    Formulir 0.0 — Penetapan Konteks
                </a>
                <div class="ml-6 space-y-1 mb-2">
                    <a href="{{ route('konteks.form', $konteks) }}" class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('konteks.form') ? 'text-primary' : 'text-muted hover:text-text' }}">Identitas & Selera Risiko</a>
                    <a href="{{ route('sasaran.form', $konteks) }}" class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('sasaran.form') ? 'text-primary' : 'text-muted hover:text-text' }}">Sasaran UPR</a>
                    <a href="{{ route('struktur.form', $konteks) }}" class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('struktur.form') ? 'text-primary' : 'text-muted hover:text-text' }}">Struktur Pelaksana</a>
                </div>

                <a href="{{ route('risiko.index', $konteks) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('risiko.*') && !request()->routeIs('risiko.peta') ? 'bg-primary/10 text-primary font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    Formulir 1.0 — Daftar Risiko
                </a>
                
                <a href="{{ route('layanan-digital.index', $konteks) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('layanan-digital.*') ? 'bg-primary/10 text-primary font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    Formulir 2.0 — Layanan Digital
                </a>
                
                <a href="{{ route('risiko.peta', $konteks) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('risiko.peta') ? 'bg-primary/10 text-primary font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    Formulir 3.0 — Peta Risiko
                </a>
                
                <a href="{{ route('pemantauan.form', $konteks) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('pemantauan.form') ? 'bg-primary/10 text-primary font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    Formulir 3.1 — Pemantauan
                </a>

            @else
                {{-- MODE A: Tidak ada konteks spesifik --}}
                <a href="{{ route('konteks.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('konteks.*') ? 'bg-primary/10 text-primary font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Daftar Konteks
                </a>
            @endif

            @if(auth()->user()->isAdmin())
                <div class="border-t border-border my-2"></div>
                <p class="px-3 py-1 text-xs font-semibold text-muted uppercase tracking-wider">Admin</p>

                <a href="{{ route('admin.review.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.review.*') ? 'bg-warning-bg text-warning font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    Monitoring
                </a>
                <a href="{{ route('admin.user.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.user.*') ? 'bg-info-bg text-info font-medium' : 'text-text hover:text-text-strong hover:bg-surface-soft' }}">
                    Kelola User
                </a>
            @endif
        </nav>
    </aside>

    {{-- Main content --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">
        {{-- Topbar --}}
        <header class="h-16 bg-surface/90 backdrop-blur-sm border-b border-border flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-20">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-muted hover:text-text-strong hover:bg-surface-soft transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('layanan.index') }}" class="text-muted hover:text-text transition-colors">Layanan</a>
                @if(isset($layanan) && $layanan)
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-text max-w-[140px] truncate" title="{{ $layanan->nama_layanan }}">{{ $layanan->nama_layanan }}</span>
                @endif
                @if(isset($breadcrumb))
                    @foreach($breadcrumb as $label => $url)
                        <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        @if($url)
                            <a href="{{ $url }}" class="text-text hover:text-text-strong transition-colors">{{ $label }}</a>
                        @else
                            <span class="text-text-strong font-medium">{{ $label }}</span>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- User menu --}}
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-text-strong">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-muted">{{ auth()->user()->role->label }}</p>
                </div>
                <button
                    type="button"
                    id="theme-toggle-app"
                    x-data="{ dark: document.documentElement.getAttribute('data-theme') === 'dark' }"
                    x-init="$watch('dark', value => {
                        document.documentElement.setAttribute('data-theme', value ? 'dark' : 'light');
                        localStorage.setItem('theme', value ? 'dark' : 'light');
                    })"
                    @click="dark = !dark"
                    :aria-pressed="dark.toString()"
                    aria-label="Ganti tema gelap/terang"
                    class="p-2 rounded-lg text-muted hover:bg-surface-soft hover:text-text transition-colors cursor-pointer"
                >
                    <svg x-show="!dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="dark" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 rounded-lg text-muted hover:text-text-strong hover:bg-surface-soft transition-colors cursor-pointer" title="Keluar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-400 text-sm">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-red-500/10 border border-red-500/20 p-4 text-red-400 text-sm">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
