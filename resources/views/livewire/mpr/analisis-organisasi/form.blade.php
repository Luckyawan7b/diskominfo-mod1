<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Analisis Dampak Organisasi (F2B)</h1>
            <p class="text-sm text-muted mt-1">MPR02B: ukuran kompleksitas pada 4 area dampak organisasi, indikator terdampak (Tabel 2c), dan kesimpulan otomatis</p>
        </div>
        <a href="{{ route('mpr.perubahan.hub', [$konteks, $perubahan]) }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
            Hub Perubahan
        </a>
    </div>

    @if(! $wajib)
        <div class="mb-6 rounded-lg bg-warning/10 border border-warning/30 px-4 py-3 text-sm text-text" id="banner-tidak-wajib">
            Formulir Analisis Organisasi (2B) <strong>tidak wajib</strong> untuk klasifikasi ({{ $perubahan->klasifikasi?->nama ?? '-' }}) dan lingkup ({{ $perubahan->lingkup?->nama ?? '-' }}) perubahan ini. Halaman ditampilkan dalam mode nonaktif agar alurnya tetap jelas.
        </div>
    @elseif($isAdmin)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat data ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    {{-- Panel kesimpulan otomatis setelah simpan --}}
    @if($kesimpulanNama)
        <div class="mb-6 rounded-xl border border-info/30 bg-info/10 p-5" id="panel-kesimpulan">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-info">Kesimpulan Ukuran Kompleksitas Perubahan Organisasi</span>
                <span class="px-3 py-1 rounded-full bg-primary/20 text-primary text-xs font-bold">{{ $kesimpulanNama }}</span>
            </div>
            @if($rekomendasi)
                <p class="text-sm text-text">{{ $rekomendasi }}</p>
            @endif
        </div>
    @endif

    {{-- Panduan Ukuran Kompleksitas (Tabel 2d) --}}
    <div class="mb-6 rounded-xl border border-border bg-surface p-4">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Acuan Ukuran Kompleksitas (Tabel 2d — arahkan kursor untuk deskripsi lengkap)</p>
        <div class="flex flex-wrap gap-2">
            @foreach($ukuranList as $uk)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-surface-soft text-xs text-text cursor-help"
                      title="{{ $uk->definisi }}">
                    <strong class="text-text-strong">{{ $uk->nama }}</strong>
                    @if($uk->definisi)
                        <span class="text-muted hidden md:inline">&mdash; {{ \Illuminate\Support\Str::limit($uk->definisi, 55) }}</span>
                    @endif
                </span>
            @endforeach
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($areaRefs as $area)
                @php
                    $selectedUkuranId = $areas[$area->id]['ref_ukuran_kompleksitas_id'] ?? null;
                    $selectedUkuran = $selectedUkuranId ? $ukuranList->firstWhere('id', (int) $selectedUkuranId) : null;
                @endphp
                <div class="rounded-xl border border-border bg-surface p-6 flex flex-col justify-between" wire:key="area-org-{{ $area->id }}" data-area-dampak="{{ $area->id }}">
                    <div>
                        <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-border">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-primary text-xs font-bold shrink-0">{{ $loop->iteration }}</span>
                            <h2 class="text-base font-bold text-text-strong">{{ $area->nama }}</h2>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                                Ukuran Kompleksitas
                            </label>
                            <select wire:model.live="areas.{{ $area->id }}.ref_ukuran_kompleksitas_id"
                                id="ukuran-area-{{ $area->id }}"
                                title="{{ $selectedUkuran?->definisi }}"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                                <option value="">— Pilih Ukuran Kompleksitas —</option>
                                @foreach($ukuranList as $uk)
                                    <option value="{{ $uk->id }}" title="{{ $uk->definisi }}">{{ $uk->nama }}</option>
                                @endforeach
                            </select>
                            @if($selectedUkuran?->definisi)
                                <p class="text-[11px] text-muted mt-1.5">{{ $selectedUkuran->definisi }}</p>
                            @endif
                            @error("areas.{$area->id}.ref_ukuran_kompleksitas_id")
                                <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-text uppercase tracking-wider mb-2">Indikator Area Dampak (Tabel 2c)</p>
                            <div class="space-y-1.5 rounded-xl border border-border bg-surface-soft p-3 max-h-64 overflow-y-auto">
                                @forelse($area->indikator as $ind)
                                    <label class="flex items-start gap-2 text-sm text-text" wire:key="ind-org-{{ $area->id }}-{{ $ind->id }}">
                                        <input type="checkbox" value="{{ $ind->id }}"
                                            wire:model="areas.{{ $area->id }}.indikator_ids"
                                            @disabled(!$isEditable)
                                            class="mt-0.5 rounded border-border">
                                        <span>{{ $ind->teks }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-muted italic">Tidak ada indikator referensi untuk area ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($isEditable)
            <div class="flex justify-end pt-4 border-t border-border">
                <button type="submit" id="simpan-analisis-organisasi"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                    Simpan Analisis Organisasi
                </button>
            </div>
        @endif
    </form>
</div>
