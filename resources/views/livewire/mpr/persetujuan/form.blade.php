<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Persetujuan Perubahan</h1>
            <p class="text-sm text-muted mt-1">Pencatatan keputusan persetujuan, PIC penyetuju, tanggal, dan catatan per tahap</p>
        </div>
        <a href="{{ route('mpr.perubahan.hub', [$konteks, $perubahan]) }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
            Hub Perubahan
        </a>
    </div>

    @if(! $wajib)
        <div class="mb-6 rounded-lg bg-warning/10 border border-warning/30 px-4 py-3 text-sm text-text" id="banner-tidak-wajib">
            Formulir Persetujuan <strong>tidak wajib</strong> untuk klasifikasi ({{ $perubahan->klasifikasi?->nama ?? '-' }}) dan lingkup ({{ $perubahan->lingkup?->nama ?? '-' }}) perubahan ini. Halaman ditampilkan dalam mode nonaktif agar alurnya tetap jelas.
        </div>
    @elseif($isAdmin)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat data ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        @foreach($tahapMeta as $tahap => $judul)
            @php
                $relevan = $tahapRelevan[$tahap] ?? false;
                $dapatDiisi = $isEditable && $relevan;
            @endphp
            <div class="rounded-xl border border-border bg-surface p-6 {{ ! $relevan ? 'opacity-60' : '' }}"
                 wire:key="persetujuan-{{ $tahap }}"
                 data-tahap="{{ $tahap }}">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4 pb-3 border-b border-border">
                    <h2 class="text-base font-bold text-text-strong">{{ $judul }}</h2>
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold {{ $relevan ? 'bg-primary/20 text-primary' : 'bg-surface-soft text-muted' }}">
                        {{ $relevan ? 'Wajib' : 'Tidak wajib untuk perubahan ini' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Status Persetujuan</label>
                        <select wire:model="items.{{ $tahap }}.status" id="status-{{ $tahap }}" @disabled(!$dapatDiisi)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            <option value="">— Pilih Status —</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Tidak Disetujui">Tidak Disetujui</option>
                        </select>
                        @error("items.$tahap.status")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">PIC Penyetuju</label>
                        <input type="text" wire:model="items.{{ $tahap }}.pic_penyetuju" maxlength="255"
                            id="pic-{{ $tahap }}" @disabled(!$dapatDiisi)
                            placeholder="Nama / Jabatan penyetuju"
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        @error("items.$tahap.pic_penyetuju")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Persetujuan</label>
                        <input type="date" wire:model="items.{{ $tahap }}.tanggal"
                            id="tanggal-{{ $tahap }}" @disabled(!$dapatDiisi)
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        @error("items.$tahap.tanggal")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Catatan</label>
                    <x-textarea-auto wire:model="items.{{ $tahap }}.catatan" rows="2" :disabled="!$dapatDiisi" />
                    @error("items.$tahap.catatan")
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endforeach

        @if($isEditable)
            <div class="flex justify-end pt-4 border-t border-border">
                <button type="submit" id="simpan-persetujuan"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                    Simpan Persetujuan
                </button>
            </div>
        @endif
    </form>
</div>
