<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">
                {{ $isNew ? 'Ajukan Perubahan' : 'Permohonan Perubahan' }}
            </h1>
            <p class="text-sm text-muted mt-1">
                F1 (MPR01)
                @if(! $isNew)
                    &mdash; <span class="font-mono">{{ $perubahanModel->id_perubahan }}</span>
                @else
                    &mdash; ID dibuat otomatis saat disimpan
                @endif
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('mpr.perubahan.index', $konteks) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                Daftar Perubahan
            </a>
            @if(! $isNew)
                <a href="{{ route('mpr.perubahan.hub', [$konteks, $perubahanModel]) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                    Buka Hub
                </a>
            @endif
        </div>
    </div>

    {{-- Hasil resolver setelah simpan --}}
    @if(! $isNew && $hasilAlur !== [])
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info" id="hasil-alur">
            <p class="font-semibold mb-1">Formulir yang wajib diisi:</p>
            <p>{{ implode(', ', $hasilAlur) }}</p>
        </div>
    @endif

    {{-- Pengaman ubah klasifikasi/lingkup --}}
    @if($perluKonfirmasi)
        <div class="mb-6 rounded-lg bg-warning/10 border border-warning/30 px-4 py-4 text-sm" id="peringatan-ubah">
            <p class="font-semibold text-text-strong mb-1">Perubahan klasifikasi/lingkup diblokir</p>
            <p class="text-text mb-2">Formulir berikut sudah berisi data. Mengubah klasifikasi atau lingkup dapat mengubah formulir yang wajib diisi. Data tidak akan dihapus.</p>
            <ul class="list-disc ml-5 text-text mb-3">
                @foreach($formulirTerisi as $label)
                    <li>{{ $label }}</li>
                @endforeach
            </ul>
            <div class="flex gap-3">
                <button type="button" wire:click="save(true)" id="konfirmasi-ubah"
                    class="px-4 py-2 rounded-lg bg-warning text-white text-sm font-semibold cursor-pointer">Ya, tetap ubah</button>
                <button type="button" wire:click="$set('perluKonfirmasi', false)"
                    class="px-4 py-2 rounded-lg border border-border text-sm text-text cursor-pointer">Batal</button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form wire:submit="save" class="lg:col-span-2 rounded-xl border border-border bg-surface p-6 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Nama Layanan Digital <span class="text-danger">*</span></label>
                <input type="text" wire:model="nama_layanan_digital" maxlength="255" id="nama_layanan_digital" @disabled(!$isEditable)
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                @error('nama_layanan_digital') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tautkan ke Analisis Prioritas</label>
                <select wire:model="mpr_analisis_prioritas_id" id="mpr_analisis_prioritas_id" @disabled(!$isEditable)
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                    <option value="">— Tidak ditautkan —</option>
                    @foreach($prioritasList as $pr)
                        <option value="{{ $pr->id }}">{{ $pr->nama_layanan_prioritas }}{{ $pr->prioritas ? ' (Prioritas '.$pr->prioritas.')' : '' }} — {{ \Illuminate\Support\Str::limit($pr->kondisi_perlu_berubah, 50) }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-muted mt-1">Bila ditautkan, perubahan ini ditandai sebagai layanan prioritas.</p>
                @error('mpr_analisis_prioritas_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Detail Perubahan <span class="text-danger">*</span></label>
                <x-textarea-auto wire:model="detail_perubahan" rows="4" :disabled="!$isEditable" />
                @error('detail_perubahan') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Unit Pemohon</label>
                <input type="text" wire:model="unit_pemohon" maxlength="255" id="unit_pemohon" @disabled(!$isEditable)
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                @error('unit_pemohon') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Klasifikasi Perubahan <span class="text-danger">*</span></label>
                    <select wire:model.live="ref_klasifikasi_perubahan_id" id="ref_klasifikasi_perubahan_id" @disabled(!$isEditable)
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        <option value="">— Pilih —</option>
                        @foreach($klasifikasiRef as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('ref_klasifikasi_perubahan_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Lingkup Perubahan <span class="text-danger">*</span></label>
                    <select wire:model.live="ref_lingkup_perubahan_id" id="ref_lingkup_perubahan_id" @disabled(!$isEditable)
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        <option value="">— Pilih —</option>
                        @foreach($lingkupRef as $l)
                            <option value="{{ $l->id }}">{{ $l->nama }}</option>
                        @endforeach
                    </select>
                    @error('ref_lingkup_perubahan_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($isEditable)
                <div class="flex justify-end pt-4 border-t border-border">
                    <button type="submit" id="simpan-perubahan"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                        {{ $isNew ? 'Ajukan Perubahan' : 'Simpan Perubahan' }}
                    </button>
                </div>
            @else
                <div class="rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
                    Anda melihat data ini dalam mode <strong>read-only</strong>.
                </div>
            @endif
        </form>

        {{-- Panel info klasifikasi & lingkup --}}
        <aside class="space-y-4">
            <div class="rounded-xl border border-border bg-surface p-5">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Klasifikasi</p>
                @if($klasifikasiInfo)
                    <p class="text-sm font-semibold text-text-strong">{{ $klasifikasiInfo->nama }}</p>
                    <p class="text-sm text-text mt-1">{{ $klasifikasiInfo->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                @else
                    <p class="text-sm text-muted italic">Pilih klasifikasi untuk melihat deskripsinya.</p>
                @endif
            </div>
            <div class="rounded-xl border border-border bg-surface p-5">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Lingkup</p>
                @if($lingkupInfo)
                    <p class="text-sm font-semibold text-text-strong">{{ $lingkupInfo->nama }}</p>
                    <p class="text-sm text-text mt-1">{{ $lingkupInfo->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                @else
                    <p class="text-sm text-muted italic">Pilih lingkup untuk melihat deskripsinya.</p>
                @endif
            </div>
        </aside>
    </div>
</div>
