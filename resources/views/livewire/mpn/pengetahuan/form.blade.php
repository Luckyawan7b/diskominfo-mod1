<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">
                {{ $isNew ? 'Tambah Pengetahuan Baru' : 'Edit Pengetahuan' }}
            </h1>
            <p class="text-sm text-muted mt-1">Formulir 3: Inventarisasi dan Rencana Dokumentasi</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('mpn.pengetahuan.index', $konteks) }}" wire:navigate
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <form wire:submit.prevent="save">
        {{-- Tabs Navigation --}}
        <div class="flex items-center gap-1 mb-6 border-b border-border overflow-x-auto no-scrollbar">
            <button type="button" wire:click="switchTab(1)"
                class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 transition-colors {{ $activeTab === 1 ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-text hover:border-border' }}">
                1. Identifikasi
            </button>
            <button type="button" wire:click="switchTab(2)"
                {{ $apakah_terdokumentasi ? 'disabled' : '' }}
                class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 transition-colors {{ $apakah_terdokumentasi ? 'opacity-50 cursor-not-allowed border-transparent text-muted' : ($activeTab === 2 ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-text hover:border-border') }}">
                2. Rencana Dokumentasi
                @if($apakah_terdokumentasi)
                    <span class="ml-1 text-[10px] font-normal italic">(Otomatis disembunyikan)</span>
                @endif
            </button>
            <button type="button" wire:click="switchTab(3)"
                {{ $isNew ? 'disabled' : '' }}
                class="whitespace-nowrap px-4 py-3 text-sm font-semibold border-b-2 transition-colors {{ $isNew ? 'opacity-50 cursor-not-allowed border-transparent text-muted' : ($activeTab === 3 ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-text hover:border-border') }}">
                3. Pengumpulan & Riwayat
                @if($isNew)
                    <span class="ml-1 text-[10px] font-normal italic">(Simpan data dulu)</span>
                @endif
            </button>
        </div>

        {{-- Tab 1: Identifikasi --}}
        <div class="{{ $activeTab === 1 ? 'block' : 'hidden' }} space-y-6">
            <div class="rounded-xl border border-border bg-surface p-6">
                <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider mb-5 pb-3 border-b border-border">
                    Bagian A: Identifikasi Pengetahuan
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nama Sub Fitur --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Nama Sub-Fitur (Opsional)
                        </label>
                        <p class="text-xs text-muted mb-2">Jika pengetahuan ini spesifik untuk fitur layanan tertentu.</p>
                        <input type="text" wire:model="nama_sub_fitur" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Fitur Pendaftaran Online">
                    </div>

                    {{-- Layanan Prioritas --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Layanan Prioritas (Opsional)
                        </label>
                        <p class="text-xs text-muted mb-2">Tandai jika ini merupakan layanan prioritas atau kritikal.</p>
                        <input type="text" wire:model="layanan_prioritas" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Layanan Utama Disdukcapil">
                    </div>

                    {{-- Nama Pengetahuan --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Nama Pengetahuan <span class="text-danger">*</span>
                        </label>
                        <p class="text-xs text-muted mb-2">Judul atau deskripsi pengetahuan layanan.</p>
                        <input type="text" wire:model="nama_pengetahuan" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: SOP Pendaftaran Penduduk Terpadu">
                        @error('nama_pengetahuan') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Aspek PEMDI --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Aspek PEMDI <span class="text-danger">*</span>
                        </label>
                        <p class="text-xs text-muted mb-2">Pilih kategori aspek PEMDI terkait.</p>
                        <select wire:model.live="ref_aspek_pemdi_id" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                            <option value="">-- Pilih Aspek PEMDI --</option>
                            @foreach($aspekList as $aspek)
                                <option value="{{ $aspek->id }}">{{ $aspek->nama }}</option>
                            @endforeach
                        </select>
                        @error('ref_aspek_pemdi_id') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Indikator PEMDI --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Indikator PEMDI <span class="text-danger">*</span>
                        </label>
                        <p class="text-xs text-muted mb-2">Pilih indikator PEMDI yang relevan (tergantung aspek).</p>
                        <select wire:model="ref_indikator_pemdi_id" {{ !$isEditable || empty($indikatorList) ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50">
                            <option value="">-- Pilih Indikator PEMDI --</option>
                            @foreach($indikatorList as $indikator)
                                <option value="{{ $indikator->id }}">{{ $indikator->nama }}</option>
                            @endforeach
                        </select>
                        @error('ref_indikator_pemdi_id') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Apakah Terdokumentasi --}}
                    <div class="md:col-span-2 border-t border-border pt-4 mt-2">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Apakah Saat Ini Sudah Terdokumentasi? <span class="text-danger">*</span>
                        </label>
                        <p class="text-xs text-muted mb-3">Jika belum, Anda wajib mengisi Rencana Dokumentasi di Tab 2.</p>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="apakah_terdokumentasi" value="1" {{ !$isEditable ? 'disabled' : '' }}
                                    class="w-4 h-4 text-primary focus:ring-primary border-border">
                                <span class="text-sm font-medium">Ya, sudah ada dokumen/sumbernya</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="apakah_terdokumentasi" value="0" {{ !$isEditable ? 'disabled' : '' }}
                                    class="w-4 h-4 text-primary focus:ring-primary border-border">
                                <span class="text-sm font-medium">Belum, perlu rencana dokumentasi</span>
                            </label>
                        </div>
                    </div>

                    {{-- Sudah Terdokumentasi (Deskripsi) --}}
                    <div class="md:col-span-2 {{ !$apakah_terdokumentasi ? 'hidden' : '' }}">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Deskripsi Dokumentasi (Jika Ya)
                        </label>
                        <p class="text-xs text-muted mb-2">Tuliskan bentuk/lokasi dokumentasi yang sudah ada.</p>
                        <x-textarea-auto wire:model="sudah_terdokumentasi" rows="3" :disabled="!$isEditable"
                            placeholder="Contoh: Dokumen tersimpan di Google Drive Bidang IT..." />
                    </div>

                </div>
            </div>

            @if($isEditable)
                <div class="flex justify-end gap-3 mt-6">
                    @if(!$apakah_terdokumentasi)
                        <button type="button" wire:click="switchTab(2)"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-surface-soft hover:bg-border border border-border text-sm font-semibold text-text transition-all">
                            Lanjut ke Rencana Dokumentasi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    @else
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Pengetahuan
                        </button>
                    @endif
                    @if(!$isNew)
                        <button type="button" wire:click="switchTab(3)"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-surface-soft hover:bg-border border border-border text-sm font-semibold text-text transition-all">
                            Lanjut ke Pengumpulan
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Tab 2: Rencana Dokumentasi --}}
        <div class="{{ $activeTab === 2 ? 'block' : 'hidden' }} space-y-6">
            <div class="rounded-xl border border-border bg-surface p-6">
                <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider mb-5 pb-3 border-b border-border">
                    Bagian B: Rencana Dokumentasi
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Target Tahun Ini --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Target Tahun Ini
                        </label>
                        <input type="text" wire:model="target_tahun_ini" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Selesai di-draft Q2">
                    </div>

                    {{-- Pemilik Pengetahuan --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Pemilik Pengetahuan (Subject Matter Expert)
                        </label>
                        <input type="text" wire:model="pemilik_pengetahuan" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Bpk. Budi (Kasubag Perencanaan)">
                    </div>

                    {{-- Tipe Dokumentasi --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-2">
                            Tipe Dokumentasi yang Direncanakan
                        </label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="tipe_teks" {{ !$isEditable ? 'disabled' : '' }}
                                    class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                <span class="text-sm">Teks/Dokumen</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="tipe_gambar" {{ !$isEditable ? 'disabled' : '' }}
                                    class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                <span class="text-sm">Gambar/Infografis</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="tipe_audio" {{ !$isEditable ? 'disabled' : '' }}
                                    class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                <span class="text-sm">Audio/Podcast</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="tipe_video" {{ !$isEditable ? 'disabled' : '' }}
                                    class="w-4 h-4 rounded border-border text-primary focus:ring-primary">
                                <span class="text-sm">Video/Tutorial</span>
                            </label>
                        </div>
                    </div>

                    {{-- Penanggung Jawab --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Penanggung Jawab Dokumentasi
                        </label>
                        <input type="text" wire:model="penanggung_jawab" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Tim IT">
                    </div>

                    {{-- Target Waktu --}}
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Target Waktu Penyelesaian
                        </label>
                        <input type="text" wire:model="target_waktu_dokumentasi" {{ !$isEditable ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50"
                            placeholder="Contoh: Akhir Maret 2025">
                    </div>
                </div>
            </div>

            @if($isEditable)
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" wire:click="switchTab(1)"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm font-semibold text-text hover:bg-surface-soft transition-all">
                        Kembali ke Identifikasi
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Semua Pengetahuan
                    </button>
                    @if(!$isNew)
                        <button type="button" wire:click="switchTab(3)"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-surface-soft hover:bg-border border border-border text-sm font-semibold text-text transition-all">
                            Lanjut ke Pengumpulan
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Tab 3: Pengumpulan & Riwayat --}}
        <div class="{{ $activeTab === 3 ? 'block' : 'hidden' }} space-y-6">
            <div class="rounded-xl border border-border bg-surface p-6">
                <h3 class="text-sm font-bold text-text-strong uppercase tracking-wider mb-2 pb-3 border-b border-border">
                    Bagian C: Pengumpulan, Riwayat Revisi & Pemanfaatan
                </h3>
                
                <p class="text-sm text-muted mb-6">
                    Kelola riwayat pembaruan/revisi dokumen pengetahuan ini. 
                    Setiap revisi dapat memiliki catatan evaluasi, pemanfaatan, dan alih pengetahuan tersendiri.
                </p>

                @if(!$isNew)
                    <div class="flex justify-center">
                        <a href="{{ route('mpn.pengumpulan.index', ['konteks' => $konteks, 'pengetahuan' => $pengetahuanModel]) }}" wire:navigate
                            class="inline-flex items-center gap-3 px-6 py-3 rounded-xl bg-surface-soft hover:bg-border border border-border text-sm font-semibold text-text-strong shadow-sm transition-all">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Buka Timeline & Riwayat Revisi
                        </a>
                    </div>
                @else
                    <div class="text-center p-6 border border-dashed border-border rounded-xl bg-surface-soft text-muted text-sm">
                        Simpan data pengetahuan terlebih dahulu untuk dapat mengelola riwayat revisi.
                    </div>
                @endif
            </div>
            
            <div class="flex justify-start gap-3 mt-6">
                <button type="button" wire:click="switchTab(1)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm font-semibold text-text hover:bg-surface-soft transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Identifikasi
                </button>
            </div>
        </div>
    </form>
</div>
