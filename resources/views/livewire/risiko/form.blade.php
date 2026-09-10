<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">
                {{ $isNew ? 'Tambah Risiko Baru' : 'Edit Risiko — ' . $kode_risiko }}
            </h1>
            <p class="text-sm text-slate-400 mt-1">Formulir 5-7 & Kolom Tambahan SPBE</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('risiko.index', $konteks) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-600 text-sm text-slate-300 hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Daftar Risiko
            </a>
            @if($isEditable)
                <button wire:click="save" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-700 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Risiko
                </button>
            @endif
        </div>
    </div>


    {{-- Tabs Header --}}
    <div class="flex border-b border-slate-700/50 space-x-2 overflow-x-auto mb-6">
        @php
            $tabs = [
                1 => ['label' => '1. Identifikasi (F5)', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                2 => ['label' => '2. Analisis & Evaluasi (F6)', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                3 => ['label' => '3. Perlakuan Risiko (F7)', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                4 => ['label' => '4. Risiko Residual', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                5 => ['label' => '5. Kolom Tambahan (E)', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <button wire:click="$set('activeTab', {{ $key }})"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors cursor-pointer {{ $activeTab === $key ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/></svg>
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Tabs Content --}}
    <div class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-6 sm:p-8">

        {{-- TAB 1: IDENTIFIKASI --}}
        <div class="{{ $activeTab === 1 ? 'block' : 'hidden' }} space-y-6">
            {{-- 1. No & 5. Kode Risiko --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">No <span class="text-xs text-slate-500 font-normal ml-2">Diisi sesuai nomor urut</span></label>
                    <input type="text" value="{{ $no }}" disabled
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-slate-400 font-mono text-sm disabled:opacity-75 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Kode Risiko <span class="text-xs text-slate-500 font-normal ml-2">Diisi kode risiko terkait</span></label>
                    <input wire:model="kode_risiko" type="text" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white font-mono text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                    @error('kode_risiko') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 3. Sasaran UPR & 2. Sasaran Pembangunan Nasional --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Sasaran UPR <span class="text-xs text-slate-500 font-normal ml-2">Diisi sesuai sasaran UPR</span></label>
                    
                    <div x-data="{ open: false }" class="relative" wire:key="select-sasaran-upr-{{ $mr_sasaran_upr_id }}">
                        @php
                            $selectedSasaran = $sasaranList->firstWhere('id', $mr_sasaran_upr_id);
                        @endphp

                        <button type="button" 
                            @click="open = !open" 
                            {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full min-h-[44px] rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-left text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50 flex items-center justify-between gap-2 transition-all cursor-pointer disabled:cursor-not-allowed">
                            <span class="{{ $selectedSasaran ? 'text-white font-medium' : 'text-slate-400' }} break-words leading-relaxed">
                                {{ $selectedSasaran ? $selectedSasaran->sasaran_upr : '-- Pilih Sasaran UPR --' }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" 
                            @click.outside="open = false" 
                            x-cloak 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-1.5 w-full rounded-xl border border-slate-600 bg-slate-800 shadow-2xl overflow-hidden max-h-72 overflow-y-auto divide-y divide-slate-700/50">
                            <button type="button" 
                                @click="$wire.set('mr_sasaran_upr_id', null); open = false"
                                class="w-full text-left px-4 py-3 text-xs text-slate-400 hover:bg-slate-700/50 transition-colors cursor-pointer">
                                -- Kosongkan Pilihan --
                            </button>
                            @forelse($sasaranList as $s)
                                <button type="button" 
                                    @click="$wire.set('mr_sasaran_upr_id', {{ $s->id }}); open = false"
                                    class="w-full text-left px-4 py-3 text-sm transition-colors flex items-start justify-between gap-3 hover:bg-slate-700/70 cursor-pointer {{ $mr_sasaran_upr_id == $s->id ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-200' }}">
                                    <span class="break-words leading-relaxed">{{ $s->sasaran_upr }}</span>
                                    @if($mr_sasaran_upr_id == $s->id)
                                        <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            @empty
                                <div class="px-4 py-3 text-xs text-slate-400 italic">
                                    Belum ada Sasaran UPR yang dibuat. Silakan tambahkan pada tahap Sasaran UPR terlebih dahulu.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Sasaran Pembangunan Nasional <span class="text-xs text-slate-500 font-normal ml-2">Otomatis dari Sasaran UPR</span></label>
                    <div class="w-full min-h-[44px] rounded-lg border border-slate-600 bg-slate-700/30 px-4 py-2.5 text-sm flex items-center"
                        wire:key="sasaran-nasional-preview-{{ $mr_sasaran_upr_id }}">
                        <span class="{{ filled($sasaranNasionalText) ? 'text-slate-200' : 'text-slate-400 italic' }} break-words leading-relaxed">
                            @if(empty($mr_sasaran_upr_id))
                                -- Otomatis terisi setelah Sasaran UPR dipilih --
                            @elseif(filled($sasaranNasionalText))
                                {{ $sasaranNasionalText }}
                            @else
                                (Tidak ada Sasaran Pembangunan Nasional terkait pada Sasaran UPR ini)
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- 4. Indikator Kinerja --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Indikator Kinerja <span class="text-xs text-slate-500 font-normal ml-2">Diisi indikator kinerja berdasarkan formulir 0.0</span></label>
                
                <div x-data="{ open: false }" class="relative" wire:key="select-indikator-{{ $mr_sasaran_upr_id }}-{{ $indikator_kinerja_snapshot }}">
                    <button type="button" 
                        @click="open = !open" 
                        {{ !$isEditable || empty($mr_sasaran_upr_id) ? 'disabled' : '' }}
                        class="w-full min-h-[44px] rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-left text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50 flex items-center justify-between gap-2 transition-all cursor-pointer disabled:cursor-not-allowed">
                        <span class="{{ filled($indikator_kinerja_snapshot) ? 'text-white font-medium' : 'text-slate-400' }} break-words leading-relaxed">
                            @if(empty($mr_sasaran_upr_id))
                                -- Pilih Sasaran UPR terlebih dahulu --
                            @elseif(filled($indikator_kinerja_snapshot))
                                {{ $indikator_kinerja_snapshot }}
                            @else
                                -- Pilih Indikator Kinerja --
                            @endif
                        </span>
                        <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    @if(!empty($mr_sasaran_upr_id))
                        <div x-show="open" 
                            @click.outside="open = false" 
                            x-cloak 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-1.5 w-full rounded-xl border border-slate-600 bg-slate-800 shadow-2xl overflow-hidden max-h-72 overflow-y-auto divide-y divide-slate-700/50">
                            <button type="button" 
                                @click="$wire.set('indikator_kinerja_snapshot', null); open = false"
                                class="w-full text-left px-4 py-3 text-xs text-slate-400 hover:bg-slate-700/50 transition-colors cursor-pointer">
                                -- Kosongkan Pilihan --
                            </button>
                            @forelse($indikatorList as $ind)
                                <button type="button" 
                                    @click="$wire.set('indikator_kinerja_snapshot', @js($ind->indikator_kinerja)); open = false"
                                    class="w-full text-left px-4 py-3 text-sm transition-colors flex items-start justify-between gap-3 hover:bg-slate-700/70 cursor-pointer {{ $indikator_kinerja_snapshot === $ind->indikator_kinerja ? 'bg-emerald-500/10 text-emerald-400 font-semibold' : 'text-slate-200' }}">
                                    <div class="space-y-0.5">
                                        <div class="break-words leading-relaxed">{{ $ind->indikator_kinerja }}</div>
                                        @if(filled($ind->target_kinerja))
                                            <div class="text-xs text-emerald-400 font-normal">Target: {{ $ind->target_kinerja }}</div>
                                        @endif
                                    </div>
                                    @if($indikator_kinerja_snapshot === $ind->indikator_kinerja)
                                        <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            @empty
                                <div class="px-4 py-3 text-xs text-slate-400 italic">
                                    Sasaran UPR ini belum memiliki indikator kinerja. Anda dapat menambahkannya di tahap Sasaran UPR.
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

            {{-- 6. Peristiwa Risiko --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Peristiwa Risiko <span class="text-xs text-slate-500 font-normal ml-2">Diisi peristiwa risiko yang mungkin terjadi</span></label>
                <x-textarea-auto wire:model="peristiwa_risiko" rows="3" :disabled="!$isEditable"
                    placeholder="Deskripsi kejadian atau peristiwa risiko" />
                @error('peristiwa_risiko') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-4">
                <button type="button" wire:click="$set('activeTab', 2)" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm text-white transition-colors cursor-pointer">
                    Lanjut ke Analisis (F6)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- TAB 2: ANALISIS & EVALUASI --}}
        <div class="{{ $activeTab === 2 ? 'block' : 'hidden' }} space-y-6">
            {{-- 7. Kategori & 10. Area Dampak --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Kategori Risiko <span class="text-xs text-slate-500 font-normal ml-2">Ketik bebas, contoh: Risiko Operasional, Risiko Keamanan Informasi</span></label>
                    <input wire:model="kategori_risiko" type="text" list="kategori-risiko-list"
                        {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50"
                        placeholder="Masukkan kategori risiko...">
                    {{-- datalist dari histori kategori yang pernah dipakai (UX helper, bukan constraint) --}}
                    <datalist id="kategori-risiko-list">
                        @foreach($kategoriSuggestions as $saran)
                            <option value="{{ $saran }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Area Dampak <span class="text-xs text-slate-500 font-normal ml-2">Diisi area dampak risiko tersebut</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Penurunan Reputasi', 'Keuangan', 'Gangguan Terhadap Layanan Organisasi', 'Penurunan Kinerja'] as $area)
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-700 bg-slate-700/30 text-sm text-slate-300 cursor-pointer hover:bg-slate-700/50">
                                <input type="radio" wire:model="area_dampak" value="{{ $area }}" {{ !$isEditable ? 'disabled' : '' }} class="text-emerald-500 focus:ring-emerald-500">
                                <span>{{ $area }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 8. Penyebab & 9. Dampak --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Penyebab / Akar Masalah <span class="text-xs text-slate-500 font-normal ml-2">Diisi penyebab terjadinya risiko</span></label>
                    <x-textarea-auto wire:model="penyebab" rows="3" :disabled="!$isEditable"
                        placeholder="Faktor penyebab timbulnya risiko" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Dampak Risiko <span class="text-xs text-slate-500 font-normal ml-2">Diisi dampak risiko tersebut</span></label>
                    <x-textarea-auto wire:model="dampak" rows="3" :disabled="!$isEditable"
                        placeholder="Akibat yang ditimbulkan jika risiko terjadi" />
                </div>
            </div>

            {{-- 11. Level Kemungkinan & 12. Level Dampak --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-700/50">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Level Kemungkinan <span class="text-xs text-slate-500 font-normal ml-2">Diisi level kemungkinan terjadinya risiko</span></label>
                    <select wire:model.live="level_kemungkinan" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                        <option value="">-- Pilih Level Kemungkinan --</option>
                        <option value="1">1 - Hampir Tidak Terjadi</option>
                        <option value="2">2 - Jarang Terjadi</option>
                        <option value="3">3 - Kadang Terjadi</option>
                        <option value="4">4 - Sering Terjadi</option>
                        <option value="5">5 - Hampir Pasti Terjadi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Level Dampak <span class="text-xs text-slate-500 font-normal ml-2">Diisi level dampak terjadinya risiko</span></label>
                    <select wire:model.live="level_dampak" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                        <option value="">-- Pilih Level Dampak --</option>
                        <option value="1">1 - Tidak Signifikan</option>
                        <option value="2">2 - Kecil / Minor</option>
                        <option value="3">3 - Sedang / Moderat</option>
                        <option value="4">4 - Besar / Signifikan</option>
                        <option value="5">5 - Sangat Besar / Katastropik</option>
                    </select>
                </div>
            </div>

            {{-- Hasil Analisis Visual --}}
            <div class="p-6 rounded-xl border border-slate-700 bg-slate-900/50 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Hasil Perhitungan Besaran Risiko</h3>
                    <p class="text-xs text-slate-500 mt-1">Dihitung otomatis berdasarkan Matriks SPBE Resmi</p>
                    <div class="mt-4 flex items-center gap-4">
                        <div>
                            <span class="text-xs text-slate-400">Besaran Risiko:</span>
                            <div class="text-3xl font-bold text-white">{{ $besaran ?? '-' }}</div>
                        </div>
                        @if($besaranLabel)
                            @php
                                $badgeCls = match($besaranLabel) {
                                    'Rendah'        => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                    'Sedang'        => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                    'Tinggi'        => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
                                    'Sangat Tinggi' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                    default         => 'bg-slate-700 text-slate-400 border-slate-600',
                                };
                            @endphp
                            <div class="px-3 py-1.5 rounded-full border text-sm font-semibold {{ $badgeCls }}">
                                Level: {{ $besaranLabel }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Mini 5x5 Matrix Preview --}}
                <div class="bg-slate-800 p-3 rounded-lg border border-slate-700">
                    <p class="text-xs text-slate-400 text-center mb-2 font-medium">Posisi Matriks 5×5</p>
                    <div class="grid grid-cols-5 gap-1 w-32 h-32">
                        @for($k = 5; $k >= 1; $k--)
                            @for($d = 1; $d <= 5; $d++)
                                @php
                                    $isSelected = ($level_kemungkinan == $k && $level_dampak == $d);
                                    $cellCalc = app(\App\Services\RiskMatrixCalculator::class)->calculate($k, $d);
                                    $cellColor = match(true) {
                                        $cellCalc <= 4  => 'bg-emerald-600/40',
                                        $cellCalc <= 9  => 'bg-amber-600/40',
                                        $cellCalc <= 16 => 'bg-orange-600/40',
                                        default         => 'bg-red-600/40',
                                    };
                                @endphp
                                <div class="rounded-xs {{ $cellColor }} {{ $isSelected ? 'ring-2 ring-white scale-110 z-10' : 'opacity-60' }} flex items-center justify-center text-[9px] text-white">
                                    {{ $isSelected ? '★' : '' }}
                                </div>
                            @endfor
                        @endfor
                    </div>
                </div>
            </div>

            <div class="flex justify-between pt-4">
                <button type="button" wire:click="$set('activeTab', 1)" class="px-4 py-2 rounded-lg border border-slate-600 text-sm text-slate-300 hover:bg-slate-700 cursor-pointer">← Kembali</button>
                <button type="button" wire:click="$set('activeTab', 3)" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm text-white cursor-pointer">Lanjut ke Perlakuan →</button>
            </div>
        </div>

        {{-- TAB 3: PERLAKUAN RISIKO --}}
        <div class="{{ $activeTab === 3 ? 'block' : 'hidden' }} space-y-6">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                    Keputusan Perlakuan Risiko <span class="text-xs text-slate-500 font-normal ml-2">Diisi perlakuan risiko dari kejadian risiko terkait</span>
                </label>
                <select wire:model="keputusan_perlakuan" {{ !$isEditable ? 'disabled' : '' }}
                    class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                    <option value="">-- Pilih Keputusan Perlakuan Risiko --</option>
                    @foreach(['Mengurangi risiko', 'Menghindari risiko', 'Membagi risiko', 'Menerima risiko'] as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                    Deskripsi Detail Perlakuan Risiko <span class="text-xs text-slate-500 font-normal ml-2">Diisi detail deskripsi perlakuan risiko tersebut</span>
                </label>
                <x-textarea-auto wire:model="deskripsi_detail_perlakuan" rows="3" :disabled="!$isEditable"
                    placeholder="Langkah-langkah detail penanganan / mitigasi risiko yang akan dijalankan" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Waktu Rencana Perlakuan Risiko <span class="text-xs text-slate-500 font-normal ml-2">Diisi waktu perencanaan perlakuan risiko</span>
                    </label>
                    <input wire:model="waktu_rencana_perlakuan" type="text" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50"
                        placeholder="Contoh: Triwulan II / Juni 2026">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Penanggung Jawab <span class="text-xs text-slate-500 font-normal ml-2">Diisi penanggung jawab perlakuan kejadian risiko</span>
                    </label>
                    <input wire:model="penanggung_jawab" type="text" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50"
                        placeholder="Nama seksi / unit / pejabat penanggung jawab">
                </div>
            </div>

            <div class="flex justify-between pt-4">
                <button type="button" wire:click="$set('activeTab', 2)" class="px-4 py-2 rounded-lg border border-slate-600 text-sm text-slate-300 hover:bg-slate-700 cursor-pointer">← Kembali</button>
                <button type="button" wire:click="$set('activeTab', 4)" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm text-white cursor-pointer">Lanjut ke Residual →</button>
            </div>
        </div>

        {{-- TAB 4: RISIKO RESIDUAL --}}
        <div class="{{ $activeTab === 4 ? 'block' : 'hidden' }} space-y-6">
            <p class="text-sm text-slate-400">Estimasi tingkat risiko yang tersisa setelah rencana perlakuan diterapkan.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Level Kemungkinan Residual (1-5)</label>
                    <select wire:model.live="level_kemungkinan_residual" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                        <option value="">-- Pilih Level --</option>
                        <option value="1">1 - Hampir Tidak Terjadi</option>
                        <option value="2">2 - Jarang Terjadi</option>
                        <option value="3">3 - Kadang Terjadi</option>
                        <option value="4">4 - Sering Terjadi</option>
                        <option value="5">5 - Hampir Pasti Terjadi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Level Dampak Residual (1-5)</label>
                    <select wire:model.live="level_dampak_residual" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                        <option value="">-- Pilih Level --</option>
                        <option value="1">1 - Tidak Signifikan</option>
                        <option value="2">2 - Kecil / Minor</option>
                        <option value="3">3 - Sedang / Moderat</option>
                        <option value="4">4 - Besar / Signifikan</option>
                        <option value="5">5 - Sangat Besar / Katastropik</option>
                    </select>
                </div>
            </div>

            <div class="p-5 rounded-xl border border-slate-700 bg-slate-900/40 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-400">Besaran Risiko Residual:</span>
                    <div class="text-2xl font-bold text-white">{{ $besaranResidual ?? '-' }}</div>
                </div>
                @if($besaranResidual)
                    @php
                        $resLabel = app(\App\Services\RiskMatrixCalculator::class)->label($besaranResidual);
                        $resBadgeCls = match($resLabel) {
                            'Rendah'        => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                            'Sedang'        => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                            'Tinggi'        => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
                            'Sangat Tinggi' => 'bg-red-500/20 text-red-400 border-red-500/30',
                            default         => 'bg-slate-700 text-slate-400',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full border text-xs font-semibold {{ $resBadgeCls }}">
                        {{ $resLabel }}
                    </span>
                @endif
            </div>

            <div class="flex justify-between pt-4">
                <button type="button" wire:click="$set('activeTab', 3)" class="px-4 py-2 rounded-lg border border-slate-600 text-sm text-slate-300 hover:bg-slate-700 cursor-pointer">← Kembali</button>
                <button type="button" wire:click="$set('activeTab', 5)" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm text-white cursor-pointer">Lanjut ke Kolom Tambahan →</button>
            </div>
        </div>

        {{-- TAB 5: KOLOM TAMBAHAN (BAGIAN E) --}}
        <div class="{{ $activeTab === 5 ? 'block' : 'hidden' }} space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Layanan Pendukung <span class="text-xs text-slate-500 font-normal ml-2">Diisi jenis layanan apa yang mendukung kejadian risiko tersebut</span>
                    </label>
                    <input wire:model="layanan_pendukung" type="text" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50"
                        placeholder="Contoh: Jaringan internet desa, server/pusat data, aplikasi pelayanan internal">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Layanan Prioritas <span class="text-xs text-slate-500 font-normal ml-2">Diisi apakah termasuk layanan prioritas atau tidak</span>
                    </label>
                    <select wire:model.live="layanan_prioritas" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                        <option value="Instansional">Instansional (Biasa)</option>
                        <option value="Tematik">Tematik</option>
                        <option value="Prioritas">Prioritas (Layanan Digital SPBE)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Pemilik Layanan <span class="text-xs text-slate-500 font-normal ml-2">Diisi apakah layanan milik pusat, instansi lain, atau milik sendiri</span>
                    </label>
                    <select wire:model="pemilik_layanan" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                        <option value="">-- Pilih Pemilik Layanan --</option>
                        <option value="Milik sendiri">Milik sendiri (Desa)</option>
                        <option value="Pusat">Pusat (Kementerian/Lembaga)</option>
                        <option value="Instansi lain">Instansi lain (Pemda/OPD Lain)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Strategis atau Operasional <span class="text-xs text-slate-500 font-normal ml-2">Diisi apakah risiko termasuk risiko strategis atau operasional</span>
                    </label>
                    <select wire:model="strategis_atau_operasional" {{ !$isEditable ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none disabled:opacity-50">
                        <option value="Operasional">Operasional</option>
                        <option value="Strategis">Strategis</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-700/50">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Lintas Sektor <span class="text-xs text-slate-500 font-normal ml-2">Diisi apakah kejadian risiko termasuk lintas sektor</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-700 bg-slate-700/20 text-sm text-slate-300 cursor-pointer hover:bg-slate-700/30">
                            <input type="checkbox" wire:model="lintas_sektor" {{ !$isEditable ? 'disabled' : '' }} class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500">
                            <span>Layanan Lintas Sektor (Ya)</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Membutuhkan Manajemen Perubahan <span class="text-xs text-slate-500 font-normal ml-2">Diisi apakah membutuhkan perubahan atau tidak</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-700 bg-slate-700/20 text-sm text-slate-300 cursor-pointer hover:bg-slate-700/30">
                            <input type="checkbox" wire:model="membutuhkan_perubahan" {{ !$isEditable ? 'disabled' : '' }} class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500">
                            <span>Membutuhkan Manajemen Perubahan (Ya)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        IPPD Terkait <span class="text-xs text-slate-500 font-normal ml-2">Diisi IPPD yang berkaitan dengan kejadian risiko</span>
                    </label>
                    <x-textarea-auto wire:model="ippd_terkait" rows="4" :disabled="!$isEditable"
                        placeholder="Contoh: Seluruh Perangkat Daerah / Instansi pengguna layanan TIK desa..." />
                </div>
            </div>

            {{-- 29. Risiko Residual --}}
            <div class="pt-4 border-t border-slate-700/50">
                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                    Risiko Residual <span class="text-xs text-slate-500 font-normal ml-2">Diisi jenis risiko residual yang mungkin muncul setelah dilakukan perlakuan risiko</span>
                </label>
                <x-textarea-auto wire:model="keterangan_residual" rows="3" :disabled="!$isEditable"
                    placeholder="Contoh: Penumpukan antrean layanan sementara selama masa pemeliharaan sistem..." />
            </div>

            {{-- Layanan Digital conditional fields --}}
            @if($layanan_prioritas === 'Prioritas')
                <div class="p-5 rounded-xl border border-emerald-500/30 bg-emerald-950/20">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <h3 class="text-sm font-semibold text-emerald-400">Layanan Digital Prioritas (Formulir Khusus)</h3>
                            <p class="text-xs text-slate-300 mt-1">
                                Risiko ini akan muncul di <strong>Formulir 2.0 — Daftar Layanan Digital Prioritas</strong>.
                                Silakan lengkapi data MKB, PIC, dan Target Waktu Penyusunan pada formulir tersebut.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex justify-between pt-4">
                <button type="button" wire:click="$set('activeTab', 4)" class="px-4 py-2 rounded-lg border border-slate-600 text-sm text-slate-300 hover:bg-slate-700 cursor-pointer">← Kembali</button>
                @if($isEditable)
                    <button type="button" wire:click="save" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-700 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Semua Data Risiko
                    </button>
                @endif
            </div>
        </div>

    </div>
</div>
