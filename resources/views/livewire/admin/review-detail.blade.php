<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-white">
                    {{ $konteks->nama_instansi ?? 'Dinas' }}
                </h1>
                @if($konteks->layanan)
                    <span class="text-slate-400 text-sm">—</span>
                    <span class="text-slate-300 text-sm font-medium max-w-sm truncate" title="{{ $konteks->layanan->nama_layanan }}">
                        {{ $konteks->layanan->nama_layanan }}
                    </span>
                @endif
                @php
                    $statusConfig = [
                        'draft'     => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                        'submitted' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                        'approved'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                        'rejected'  => 'bg-red-500/10 text-red-400 border-red-500/20',
                    ];
                    $statusLabel = [
                        'draft'     => 'Draft',
                        'submitted' => 'Menunggu',
                        'approved'  => 'Selesai',
                        'rejected'  => 'Ditolak',
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusConfig[$konteks->status] ?? '' }}">
                    MR: {{ $statusLabel[$konteks->status] ?? ucfirst($konteks->status) }}
                </span>
            </div>
            <p class="text-sm text-slate-400 mt-1">
                UPR: {{ $konteks->nama_upr ?: '-' }}
                @if($konteks->selera_risiko)
                    &nbsp;·&nbsp; Selera Risiko: {{ $konteks->selera_risiko }}
                @endif
            </p>
        </div>
        <a href="{{ route('admin.review.index') }}"
           class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-600 text-sm text-slate-300 hover:bg-slate-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Monitoring
        </a>
    </div>

    {{-- Info Card --}}
    <div class="rounded-xl border border-slate-700/50 bg-slate-800/40 p-5 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <div>
            <span class="text-slate-500 uppercase font-semibold">Tugas UPR:</span>
            <p class="text-slate-300 mt-1">{{ $konteks->tugas_upr ?: '-' }}</p>
        </div>
        <div>
            <span class="text-slate-500 uppercase font-semibold">Fungsi UPR:</span>
            <p class="text-slate-300 mt-1">{{ $konteks->fungsi_upr ?: '-' }}</p>
        </div>
        <div>
            <span class="text-slate-500 uppercase font-semibold">Struktur Pelaksana:</span>
            <p class="text-slate-300 mt-1">Pemilik: {{ $konteks->strukturPelaksana?->pemilik_risiko ?: '-' }}</p>
            <p class="text-slate-300">Koordinator: {{ $konteks->strukturPelaksana?->koordinator_risiko ?: '-' }}</p>
        </div>
    </div>

    {{-- Notice: Read-only mode --}}
    <div class="mb-6 flex items-start gap-3 p-4 rounded-xl border border-blue-500/20 bg-blue-500/5 text-sm text-blue-300">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Ini adalah halaman <strong>monitoring</strong>. Data disetujui secara otomatis oleh sistem. Admin tidak perlu mengambil tindakan.</span>
    </div>

    {{-- Daftar Risiko (Read-only) --}}
    <h2 class="text-lg font-bold text-white mb-4">
        Daftar Risiko
        <span class="text-sm font-normal text-slate-500 ml-2">({{ $risikos->count() }} risiko)</span>
    </h2>

    <div class="space-y-4">
        @forelse($risikos as $r)
            @php
                $besaranColor = match(true) {
                    !$r->besaran_risiko   => 'bg-slate-700 text-slate-400 border-slate-600',
                    $r->besaran_risiko <= 4  => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                    $r->besaran_risiko <= 9  => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                    $r->besaran_risiko <= 16 => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
                    default                  => 'bg-red-500/20 text-red-400 border-red-500/30',
                };
                $rStatusBadge = match($r->status) {
                    'approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    'draft'    => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                    default    => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                };
            @endphp
            <div class="rounded-xl border border-slate-700/60 bg-slate-800/50 p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-700/50 pb-3">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-sm font-mono font-bold text-white bg-slate-700/60 px-2.5 py-1 rounded">
                            {{ $r->kode_risiko }}
                        </span>
                        <span class="text-xs text-slate-400">
                            {{ $r->kategori_risiko ?? 'Tanpa Kategori' }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded border {{ $besaranColor }} font-semibold">
                            K={{ $r->level_kemungkinan }} × D={{ $r->level_dampak }}
                            (Besaran: {{ $r->besaran_risiko }})
                        </span>
                    </div>
                    {{-- Status badge (read-only) --}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $rStatusBadge }}">
                        {{ ucfirst($r->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <strong class="text-slate-300">Peristiwa Risiko:</strong>
                        <p class="text-slate-200 mt-0.5 text-sm">{{ $r->peristiwa_risiko }}</p>
                    </div>
                    <div>
                        <strong class="text-slate-300">Penyebab / Akar Masalah:</strong>
                        <p class="text-slate-400 mt-0.5">{{ $r->penyebab ?: '-' }}</p>
                    </div>
                    <div>
                        <strong class="text-slate-300">Dampak:</strong>
                        <p class="text-slate-400 mt-0.5">{{ $r->dampak_risiko ?: '-' }}</p>
                    </div>
                    <div>
                        <strong class="text-slate-300">
                            Rencana Perlakuan ({{ $r->perlakuan?->keputusan_perlakuan ?? '-' }}):
                        </strong>
                        <p class="text-slate-400 mt-0.5">{{ $r->perlakuan?->deskripsi_detail_perlakuan ?: '-' }}</p>
                        <p class="text-slate-500 mt-0.5 text-[11px]">
                            Target: {{ $r->perlakuan?->waktu_rencana_perlakuan ?: '-' }}
                            &nbsp;|&nbsp;
                            PIC: {{ $r->perlakuan?->penanggung_jawab ?: '-' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-500 text-sm">
                Belum ada risiko yang terdaftar.
            </div>
        @endforelse
    </div>
</div>
