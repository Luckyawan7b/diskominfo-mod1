<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-strong tracking-tight">Formulir 2.0 - Daftar Layanan Digital Prioritas</h1>
            <p class="text-sm text-muted mt-1">Daftar layanan yang diidentifikasi sebagai prioritas dalam penilaian risiko SPBE.</p>
        </div>
    </div>

    {{-- Wizard Navigasi --}}

    {{-- Pesan Sukses --}}
    @if(session()->has('success'))
        <div class="p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Main Content --}}
    <div class="bg-surface border border-border rounded-2xl p-6 shadow-xl backdrop-blur-xl">
        @if(empty($items))
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-full bg-surface-soft flex items-center justify-center mx-auto mb-4 border border-border">
                    <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-text-strong mb-2">Belum Ada Layanan Digital Prioritas</h3>
                <p class="text-muted text-sm max-w-md mx-auto mb-6">
                    Layanan digital prioritas akan muncul di sini jika Anda telah menandai sebuah risiko dengan <strong>Layanan Prioritas</strong> = "Prioritas" pada bagian <strong>Kolom Tambahan</strong> di Formulir 1.0.
                </p>
                <a href="{{ route('risiko.index', $konteks) }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-surface-soft hover:bg-border border border-border text-text text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Daftar Risiko
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-muted uppercase bg-surface-soft border-b border-border">
                        <tr>
                            <th class="px-4 py-3 rounded-tl-xl w-12 text-center">No</th>
                            <th class="px-4 py-3">Layanan Prioritas & Risiko</th>
                            <th class="px-4 py-3 w-40">Perlu MKB?</th>
                            <th class="px-4 py-3 w-56">PIC</th>
                            <th class="px-4 py-3 w-56 rounded-tr-xl">Target Waktu Penyusunan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @php $no = 1; @endphp
                        @foreach($items as $id => $item)
                            <tr class="hover:bg-surface-soft transition-colors">
                                <td class="px-4 py-4 text-center text-muted font-medium">
                                    {{ $no++ }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-text-strong mb-1">{{ $item['layanan_prioritas'] ?: '-' }}</div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="inline-flex items-center rounded-md bg-surface-soft px-2 py-1 text-xs font-medium text-muted ring-1 ring-inset ring-border">
                                            {{ $item['kode_risiko'] }}
                                        </span>
                                        @if($item['besaran_risiko'])
                                            @php
                                                $rmc = app(\App\Services\RiskMatrixCalculator::class);
                                                $lbl = $rmc->label($item['besaran_risiko']);
                                                $cls = $rmc->colorClass($lbl) ?? 'bg-surface-soft text-muted border-border';
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-bold border {{ $cls }}">
                                                {{ $item['besaran_risiko'] }} - {{ $lbl }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <select wire:model="items.{{ $id }}.perlu_mkb" {{ !$isEditable ? 'disabled' : '' }}
                                        class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:ring-2 focus:ring-accent focus:outline-none disabled:opacity-50">
                                        <option value="0">Tidak</option>
                                        <option value="1">Ya</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <textarea wire:model="items.{{ $id }}.pic" rows="2" {{ !$isEditable ? 'disabled' : '' }}
                                        class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:ring-2 focus:ring-accent focus:outline-none disabled:opacity-50 resize-none"
                                        placeholder="Nama PIC"></textarea>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <textarea wire:model="items.{{ $id }}.target_waktu_penyusunan" rows="2" {{ !$isEditable ? 'disabled' : '' }}
                                        class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:ring-2 focus:ring-accent focus:outline-none disabled:opacity-50 resize-none"
                                        placeholder="Target Waktu"></textarea>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($isEditable)
                <div class="mt-6 flex justify-end">
                    <button wire:click="saveAll" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                        <svg wire:loading.remove wire:target="saveAll" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <svg wire:loading wire:target="saveAll" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Simpan Daftar Layanan Digital
                    </button>
                </div>
            @endif
        @endif
    </div>
</div>
