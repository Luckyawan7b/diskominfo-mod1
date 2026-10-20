<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div class="min-w-0">
            <p class="text-xs font-mono text-muted">{{ $p->id_perubahan }}</p>
            <h1 class="text-2xl font-bold text-text-strong">{{ $p->nama_layanan_digital }}</h1>
            <p class="text-sm text-muted mt-1">
                {{ $p->klasifikasi?->nama ?? '-' }} &middot; {{ $p->lingkup?->nama ?? '-' }}
                @if($p->is_layanan_prioritas) &middot; <span class="text-warning font-medium">Layanan Prioritas</span> @endif
            </p>
        </div>
        <div class="flex gap-3 shrink-0">
            <a href="{{ route('mpr.perubahan.form', [$konteks, $p]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                {{ $isEditable ? 'Edit Permohonan (F1)' : 'Lihat Permohonan (F1)' }}
            </a>
            <a href="{{ route('mpr.perubahan.index', $konteks) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                Daftar Perubahan
            </a>
        </div>
    </div>

    @if(! $isEditable)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat perubahan ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    {{-- Stepper --}}
    <div class="rounded-xl border border-border bg-surface p-5 mb-6">
        <div class="flex items-center justify-between text-xs mb-2">
            <span class="font-semibold text-text-strong">Kelengkapan Formulir Wajib</span>
            <span class="text-muted">{{ $kelengkapan['terisi'] }}/{{ $kelengkapan['wajib'] }} terisi</span>
        </div>
        <div class="h-1.5 rounded-full bg-border mb-4">
            <div class="h-1.5 rounded-full bg-accent transition-all"
                 style="width: {{ $kelengkapan['wajib'] > 0 ? round($kelengkapan['terisi'] / $kelengkapan['wajib'] * 100) : 0 }}%"></div>
        </div>
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-2 text-[11px]" id="stepper-perubahan">
            <li class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-success-bg text-success border border-success/20 font-medium">F1 Permohonan</li>
            @foreach($formulir as $kunci => $f)
                <li class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border font-medium
                    {{ ! $f['wajib'] ? 'bg-surface-soft text-muted border-border opacity-60' : ($f['terisi'] ? 'bg-success-bg text-success border-success/20' : 'bg-surface-soft text-text border-border') }}">
                    {{ $f['label'] }}
                </li>
            @endforeach
        </ol>
    </div>

    {{-- Checklist --}}
    <div class="rounded-xl border border-border bg-surface overflow-hidden" id="checklist-perubahan">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-base font-semibold text-text-strong">Checklist Formulir</h2>
            <p class="text-xs text-muted mt-0.5">Formulir yang tidak wajib menurut klasifikasi dan lingkup tetap ditampilkan (nonaktif) agar alurnya jelas.</p>
        </div>
        <ul class="divide-y divide-border">
            @foreach($formulir as $kunci => $f)
                @php
                    $url = ! empty($f['route'])
                        ? route($f['route'], array_filter([$konteks, $p, 'tab' => $f['tab'] ?? null]))
                        : null;
                @endphp
                <li class="flex items-center justify-between gap-4 px-5 py-3 {{ ! $f['wajib'] ? 'opacity-60' : '' }}" data-formulir="{{ $kunci }}">
                    <div>
                        @if($url)
                            <a href="{{ $url }}" class="text-sm font-medium text-accent hover:text-primary hover:underline transition-colors">
                                {{ $f['label'] }}
                            </a>
                        @else
                            <p class="text-sm font-medium text-text-strong">{{ $f['label'] }}</p>
                        @endif
                        @if(! $f['wajib'])
                            <p class="text-xs text-muted">Tidak wajib untuk klasifikasi dan lingkup ini.</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $f['wajib'] ? 'bg-primary/20 text-primary' : 'bg-surface-soft text-muted' }}">
                            {{ $f['wajib'] ? 'Wajib' : 'Tidak wajib' }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $f['terisi'] ? 'bg-success-bg text-success' : 'bg-surface-soft text-muted' }}">
                            {{ $f['terisi'] ? 'Terisi' : 'Belum terisi' }}
                        </span>
                        @if($url)
                            <a href="{{ $url }}" class="ml-1 text-xs font-medium text-accent hover:text-primary transition-colors">
                                {{ $isEditable && $f['wajib'] ? 'Isi' : 'Lihat' }} &rarr;
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
