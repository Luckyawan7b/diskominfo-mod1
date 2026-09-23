<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">
                {{ $isNew ? 'Tambah Riwayat Pengumpulan' : 'Edit Pengumpulan' }}
            </h1>
            <p class="text-sm text-muted mt-1">Formulir 2: Database Pengumpulan & Pengolahan Pengetahuan</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('mpn.pengumpulan.index', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan]) }}" wire:navigate
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Batal
            </a>
        </div>
    </div>

    <form wire:submit.prevent="save">
        {{-- Tabs Navigation (Simulated) --}}
        <div class="flex items-center gap-1 mb-6 border-b border-border overflow-x-auto no-scrollbar">
            <button type="button" class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 border-primary text-primary transition-colors">
                1. Detail Dokumen & Revisi
            </button>
            @if(!$isNew)
                <a href="{{ route('mpn.pemanfaatan.form', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan, 'pengumpulan' => $pengumpulanModel]) }}" wire:navigate
                    class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-muted hover:text-text hover:border-border transition-colors">
                    2. Pemanfaatan & Rating
                </a>
                <a href="{{ route('mpn.alih-pengetahuan.form', ['konteks' => $konteks, 'pengetahuan' => $pengetahuan, 'pengumpulan' => $pengumpulanModel]) }}" wire:navigate
                    class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-muted hover:text-text hover:border-border transition-colors">
                    3. Alih Pengetahuan
                </a>
            @else
                <button type="button" disabled
                    class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-muted opacity-50 cursor-not-allowed">
                    2. Pemanfaatan & Rating
                    <span class="ml-1 text-[10px] font-normal italic">(Simpan dulu)</span>
                </button>
                <button type="button" disabled
                    class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-muted opacity-50 cursor-not-allowed">
                    3. Alih Pengetahuan
                    <span class="ml-1 text-[10px] font-normal italic">(Simpan dulu)</span>
                </button>
            @endif
        </div>

        <div class="space-y-6">
            
            {{-- Bagian A: Metadata Utama --}}
            <div class="rounded-xl border border-border bg-surface p-6">
                <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider mb-5 pb-3 border-b border-border">
                    Bagian A (Formulir 2): Metadata Utama
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- ID Pengetahuan --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            ID Pengetahuan <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="id_pengetahuan" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                        @error('id_pengetahuan') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tanggal Pengumpulan --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Tanggal Pengumpulan <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_pengumpulan" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                        @error('tanggal_pengumpulan') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Unit Pengumpulan --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Unit Pengumpulan <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="unit_pengumpulan" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Bidang E-Gov">
                        @error('unit_pengumpulan') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status Publikasi --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select wire:model="status_publikasi_simpan" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                            <option value="Disimpan">Disimpan Saja</option>
                            <option value="Publikasi">Publikasi (Tersedia Umum)</option>
                        </select>
                    </div>

                    {{-- Lokasi Penyimpanan Lain --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Lokasi Penyimpanan Lain
                        </label>
                        <input type="text" wire:model="lokasi_penyimpanan_lain" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Rak Buku A2">
                    </div>

                    {{-- URL/Link Dokumen --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            URL / Tautan Akses
                        </label>
                        <input type="url" wire:model="url" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: https://drive.google.com/...">
                    </div>
                </div>
            </div>

            {{-- Bagian B: Detail Konten & Revisi --}}
            <div class="rounded-xl border border-border bg-surface p-6">
                <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider mb-5 pb-3 border-b border-border">
                    Bagian B: Detail Konten & Revisi
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Revisi Dari --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Revisi Dari (Opsional)
                        </label>
                        <p class="text-xs text-muted mb-2">Pilih riwayat pengumpulan sebelumnya jika ini adalah versi pembaruan.</p>
                        <select wire:model="revisi_dari_id" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                            <option value="">-- Versi Awal (Bukan Revisi) --</option>
                            @foreach($revisiList as $rev)
                                @if($isNew || $rev->id !== $pengumpulanModel->id)
                                    <option value="{{ $rev->id }}">{{ $rev->id_pengetahuan }} ({{ \Carbon\Carbon::parse($rev->tanggal_pengumpulan)->format('d/m/Y') }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Penulis --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Penulis</label>
                        <input type="text" wire:model="penulis" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                    </div>

                    {{-- Kontributor --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Kontributor</label>
                        <input type="text" wire:model="kontributor" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                    </div>

                    {{-- Label Tags --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Label / Tags</label>
                        <p class="text-xs text-muted mb-2">Pisahkan dengan koma.</p>
                        <input type="text" wire:model="label_tags" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: panduan, sop, administrasi">
                    </div>

                    {{-- Metode Pengolahan --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Metode Pengolahan (Opsional)</label>
                        <select wire:model="ref_metode_pengolahan_id" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                            <option value="">-- Pilih Metode --</option>
                            @foreach($metodeList as $metode)
                                <option value="{{ $metode->id }}">{{ $metode->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Update Terakhir --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Update Terakhir (Pada Dokumen Asli)</label>
                        <input type="date" wire:model="tanggal_update_terakhir" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                    </div>
                </div>
            </div>

            @if($isEditable)
                <div class="flex justify-end gap-3 mt-6">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Data
                    </button>
                </div>
            @endif

        </div>
    </form>
</div>
