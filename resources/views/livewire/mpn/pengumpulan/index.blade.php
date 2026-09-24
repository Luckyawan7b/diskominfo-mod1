<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Timeline Riwayat Revisi</h1>
            <p class="text-sm text-muted mt-1">Formulir 2: Timeline Riwayat Pengumpulan & Pengolahan Pengetahuan</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('mpn.pengetahuan.form', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan]) }}" wire:navigate
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Form
            </a>
            @if ($isEditable)
                <a href="{{ route('mpn.pengumpulan.form', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan, 'pengumpulan' => 'new']) }}" wire:navigate
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Data Baru
                </a>
            @endif
        </div>
    </div>



    {{-- Timeline Riwayat --}}
    <div class="relative border-l-2 border-border ml-4 md:ml-6 py-4 space-y-8">
        @forelse($pengumpulanList as $i => $item)
            <div class="relative pl-6 sm:pl-8" wire:key="pengumpulan-{{ $item->id }}">
                {{-- Timeline dot --}}
                <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full bg-surface border-2 border-primary z-10"></div>
                
                {{-- Card Content --}}
                <div class="rounded-xl border border-border bg-surface p-5 hover:border-border-strong transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-sm font-bold text-text-strong">
                                    {{ \Carbon\Carbon::parse($item->tanggal_pengumpulan)->translatedFormat('d F Y') }}
                                </h3>
                                {{-- Badge: Status Alur Kerja SIMPAN (4 state) --}}
                                @if($item->status_publikasi_simpan === 'Dipublikasikan')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-success-bg text-success border border-success/30">Dipublikasikan</span>
                                @elseif($item->status_publikasi_simpan === 'Draft')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warning-bg text-warning border border-warning/30">Draft</span>
                                @elseif($item->status_publikasi_simpan === 'Ditolak')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-danger-bg text-danger border border-danger/30">Ditolak</span>
                                @elseif($item->status_publikasi_simpan === 'Diarsipkan')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-surface-soft text-muted border border-border">Diarsipkan</span>
                                @endif

                                {{-- Badge: Visibilitas Dokumen --}}
                                @if($item->visibilitas_dokumen === 'Publik')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-success-bg text-success border border-success/30">Publik</span>
                                @elseif($item->visibilitas_dokumen === 'Internal')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-surface-soft text-muted border border-border">Internal</span>
                                @endif
                                
                                @if($i === 0)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-info-bg text-info border border-info/30">Versi Terbaru</span>
                                @endif
                            </div>
                            <p class="text-xs text-muted">ID: {{ $item->id_pengetahuan }} | Unit: {{ $item->unit_pengumpulan }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('mpn.pengumpulan.form', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan, 'pengumpulan' => $item->id]) }}" wire:navigate
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-surface hover:bg-surface-soft text-xs font-semibold text-text transition-colors">
                                {{ $isEditable ? 'Edit' : 'Lihat' }}
                            </a>
                            @if ($isEditable)
                                <button type="button"
                                    @click="$dispatch('confirm-action', {
                                        wireId: $wire.id,
                                        action: 'deletePengumpulan',
                                        params: [{{ $item->id }}],
                                        title: 'Hapus Data',
                                        message: 'Apakah Anda yakin ingin menghapus catatan riwayat ini?',
                                        confirmText: 'Ya, Hapus',
                                        type: 'danger'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-danger/30 text-danger hover:bg-danger-bg text-xs font-semibold transition-colors cursor-pointer">
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-surface-soft p-4 rounded-lg border border-border">
                        <div>
                            <p class="text-[10px] font-semibold text-muted uppercase tracking-wider mb-1">Detail Dokumen</p>
                            <ul class="text-xs space-y-1 text-text">
                                <li><span class="text-muted w-16 inline-block">Penulis:</span> {{ $item->penulis ?: '-' }}</li>
                                <li><span class="text-muted w-16 inline-block">Label:</span> {{ $item->label_tags ?: '-' }}</li>
                                <li><span class="text-muted w-16 inline-block">URL:</span> 
                                    @if($item->url)
                                        <a href="{{ $item->url }}" target="_blank" class="text-accent hover:underline">Buka Tautan</a>
                                    @else
                                        -
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-muted uppercase tracking-wider mb-1">Keterkaitan</p>
                            <ul class="text-xs space-y-1 text-text">
                                <li><span class="text-muted w-24 inline-block">Revisi Dari:</span> 
                                    @if($item->revisiDari)
                                        <a href="{{ route('mpn.pengumpulan.form', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan, 'pengumpulan' => $item->revisiDari->id]) }}" class="text-accent hover:underline">{{ $item->revisiDari->id_pengetahuan }}</a>
                                    @else
                                        <span class="italic">Versi Awal</span>
                                    @endif
                                </li>
                                <li><span class="text-muted w-24 inline-block">Rating:</span> 
                                    @if($item->rating_pengetahuan)
                                        <span class="text-warning font-bold">★ {{ number_format($item->rating_pengetahuan, 1) }}</span>
                                    @else
                                        -
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="relative pl-6 sm:pl-8">
                <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full bg-surface border-2 border-border z-10"></div>
                <div class="rounded-xl border border-dashed border-border p-8 text-center bg-surface-soft">
                    <svg class="w-10 h-10 text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                    <p class="font-medium text-text mb-1">Belum ada riwayat pengumpulan dokumen.</p>
                    <p class="text-xs text-muted">Klik "Tambah Data Baru" untuk mencatat versi awal pengetahuan ini.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
