<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Pemerintahan Berbasis Elektronik — Dashboard Hub">
    <title>{{ $title ?? 'Dashboard' }} — SPBE Diskominfo</title>
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-900 antialiased">
    {{-- Background pattern --}}
    <div class="fixed inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900"></div>
    <div class="fixed inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 30%, rgba(16, 185, 129, 0.15) 0%, transparent 40%), radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.1) 0%, transparent 40%);"></div>

    {{-- Topbar minimal --}}
    <nav class="relative z-20 border-b border-slate-700/50 bg-slate-900/80 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="text-white font-semibold text-lg">SPBE Perangkat Daerah</span>
            </div>

            {{-- User info --}}
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400">
                        {{ auth()->user()->role->label }}
                        @if(auth()->user()->nama_dinas)
                            — {{ auth()->user()->nama_dinas }}
                        @endif
                    </p>
                </div>
                <button
                    type="button"
                    id="theme-toggle-hub"
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
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="relative z-10">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-400 text-sm">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-red-500/10 border border-red-500/20 p-4 text-red-400 text-sm">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
