<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">
                Konteks Pengetahuan — Penilaian {{ $konteks->tahun_penilaian }} / Pelaksanaan {{ $konteks->tahun_pelaksanaan }}
            </h1>
            <p class="text-sm text-muted mt-1">
                {{ $konteks->layanan?->creator?->nama_dinas ?? 'Perangkat Daerah' }} &mdash; {{ $konteks->layanan?->nama_layanan ?? '-' }}
            </p>
        </div>
    </div>

    {{-- Info Card Identitas (read-only, bersumber dari relasi layanan) --}}
    <div class="rounded-xl border border-border bg-surface p-6 sm:p-8 mb-6">
        <h2 class="text-base font-semibold text-text-strong mb-4">Identitas Konteks</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Perangkat Daerah / Instansi</p>
                <p class="text-text-strong">{{ $konteks->layanan?->creator?->nama_dinas ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Nama Layanan</p>
                <p class="text-text-strong">{{ $konteks->layanan?->nama_layanan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Tahun Penilaian</p>
                <p class="text-text-strong">{{ $konteks->tahun_penilaian }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Tahun Pelaksanaan</p>
                <p class="text-text-strong">{{ $konteks->tahun_pelaksanaan }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Dibuat Oleh</p>
                <p class="text-text-strong">{{ $konteks->creator?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Tanggal Dibuat</p>
                <p class="text-text-strong">{{ $konteks->created_at?->translatedFormat('d F Y') ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Navigasi ke Sub-Modul MPN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Indikator Capaian --}}
        <a href="{{ route('mpn.indikator-capaian.form', $konteks) }}"
           class="group flex items-start gap-4 p-5 rounded-xl border border-border bg-surface hover:bg-surface-soft hover:border-border-strong transition-all">
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 group-hover:bg-primary/20 transition-colors">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-text-strong group-hover:text-primary transition-colors">Indikator Capaian</p>
                <p class="text-xs text-muted mt-0.5">Kelola indikator capaian & evaluasi realisasi</p>
                <span class="inline-block mt-2 text-[10px] text-muted bg-surface-raised border border-border rounded px-2 py-0.5">
                    Kelola indikator &amp; evaluasi capaian layanan
                </span>
            </div>
        </a>

        {{-- Daftar Pengetahuan --}}
        <a href="{{ route('mpn.pengetahuan.index', $konteks) }}"
           class="group flex items-start gap-4 p-5 rounded-xl border border-border bg-surface hover:bg-surface-soft hover:border-border-strong transition-all">
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 group-hover:bg-primary/20 transition-colors">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-text-strong group-hover:text-primary transition-colors">Daftar Pengetahuan</p>
                <p class="text-xs text-muted mt-0.5">Kelola inventarisasi & dokumentasi pengetahuan</p>
                <span class="inline-block mt-2 text-[10px] text-muted bg-surface-raised border border-border rounded px-2 py-0.5">
                    Kelola inventarisasi &amp; dokumentasi
                </span>
            </div>
        </a>
    </div>

    @if(!$isEditable)
        <div class="mt-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat konteks ini dalam mode <strong>read-only</strong>.
        </div>
    @endif
</div>
