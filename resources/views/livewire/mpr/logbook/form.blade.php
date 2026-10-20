<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Pelaksanaan / Logbook Perubahan (F4)</h1>
            <p class="text-sm text-muted mt-1">MPR05: pencatatan pelaksanaan perubahan, status akhir, tautan bukti, dan berkas bukti dukung</p>
        </div>
        <a href="{{ route('mpr.perubahan.hub', [$konteks, $perubahan]) }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
            Hub Perubahan
        </a>
    </div>

    @if($isAdmin)
        <div class="mb-6 rounded-lg bg-info/10 border border-info/20 px-4 py-3 text-sm text-info">
            Anda melihat data ini dalam mode <strong>read-only</strong>.
        </div>
    @endif

    {{-- Ringkasan Identitas Perubahan & Persetujuan Pelaksanaan (Read-only dari F1 & Persetujuan) --}}
    <div class="mb-6 rounded-xl border border-border bg-surface p-5">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-3">Ringkasan Permohonan & Persetujuan</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-muted">Unit Pemohon</p>
                <p class="font-medium text-text-strong">{{ $perubahan->unit_pemohon ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Klasifikasi & Lingkup</p>
                <p class="font-medium text-text-strong">{{ $perubahan->klasifikasi?->nama ?? '-' }} &middot; {{ $perubahan->lingkup?->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Status Persetujuan Pelaksanaan</p>
                <p class="font-medium text-text-strong">{{ $persetujuanPelaksanaan?->status ?? 'Belum diisi' }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">PIC Penyetuju</p>
                <p class="font-medium text-text-strong">{{ $persetujuanPelaksanaan?->pic_penyetuju ?: '-' }}</p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="rounded-xl border border-border bg-surface p-6 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Unit Pelaksana</label>
                <input type="text" wire:model="unit_pelaksana" maxlength="255" id="unit_pelaksana" @disabled(!$isEditable)
                    placeholder="Unit / Bidang pelaksana"
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                @error('unit_pelaksana') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">PIC Pelaksana</label>
                <input type="text" wire:model="pic_pelaksana" maxlength="255" id="pic_pelaksana" @disabled(!$isEditable)
                    placeholder="Nama penanggung jawab pelaksanaan"
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                @error('pic_pelaksana') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Mulai Pelaksanaan</label>
                <input type="date" wire:model="tanggal_mulai" id="tanggal_mulai" @disabled(!$isEditable)
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                @error('tanggal_mulai') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Selesai Pelaksanaan</label>
                <input type="date" wire:model="tanggal_selesai" id="tanggal_selesai" @disabled(!$isEditable)
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                @error('tanggal_selesai') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Status Perubahan</label>
                <select wire:model="status_perubahan" id="status_perubahan" @disabled(!$isEditable)
                    class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                    <option value="">— Pilih Status —</option>
                    <option value="Berhasil">Berhasil</option>
                    <option value="Gagal">Gagal</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
                @error('status_perubahan') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Catatan Pelaksanaan</label>
            <x-textarea-auto wire:model="catatan" rows="3" :disabled="!$isEditable" />
            @error('catatan') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">URL Bukti Pelaksanaan</label>
            <input type="url" wire:model="bukti_url" maxlength="255" id="bukti_url" @disabled(!$isEditable)
                placeholder="https://..."
                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
            @error('bukti_url') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        {{-- Lampiran Bukti Dukung --}}
        <div class="pt-4 border-t border-border space-y-3">
            <label class="block text-xs font-semibold text-text uppercase tracking-wider">
                Berkas Lampiran Bukti Pelaksanaan (Maks 10 MB)
            </label>

            @if($isEditable)
                <div>
                    <input wire:model="file_bukti" type="file" id="file_bukti"
                        class="w-full text-xs text-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-surface-soft file:text-text hover:file:bg-surface-raised cursor-pointer">
                    <div wire:loading wire:target="file_bukti" class="text-xs text-accent mt-1">Mengunggah berkas...</div>
                    @error('file_bukti') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            @if($lampiranList->isNotEmpty())
                <div class="space-y-2 pt-2" id="daftar-lampiran-logbook">
                    @foreach($lampiranList as $file)
                        <div class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-lg border border-border bg-surface-soft text-xs" wire:key="lampiran-{{ $file->id }}">
                            <a href="{{ asset('storage/' . $file->path_file) }}" target="_blank"
                                class="inline-flex items-center gap-2 text-text hover:text-accent font-medium truncate">
                                <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="truncate">{{ $file->nama_file }}</span>
                                <span class="text-muted shrink-0">({{ $file->ukuran_kb }} KB)</span>
                            </a>
                            @if($isEditable)
                                <button type="button"
                                    @click="$dispatch('confirm-action', {
                                        wireId: $wire.id,
                                        action: 'deleteLampiran',
                                        params: [{{ $file->id }}],
                                        title: 'Hapus Lampiran',
                                        message: 'Hapus berkas {{ $file->nama_file }}?',
                                        subMessage: 'Berkas fisik akan dihapus secara permanen.',
                                        confirmText: 'Ya, Hapus',
                                        type: 'danger'
                                    })"
                                    class="text-danger hover:underline cursor-pointer shrink-0">
                                    Hapus
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-muted italic">Belum ada berkas lampiran yang diunggah.</p>
            @endif
        </div>

        @if($isEditable)
            <div class="flex justify-end pt-4 border-t border-border">
                <button type="submit" id="simpan-logbook"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                    Simpan Logbook
                </button>
            </div>
        @endif
    </form>
</div>
