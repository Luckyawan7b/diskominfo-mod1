<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Catatan Alih Pengetahuan</h1>
            <p class="text-sm text-muted mt-1">Dokumen: {{ $pengumpulan->id_pengetahuan }} ({{ \Carbon\Carbon::parse($pengumpulan->tanggal_pengumpulan)->format('d M Y') }})</p>
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
        
        {{-- Form Tambah Alih Pengetahuan --}}
        @if($isEditable)
            <div class="lg:col-span-1">
                <div class="rounded-xl border border-border bg-surface p-5 sticky top-20">
                    <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider mb-4 pb-3 border-b border-border">
                        Catat Kegiatan Baru
                    </h3>
                    
                    <form wire:submit.prevent="saveAlihPengetahuan" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Kegiatan <span class="text-danger">*</span></label>
                            <input type="date" wire:model="tanggal_kegiatan" required
                                class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-2">Metode Alih Pengetahuan</label>
                            <div class="space-y-2 bg-surface-soft p-3 rounded-lg border border-border">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="metode_pelatihan" class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                    <span class="text-sm">Pelatihan</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="metode_workshop" class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                    <span class="text-sm">Workshop / FGD</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="metode_sosialisasi" class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                    <span class="text-sm">Sosialisasi</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="metode_mentoring" class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                    <span class="text-sm">Mentoring / Coaching</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="metode_sharing" class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                    <span class="text-sm">Sharing Session</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model.live="metode_lainnya" class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                    <span class="text-sm">Lainnya...</span>
                                </label>
                                
                                @if($metode_lainnya)
                                    <input type="text" wire:model="keterangan_lainnya" placeholder="Sebutkan metode lainnya..."
                                        class="w-full mt-2 rounded border border-border bg-field px-3 py-1.5 text-text text-xs focus:outline-none focus:ring-2 focus:ring-accent">
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Penerima Pengetahuan <span class="text-danger">*</span></label>
                            <input type="text" wire:model="penerima_pengetahuan" required placeholder="Contoh: Seluruh Staf Bidang X"
                                class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Hasil Evaluasi</label>
                            <x-textarea-auto wire:model="hasil_evaluasi" rows="2" placeholder="Contoh: Peserta dapat menerapkan SOP dengan baik..." />
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all disabled:opacity-50">
                                Simpan Kegiatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Daftar Alih Pengetahuan --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-border bg-surface overflow-hidden">
                <div class="px-5 py-4 border-b border-border bg-surface-soft">
                    <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider">
                        Riwayat Alih Pengetahuan
                    </h3>
                </div>

                <div class="divide-y divide-border">
                    @forelse($alihList as $item)
                        <div class="p-5 hover:bg-surface-soft transition-colors" wire:key="alih-{{ $item->id }}">
                            <div class="flex justify-between items-start gap-4 mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="text-sm font-bold text-text-strong">{{ \Carbon\Carbon::parse($item->tanggal_kegiatan)->translatedFormat('d F Y') }}</h4>
                                        <span class="text-xs text-muted">Penerima: {{ $item->penerima_pengetahuan }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @if($item->metode_pelatihan) <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">Pelatihan</span> @endif
                                        @if($item->metode_workshop) <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">Workshop</span> @endif
                                        @if($item->metode_sosialisasi) <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">Sosialisasi</span> @endif
                                        @if($item->metode_mentoring) <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">Mentoring</span> @endif
                                        @if($item->metode_sharing) <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">Sharing</span> @endif
                                        @if($item->metode_lainnya) <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">{{ $item->keterangan_lainnya }}</span> @endif
                                    </div>
                                </div>
                                
                                @if($isEditable)
                                    <button type="button"
                                        @click="$dispatch('confirm-action', {
                                            wireId: $wire.id,
                                            action: 'deleteAlihPengetahuan',
                                            params: [{{ $item->id }}],
                                            title: 'Hapus Kegiatan',
                                            message: 'Hapus riwayat alih pengetahuan ini?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger'
                                        })"
                                        class="text-danger hover:text-danger-strong hover:bg-danger/10 p-1.5 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                            
                            @if($item->hasil_evaluasi)
                                <div class="bg-surface rounded-lg p-3 border border-border/50">
                                    <p class="text-[10px] font-semibold text-muted uppercase tracking-wider mb-1">Hasil Evaluasi</p>
                                    <p class="text-sm text-text whitespace-pre-line">{{ $item->hasil_evaluasi }}</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <svg class="w-12 h-12 text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <p class="font-medium text-text mb-1">Belum ada riwayat alih pengetahuan.</p>
                            <p class="text-xs text-muted">Isi form di samping untuk mencatat kegiatan pembagian pengetahuan ke unit/staf lain.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
