<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">
                Konteks Perubahan — Penilaian {{ $konteks->tahun_penilaian }}
            </h1>
            <p class="text-sm text-muted mt-1">
                Ringkasan Konteks &mdash; {{ $konteks->nama_instansi ?: ($konteks->layanan?->creator?->nama_dinas ?? 'Perangkat Daerah') }} &mdash; {{ $konteks->layanan?->nama_layanan ?? '-' }}
            </p>
        </div>
    </div>

    {{-- Info Card Identitas (read-only) --}}
    <div class="rounded-xl border border-border bg-surface p-6 sm:p-8 mb-6">
        <h2 class="text-base font-semibold text-text-strong mb-4">Identitas Konteks</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Perangkat Daerah / Instansi</p>
                <p class="text-text-strong">{{ $konteks->nama_instansi ?: ($konteks->layanan?->creator?->nama_dinas ?? '-') }}</p>
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
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Periode Perencanaan</p>
                <p class="text-text-strong">{{ $konteks->periode_perencanaan ?? '-' }}</p>
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

    {{-- Navigasi ke F0 --}}
    <a href="{{ route('mpr.analisis-prioritas.form', $konteks) }}"
       class="group flex items-start gap-4 p-5 rounded-xl border border-border bg-surface hover:bg-surface-soft hover:border-border-strong transition-all mb-6">
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 group-hover:bg-primary/20 transition-colors">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-text-strong group-hover:text-primary transition-colors">Analisis Prioritas (F0)</p>
            <p class="text-xs text-muted mt-0.5">Kondisi perlu berubah, as-is, to-be, prioritas, aspek &amp; indikator PEMDI</p>
            <span class="inline-block mt-2 text-[10px] text-muted bg-surface-raised border border-border rounded px-2 py-0.5">
                {{ $jumlahPrioritas }} analisis tercatat
            </span>
        </div>
    </a>

    {{-- Daftar Perubahan --}}
    <div class="rounded-xl border border-border bg-surface overflow-hidden">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
            <h2 class="text-base font-semibold text-text-strong">Daftar Perubahan</h2>
            <a href="{{ route('mpr.perubahan.index', $konteks) }}" class="text-sm text-accent hover:text-primary font-medium">Kelola semua &rarr;</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">ID</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Layanan Digital</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Klasifikasi</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Lingkup</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($perubahanList as $p)
                    <tr class="hover:bg-surface-soft transition-colors">
                        <td class="px-5 py-3 font-mono text-xs"><a href="{{ route('mpr.perubahan.hub', [$konteks, $p]) }}" class="text-accent hover:text-primary font-medium">{{ $p->id_perubahan }}</a></td>
                        <td class="px-5 py-3 text-text">{{ $p->nama_layanan_digital }}</td>
                        <td class="px-5 py-3 text-text">{{ $p->klasifikasi?->nama ?? '-' }}</td>
                        <td class="px-5 py-3 text-text">{{ $p->lingkup?->nama ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-muted text-sm">Belum ada permohonan perubahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(! $isEditable)
        <div class="mt-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat konteks ini dalam mode <strong>read-only</strong>.
        </div>
    @endif
</div>
