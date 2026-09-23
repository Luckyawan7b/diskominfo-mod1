<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Pemanfaatan & Rating Pengetahuan</h1>
            <p class="text-sm text-muted mt-1">Formulir 3: Penggunaan & Alih Pengetahuan — Bagian Pemanfaatan</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('mpn.pengumpulan.index', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan]) }}" wire:navigate
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Timeline
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-success-bg border border-success/30 text-success text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Form Tambah Pemanfaatan --}}
        @if($isEditable)
            <div class="lg:col-span-1">
                <div class="rounded-xl border border-border bg-surface p-5 sticky top-20">
                    <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider mb-4 pb-3 border-b border-border">
                        Catat Pemanfaatan Baru
                    </h3>
                    
                    <form wire:submit.prevent="savePemanfaatan" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" wire:model="tanggal_pemanfaatan" required
                                class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Jenis Pengguna <span class="text-danger">*</span></label>
                            <select wire:model="jenis_pengguna" required
                                class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                                <option value="Internal">Internal (Pegawai/Tim)</option>
                                <option value="Eksternal">Eksternal (Masyarakat/Instansi Lain)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Nama/Unit Pengguna <span class="text-danger">*</span></label>
                            <input type="text" wire:model="unit_pengguna" required placeholder="Contoh: Bidang Aptika"
                                class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tujuan Pemanfaatan <span class="text-danger">*</span></label>
                            <x-textarea-auto wire:model="tujuan_pemanfaatan" required rows="2" placeholder="Contoh: Referensi pembuatan laporan" />
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Rating (1-5)</label>
                            <div class="flex items-center gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer group">
                                        <input type="radio" wire:model="rating" value="{{ $i }}" class="sr-only">
                                        <svg class="w-7 h-7 {{ $rating >= $i ? 'text-warning' : 'text-border group-hover:text-warning/50' }} transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </label>
                                @endfor
                                <button type="button" wire:click="$set('rating', null)" class="ml-2 text-xs text-muted hover:text-danger hover:underline">Reset</button>
                            </div>
                            <p class="text-[10px] text-muted mt-1">Rating digunakan untuk mengukur tingkat kebermanfaatan pengetahuan.</p>
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all disabled:opacity-50">
                                Simpan Catatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Daftar Pemanfaatan --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-border bg-surface overflow-hidden">
                <div class="px-5 py-4 border-b border-border bg-surface-soft flex justify-between items-center">
                    <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider">
                        Riwayat Pemanfaatan
                    </h3>
                    @php
                        $avg = $pengumpulan->rating_pengetahuan;
                    @endphp
                    @if($avg)
                        <div class="flex items-center gap-1.5 px-3 py-1 bg-surface rounded-md border border-border shadow-sm">
                            <span class="text-xs font-semibold text-muted uppercase">Rata-rata Rating:</span>
                            <span class="text-warning font-bold flex items-center gap-1">
                                {{ number_format($avg, 1) }}
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </span>
                        </div>
                    @endif
                </div>

                <div class="divide-y divide-border">
                    @forelse($pemanfaatanList as $item)
                        <div class="p-5 hover:bg-surface-soft transition-colors" wire:key="pemanfaatan-{{ $item->id }}">
                            <div class="flex justify-between items-start gap-4 mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-strong">{{ $item->unit_pengguna }} <span class="text-xs font-normal text-muted ml-1">({{ $item->jenis_pengguna }})</span></p>
                                        <p class="text-xs text-muted">{{ \Carbon\Carbon::parse($item->tanggal_pemanfaatan)->translatedFormat('d F Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($item->rating)
                                        <div class="flex text-warning" title="Rating: {{ $item->rating }}/5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $item->rating >= $i ? 'text-warning' : 'text-border' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    @endif
                                    
                                    @if($isEditable)
                                        <button type="button"
                                            @click="$dispatch('confirm-action', {
                                                wireId: $wire.id,
                                                action: 'deletePemanfaatan',
                                                params: [{{ $item->id }}],
                                                title: 'Hapus Catatan',
                                                message: 'Hapus catatan pemanfaatan ini?',
                                                confirmText: 'Ya, Hapus',
                                                type: 'danger'
                                            })"
                                            class="text-danger hover:text-danger-strong hover:bg-danger/10 p-1.5 rounded-md transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="pl-13">
                                <p class="text-sm text-text whitespace-pre-line bg-surface rounded-lg p-3 border border-border/50">{{ $item->tujuan_pemanfaatan }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <svg class="w-12 h-12 text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="font-medium text-text mb-1">Belum ada catatan pemanfaatan.</p>
                            <p class="text-xs text-muted">Isi form di samping untuk mencatat siapa saja yang telah menggunakan dokumen ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
