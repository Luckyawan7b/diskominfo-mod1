<div>
    <div class="bg-surface/80 backdrop-blur-sm rounded-2xl border border-border shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-text-strong mb-1">Masuk ke Akun Anda</h2>
        <p class="text-sm text-muted mb-6">Gunakan email dan password yang telah diberikan</p>

        <form wire:submit="authenticate" class="space-y-5">
            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-text mb-1.5">Email</label>
                <input wire:model="email" type="email" id="email" autocomplete="email" autofocus
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text placeholder-muted text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-shadow"
                    placeholder="nama@dinas.go.id">
                @error('email')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-text mb-1.5">Password</label>
                <input wire:model="password" type="password" id="password" autocomplete="current-password"
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text placeholder-muted text-sm focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-shadow"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="flex items-center">
                <input wire:model="remember" type="checkbox" id="remember"
                    class="w-4 h-4 rounded border-border bg-field text-accent focus:ring-accent focus:ring-offset-0">
                <label for="remember" class="ml-2 text-sm text-muted">Ingat saya</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full rounded-lg bg-primary hover:bg-primary-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-surface transition-all cursor-pointer"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75 cursor-wait">
                <span wire:loading.remove>Masuk</span>
                <span wire:loading class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    Memproses...
                </span>
            </button>
        </form>
    </div>
</div>
