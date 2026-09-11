<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">{{ $layanan && $layanan->exists ? 'Edit Deskripsi Layanan' : 'Formulir Deskripsi Layanan' }}</h1>
            <p class="text-sm text-muted mt-1">Lengkapi informasi mengenai layanan untuk melanjutkan ke 5 modul manajemen.</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="save" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <a href="{{ route('layanan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                Batal
            </a>
        </div>
    </div>

    {{-- Form Sections with AlpineJS Accordion --}}
    <div x-data="{ activeTab: 'identitas' }" class="flex flex-col lg:flex-row gap-6">
        
        {{-- Sidebar Navigation --}}
        <div class="w-full lg:w-64 shrink-0">
            <nav class="flex flex-col gap-1 sticky top-6">
                <button @click="activeTab = 'identitas'" :class="{ 'bg-primary/10 text-primary border-primary/50': activeTab === 'identitas', 'text-muted hover:bg-surface-soft border-transparent hover:text-text': activeTab !== 'identitas' }" class="text-left px-4 py-3 rounded-lg border text-sm font-medium transition-colors">
                    Identitas Layanan
                </button>
                <button @click="activeTab = 'data'" :class="{ 'bg-primary/10 text-primary border-primary/50': activeTab === 'data', 'text-muted hover:bg-surface-soft border-transparent hover:text-text': activeTab !== 'data' }" class="text-left px-4 py-3 rounded-lg border text-sm font-medium transition-colors">
                    Data & Integrasi
                </button>
                <button @click="activeTab = 'aplikasi'" :class="{ 'bg-primary/10 text-primary border-primary/50': activeTab === 'aplikasi', 'text-muted hover:bg-surface-soft border-transparent hover:text-text': activeTab !== 'aplikasi' }" class="text-left px-4 py-3 rounded-lg border text-sm font-medium transition-colors">
                    Aplikasi & Infrastruktur
                </button>
                <button @click="activeTab = 'dokumen'" :class="{ 'bg-primary/10 text-primary border-primary/50': activeTab === 'dokumen', 'text-muted hover:bg-surface-soft border-transparent hover:text-text': activeTab !== 'dokumen' }" class="text-left px-4 py-3 rounded-lg border text-sm font-medium transition-colors">
                    Dokumen Pendukung
                </button>
            </nav>
        </div>

        {{-- Content Area --}}
        <div class="flex-1 rounded-xl border border-border bg-surface p-6 sm:p-8">
            
            {{-- Section: Identitas Layanan --}}
            <div x-show="activeTab === 'identitas'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <h2 class="text-lg font-bold text-text-strong mb-4 border-b border-border pb-2">Identitas Layanan</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Nama Layanan <span class="text-danger">*</span></label>
                        <input wire:model="nama_layanan" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: Layanan Surat Pengantar">
                        @error('nama_layanan') <span class="text-xs text-danger mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Bidang/Bagian</label>
                        <input wire:model="bidang_bagian" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: Bidang Pelayanan Umum">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Status Layanan <span class="text-danger">*</span></label>
                        <select wire:model="status_layanan" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="berjalan">Berjalan</option>
                            <option value="direncanakan">Direncanakan</option>
                            <option value="dihentikan">Dihentikan</option>
                        </select>
                        @error('status_layanan') <span class="text-xs text-danger mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Layanan Prioritas</label>
                        <label class="flex items-center gap-2 mt-2 cursor-pointer">
                            <input wire:model="is_prioritas" type="checkbox" class="w-5 h-5 rounded border-border bg-field text-accent focus:ring-accent focus:ring-offset-surface">
                            <span class="text-sm text-text">Tandai sebagai Layanan Prioritas</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-text mb-1">Deskripsi Layanan</label>
                    <textarea wire:model="deskripsi_layanan" rows="3" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Deskripsikan fungsi dan tujuan layanan ini..."></textarea>
                </div>
            </div>

            {{-- Section: Data & Integrasi --}}
            <div x-show="activeTab === 'data'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <h2 class="text-lg font-bold text-text-strong mb-4 border-b border-border pb-2">Data & Integrasi</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Target Pengguna</label>
                        <select wire:model="target_pengguna" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="">-- Pilih Target Pengguna --</option>
                            <option value="Publik/Masyarakat">Publik/Masyarakat</option>
                            <option value="Internal Pemerintahan">Internal Pemerintahan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">K/L Terkait</label>
                        <input wire:model="kl_terkait" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: Kemendagri">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Supplier Data</label>
                        <input wire:model="supplier_data" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Instansi/Bagian penyuplai data">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Sifat Data</label>
                        <select wire:model="sifat_data" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="">-- Pilih Sifat Data --</option>
                            <option value="terbuka">Terbuka</option>
                            <option value="terbatas">Terbatas</option>
                            <option value="tertutup">Tertutup</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Jenis Data</label>
                        <input wire:model="jenis_data" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Validitas Data</label>
                        <input wire:model="validitas_data" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: Harian, Bulanan">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Nama Data Input</label>
                        <textarea wire:model="nama_data_input" rows="2" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Nama Data Output</label>
                        <textarea wire:model="nama_data_output" rows="2" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
                    </div>
                </div>
                
                <div class="border-t border-border pt-4 mt-4">
                    <label class="flex items-center gap-2 cursor-pointer mb-4">
                        <input wire:model.live="interoperabilitas" type="checkbox" class="w-5 h-5 rounded border-border bg-field text-accent focus:ring-accent focus:ring-offset-surface">
                        <span class="text-sm font-semibold text-text">Mendukung Interoperabilitas (Integrasi)</span>
                    </label>

                    @if($interoperabilitas)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-7 border-l-2 border-primary/30">
                            <div>
                                <label class="block text-sm font-semibold text-text mb-1">Tujuan Integrasi</label>
                                <textarea wire:model="tujuan_integrasi" rows="2" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-text mb-1">Metode Integrasi</label>
                                <input wire:model="metode_integrasi" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: API, Web Service">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-text mb-1">Link Dokumen Integrasi</label>
                                <input wire:model="link_dokumen_integrasi" type="url" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="https://...">
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Section: Aplikasi & Infrastruktur --}}
            <div x-show="activeTab === 'aplikasi'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <h2 class="text-lg font-bold text-text-strong mb-4 border-b border-border pb-2">Aplikasi & Infrastruktur</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Nama Aplikasi</label>
                        <input wire:model="nama_aplikasi" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Tipe Aplikasi</label>
                        <input wire:model="tipe_aplikasi" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: Web, Mobile, Desktop">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Link Aplikasi</label>
                        <input wire:model="link_aplikasi" type="url" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Tahun Pembuatan</label>
                        <input wire:model="tahun_pembuatan" type="number" min="1900" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: 2023">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Letak Server</label>
                        <input wire:model="letak_server" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: Data Center Diskominfo">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Link DPA (Dokumen Pelaksanaan Anggaran)</label>
                        <input wire:model="link_dpa" type="url" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="https://...">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-text mb-1">Keluaran Aplikasi</label>
                    <textarea wire:model="keluaran_aplikasi" rows="2" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
                </div>
            </div>

            {{-- Section: Dokumen Pendukung --}}
            <div x-show="activeTab === 'dokumen'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <h2 class="text-lg font-bold text-text-strong mb-4 border-b border-border pb-2">Dokumen Pendukung & Bantuan</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Link SLA (Service Level Agreement)</label>
                        <input wire:model="link_sla" type="url" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text mb-1">Link SOP</label>
                        <input wire:model="link_sop" type="url" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="https://...">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-text mb-1">Helpdesk / Kontak Bantuan</label>
                        <input wire:model="helpdesk" type="text" class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Contoh: 0812-3456-7890 atau helpdesk@desa.id">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
