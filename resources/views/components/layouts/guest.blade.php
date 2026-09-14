<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Manajemen Risiko SPBE Dinas">
    <title>{{ $title ?? 'Login' }} — SPBE Dinas</title>
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
<body class="min-h-screen bg-page flex items-center justify-center p-4 antialiased">
    {{-- Subtle decorative overlay — only visible in dark mode --}}
    <div class="fixed inset-0 pointer-events-none dark:opacity-30 opacity-0 transition-opacity" style="background-image: radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(16, 185, 129, 0.08) 0%, transparent 50%);"></div>

    {{-- Theme toggle (floating, pojok kanan atas) --}}
    <div class="fixed top-4 right-4 z-50">
        <button
            type="button"
            id="theme-toggle-guest"
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
    </div>

    <div class="relative z-10 w-full max-w-md">
        {{-- Logo / Branding --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/25 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-text-strong">SPBE Dinas</h1>
            <p class="text-muted text-sm mt-1">Sistem Pemerintahan Berbasis Elektronik</p>
        </div>

        {{ $slot }}
    </div>
</body>
</html>
