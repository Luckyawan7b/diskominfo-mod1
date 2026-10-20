<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Perencanaan Perubahan (F3)</h1>
            <p class="text-sm text-muted mt-1">MPR03: implementasi, strategi IT & organisasi, sumber daya, indikator keberhasilan, stakeholder, komunikasi, dan pelatihan</p>
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

    {{-- Navigasi Tab --}}
    <div class="flex border-b border-border space-x-2 overflow-x-auto mb-6" role="tablist">
        @foreach($tabs as $key => $label)
            <button type="button" wire:click="setTab('{{ $key }}')" id="tab-btn-{{ $key }}"
                class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors cursor-pointer {{ $activeTab === $key ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-text hover:border-border' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- TAB 1: IMPLEMENTASI (1:1) --}}
    <div class="{{ $activeTab === 'implementasi' ? 'block' : 'hidden' }}" id="panel-implementasi">
        <form wire:submit="saveImplementasi" class="rounded-xl border border-border bg-surface p-6 space-y-5">
            <div>
                <h2 class="text-base font-bold text-text-strong">Jadwal & PIC Implementasi (3.1)</h2>
                <p class="text-xs text-muted mt-0.5">Tentukan periode pelaksanaan perubahan serta penanggung jawab implementasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" wire:model="tanggal_mulai" id="tanggal_mulai" @disabled(!$isEditable)
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                    @error('tanggal_mulai') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" wire:model="tanggal_selesai" id="tanggal_selesai" @disabled(!$isEditable)
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                    <p class="text-[11px] text-muted mt-1">Harus sama atau setelah tanggal mulai.</p>
                    @error('tanggal_selesai') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Jangka Waktu</label>
                    <input type="text" wire:model="jangka_waktu" maxlength="255" id="jangka_waktu" @disabled(!$isEditable)
                        placeholder="Contoh: 3 Bulan / Triwulan II 2026"
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                    @error('jangka_waktu') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">PIC Implementasi</label>
                    <input type="text" wire:model="pic_implementasi" maxlength="255" id="pic_implementasi" @disabled(!$isEditable)
                        placeholder="Nama / Unit penanggung jawab"
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                    @error('pic_implementasi') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($isEditable)
                <div class="flex justify-end pt-4 border-t border-border">
                    <button type="submit" id="simpan-implementasi"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                        Simpan Implementasi
                    </button>
                </div>
            @endif
        </form>
    </div>

    {{-- TAB 2: STRATEGI (1:1) --}}
    <div class="{{ $activeTab === 'strategi' ? 'block' : 'hidden' }}" id="panel-strategi">
        <form wire:submit="saveStrategi" class="space-y-6">
            {{-- Strategi IT --}}
            <div class="rounded-xl border border-border bg-surface p-6 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-text-strong">Strategi IT & Rollback Plan (3.2)</h2>
                    <p class="text-xs text-muted mt-0.5">Pilih strategi deployment teknis serta rencana pemulihan (rollback) bila terjadi kegagalan.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Strategi IT (Tabel 3b)</label>
                    <select wire:model.live="ref_strategi_it_id" id="ref_strategi_it_id" @disabled(!$isEditable)
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        <option value="">— Pilih Strategi IT —</option>
                        @foreach($strategiItList as $it)
                            <option value="{{ $it->id }}">{{ $it->nama }}</option>
                        @endforeach
                    </select>
                    @error('ref_strategi_it_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror

                    @if($selectedStrategiIt && filled($selectedStrategiIt->deskripsi))
                        <div class="mt-3 rounded-lg border border-info/30 bg-info/10 px-4 py-3 text-xs text-text" id="info-strategi-it">
                            <p class="font-semibold text-info mb-1">Uraian {{ $selectedStrategiIt->nama }}:</p>
                            <p>{{ $selectedStrategiIt->deskripsi }}</p>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Deskripsi Penerapan Strategi IT</label>
                        <x-textarea-auto wire:model="deskripsi_strategi_it" rows="3" :disabled="!$isEditable" />
                        @error('deskripsi_strategi_it') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Rollback Plan</label>
                        <x-textarea-auto wire:model="rollback_plan" rows="3" :disabled="!$isEditable" />
                        @error('rollback_plan') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Strategi Organisasi --}}
            <div class="rounded-xl border border-border bg-surface p-6 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-text-strong">Strategi Organisasi (3.2)</h2>
                    <p class="text-xs text-muted mt-0.5">Pilih pendekatan manajemen perubahan organisasi beserta detail penerapannya.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Pendekatan Strategi Organisasi (Tabel 3a)</label>
                    <select wire:model.live="ref_strategi_organisasi_id" id="ref_strategi_organisasi_id" @disabled(!$isEditable)
                        class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                        <option value="">— Pilih Strategi Organisasi —</option>
                        @foreach($strategiOrgList as $so)
                            <option value="{{ $so->id }}">{{ $so->nama }}</option>
                        @endforeach
                    </select>
                    @error('ref_strategi_organisasi_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                @if($selectedStrategiOrg && $selectedStrategiOrg->kegiatan->isNotEmpty())
                    <div class="rounded-lg border border-border bg-surface-soft p-4" id="panduan-kegiatan-organisasi">
                        <p class="text-xs font-semibold text-text-strong uppercase tracking-wider mb-2">
                            Panduan 8 Kegiatan Acuan — {{ $selectedStrategiOrg->nama }}
                        </p>
                        <ol class="list-decimal ml-5 space-y-1 text-xs text-text">
                            @foreach($selectedStrategiOrg->kegiatan as $keg)
                                <li>{{ $keg->teks }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Detail Strategi Organisasi</label>
                    <x-textarea-auto wire:model="detail_strategi_organisasi" rows="3" :disabled="!$isEditable" />
                    @error('detail_strategi_organisasi') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                @if($isEditable)
                    <div class="flex justify-end pt-4 border-t border-border">
                        <button type="submit" id="simpan-strategi"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                            Simpan Strategi
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- TAB 3: SUMBER DAYA (Repeater + Reorder) --}}
    <div class="{{ $activeTab === 'sumber_daya' ? 'block' : 'hidden' }}" id="panel-sumber-daya">
        <x-repeater
            :items="$sumberDayaBlocks"
            :isEditable="$isEditable"
            addAction="addSumberDaya"
            addLabel="Tambah Sumber Daya"
            addId="mpr-tambah-sumber-daya"
            emptyTitle="Belum ada perencanaan sumber daya."
        >
            @foreach($sumberDayaBlocks as $i => $block)
                <x-repeater-item
                    wire:key="sd-{{ $block['id'] ?? 'new-'.$i }}"
                    :index="$i"
                    :total="count($sumberDayaBlocks)"
                    title="Sumber Daya Ke-{{ $i + 1 }}"
                    :subtitle="$block['jenis'] ?? null"
                    :isEditable="$isEditable"
                    saveAction="saveSumberDaya"
                    saveLabel="Simpan Sumber Daya Ini"
                    removeAction="removeSumberDaya"
                    removeTitle="Hapus Sumber Daya"
                    removeMessage="Hapus sumber daya Ke-{{ $i + 1 }} ini?"
                    moveUpAction="moveSumberDayaUp"
                    moveDownAction="moveSumberDayaDown"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                                Jenis Sumber Daya <span class="text-danger">*</span>
                            </label>
                            <input type="text" wire:model="sumberDayaBlocks.{{ $i }}.jenis" maxlength="255"
                                @disabled(!$isEditable)
                                placeholder="Contoh: Manusia / TIK / Anggaran / Lainnya"
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("sumberDayaBlocks.$i.jenis") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">PIC</label>
                            <input type="text" wire:model="sumberDayaBlocks.{{ $i }}.pic" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("sumberDayaBlocks.$i.pic") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Deskripsi Kebutuhan <span class="text-danger">*</span>
                        </label>
                        <x-textarea-auto wire:model="sumberDayaBlocks.{{ $i }}.deskripsi" rows="2" :disabled="!$isEditable" />
                        @error("sumberDayaBlocks.$i.deskripsi") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Alokasi Anggaran (Rp)</label>
                            <input type="number" step="0.01" min="0" wire:model="sumberDayaBlocks.{{ $i }}.alokasi_anggaran"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("sumberDayaBlocks.$i.alokasi_anggaran") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Skema Pembiayaan</label>
                            <input type="text" wire:model="sumberDayaBlocks.{{ $i }}.skema_pembiayaan" maxlength="255"
                                @disabled(!$isEditable)
                                placeholder="Contoh: APBD / DPA Diskominfo"
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("sumberDayaBlocks.$i.skema_pembiayaan") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-repeater-item>
            @endforeach
        </x-repeater>
    </div>

    {{-- TAB 4: INDIKATOR KEBERHASILAN (Repeater + Reorder) --}}
    <div class="{{ $activeTab === 'indikator_keberhasilan' ? 'block' : 'hidden' }}" id="panel-indikator">
        <x-repeater
            :items="$indikatorBlocks"
            :isEditable="$isEditable"
            addAction="addIndikator"
            addLabel="Tambah Indikator Keberhasilan"
            addId="mpr-tambah-indikator"
            emptyTitle="Belum ada indikator keberhasilan."
        >
            @foreach($indikatorBlocks as $i => $block)
                <x-repeater-item
                    wire:key="ik-{{ $block['id'] ?? 'new-'.$i }}"
                    :index="$i"
                    :total="count($indikatorBlocks)"
                    title="Indikator Ke-{{ $i + 1 }}"
                    :isEditable="$isEditable"
                    saveAction="saveIndikator"
                    saveLabel="Simpan Indikator Ini"
                    removeAction="removeIndikator"
                    removeTitle="Hapus Indikator Keberhasilan"
                    removeMessage="Hapus indikator keberhasilan Ke-{{ $i + 1 }} ini?"
                    moveUpAction="moveIndikatorUp"
                    moveDownAction="moveIndikatorDown"
                >
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Indikator Keberhasilan <span class="text-danger">*</span>
                        </label>
                        <x-textarea-auto wire:model="indikatorBlocks.{{ $i }}.indikator" rows="2" :disabled="!$isEditable" />
                        @error("indikatorBlocks.$i.indikator") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Satuan</label>
                            <input type="text" wire:model="indikatorBlocks.{{ $i }}.satuan" maxlength="100"
                                @disabled(!$isEditable)
                                placeholder="Contoh: % / Layanan / Hari"
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("indikatorBlocks.$i.satuan") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Target</label>
                            <input type="number" step="0.0001" wire:model="indikatorBlocks.{{ $i }}.target"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("indikatorBlocks.$i.target") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Realisasi</label>
                            <input type="number" step="0.0001" wire:model="indikatorBlocks.{{ $i }}.realisasi"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("indikatorBlocks.$i.realisasi") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-repeater-item>
            @endforeach
        </x-repeater>
    </div>

    {{-- TAB 5: STAKEHOLDER (Repeater) --}}
    <div class="{{ $activeTab === 'stakeholder' ? 'block' : 'hidden' }}" id="panel-stakeholder">
        <x-repeater
            :items="$stakeholderBlocks"
            :isEditable="$isEditable"
            addAction="addStakeholder"
            addLabel="Tambah Stakeholder"
            addId="mpr-tambah-stakeholder"
            emptyTitle="Belum ada pemangku kepentingan (stakeholder)."
        >
            @foreach($stakeholderBlocks as $i => $block)
                <x-repeater-item
                    wire:key="st-{{ $block['id'] ?? 'new-'.$i }}"
                    :index="$i"
                    :total="count($stakeholderBlocks)"
                    title="Stakeholder Ke-{{ $i + 1 }}"
                    :subtitle="$block['nama_stakeholder'] ?? null"
                    :isEditable="$isEditable"
                    saveAction="saveStakeholder"
                    saveLabel="Simpan Stakeholder Ini"
                    removeAction="removeStakeholder"
                    removeTitle="Hapus Stakeholder"
                    removeMessage="Hapus pemangku kepentingan Ke-{{ $i + 1 }} ini?"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                                Nama Pemangku Kepentingan <span class="text-danger">*</span>
                            </label>
                            <input type="text" wire:model="stakeholderBlocks.{{ $i }}.nama_stakeholder" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("stakeholderBlocks.$i.nama_stakeholder") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Kategori Stakeholder (Tabel 3c)</label>
                            <select wire:model="stakeholderBlocks.{{ $i }}.ref_kategori_stakeholder_id" @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                                <option value="">— Pilih Kategori —</option>
                                @foreach($kategoriStakeholder as $kat)
                                    <option value="{{ $kat->id }}" title="{{ $kat->deskripsi }}">{{ $kat->nama }}</option>
                                @endforeach
                            </select>
                            @error("stakeholderBlocks.$i.ref_kategori_stakeholder_id") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Jenis Resistensi (Tabel 3d)</label>
                            <select wire:model="stakeholderBlocks.{{ $i }}.ref_jenis_resistensi_id" @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                                <option value="">— Pilih Jenis Resistensi —</option>
                                @foreach($jenisResistensi as $jr)
                                    <option value="{{ $jr->id }}" title="{{ $jr->deskripsi }}">{{ $jr->nama }}</option>
                                @endforeach
                            </select>
                            @error("stakeholderBlocks.$i.ref_jenis_resistensi_id") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tingkat Resistensi (Tabel 3e)</label>
                            <select wire:model="stakeholderBlocks.{{ $i }}.ref_tingkat_resistensi_id" @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                                <option value="">— Pilih Tingkat Resistensi —</option>
                                @foreach($tingkatResistensi as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->nama }}</option>
                                @endforeach
                            </select>
                            @error("stakeholderBlocks.$i.ref_tingkat_resistensi_id") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-repeater-item>
            @endforeach
        </x-repeater>
    </div>

    {{-- TAB 6: KOMUNIKASI (Repeater) --}}
    <div class="{{ $activeTab === 'komunikasi' ? 'block' : 'hidden' }}" id="panel-komunikasi">
        <x-repeater
            :items="$komunikasiBlocks"
            :isEditable="$isEditable"
            addAction="addKomunikasi"
            addLabel="Tambah Rencana Komunikasi"
            addId="mpr-tambah-komunikasi"
            emptyTitle="Belum ada rencana komunikasi."
        >
            @foreach($komunikasiBlocks as $i => $block)
                <x-repeater-item
                    wire:key="kom-{{ $block['id'] ?? 'new-'.$i }}"
                    :index="$i"
                    :total="count($komunikasiBlocks)"
                    title="Komunikasi Ke-{{ $i + 1 }}"
                    :isEditable="$isEditable"
                    saveAction="saveKomunikasi"
                    saveLabel="Simpan Komunikasi Ini"
                    removeAction="removeKomunikasi"
                    removeTitle="Hapus Rencana Komunikasi"
                    removeMessage="Hapus rencana komunikasi Ke-{{ $i + 1 }} ini?"
                >
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                            Informasi yang Disampaikan <span class="text-danger">*</span>
                        </label>
                        <x-textarea-auto wire:model="komunikasiBlocks.{{ $i }}.informasi" rows="2" :disabled="!$isEditable" />
                        @error("komunikasiBlocks.$i.informasi") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Pengirim</label>
                            <input type="text" wire:model="komunikasiBlocks.{{ $i }}.pengirim" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("komunikasiBlocks.$i.pengirim") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Target Audiens</label>
                            <input type="text" wire:model="komunikasiBlocks.{{ $i }}.target_audiens" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("komunikasiBlocks.$i.target_audiens") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Media Komunikasi</label>
                            <input type="text" wire:model="komunikasiBlocks.{{ $i }}.media_komunikasi" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("komunikasiBlocks.$i.media_komunikasi") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Periode Komunikasi</label>
                            <input type="text" wire:model="komunikasiBlocks.{{ $i }}.periode_komunikasi" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("komunikasiBlocks.$i.periode_komunikasi") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-repeater-item>
            @endforeach
        </x-repeater>
    </div>

    {{-- TAB 7: PELATIHAN (Repeater) --}}
    <div class="{{ $activeTab === 'pelatihan' ? 'block' : 'hidden' }}" id="panel-pelatihan">
        <x-repeater
            :items="$pelatihanBlocks"
            :isEditable="$isEditable"
            addAction="addPelatihan"
            addLabel="Tambah Rencana Pelatihan"
            addId="mpr-tambah-pelatihan"
            emptyTitle="Belum ada rencana pelatihan."
        >
            @foreach($pelatihanBlocks as $i => $block)
                <x-repeater-item
                    wire:key="pel-{{ $block['id'] ?? 'new-'.$i }}"
                    :index="$i"
                    :total="count($pelatihanBlocks)"
                    title="Pelatihan Ke-{{ $i + 1 }}"
                    :subtitle="$block['nama_pelatihan'] ?? null"
                    :isEditable="$isEditable"
                    saveAction="savePelatihan"
                    saveLabel="Simpan Pelatihan Ini"
                    removeAction="removePelatihan"
                    removeTitle="Hapus Rencana Pelatihan"
                    removeMessage="Hapus rencana pelatihan Ke-{{ $i + 1 }} ini?"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                                Nama Pelatihan <span class="text-danger">*</span>
                            </label>
                            <input type="text" wire:model="pelatihanBlocks.{{ $i }}.nama_pelatihan" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("pelatihanBlocks.$i.nama_pelatihan") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Target Peserta</label>
                            <input type="text" wire:model="pelatihanBlocks.{{ $i }}.target_peserta" maxlength="255"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("pelatihanBlocks.$i.target_peserta") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Target Jumlah Peserta</label>
                            <input type="number" min="1" wire:model="pelatihanBlocks.{{ $i }}.target_jumlah"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("pelatihanBlocks.$i.target_jumlah") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Metode Pelatihan</label>
                            <input type="text" wire:model="pelatihanBlocks.{{ $i }}.metode_pelatihan" maxlength="255"
                                @disabled(!$isEditable)
                                placeholder="Contoh: Tatap Muka / Daring / Workshop"
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("pelatihanBlocks.$i.metode_pelatihan") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Tanggal Pelaksanaan</label>
                            <input type="date" wire:model="pelatihanBlocks.{{ $i }}.tanggal_pelaksanaan"
                                @disabled(!$isEditable)
                                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                            @error("pelatihanBlocks.$i.tanggal_pelaksanaan") <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-repeater-item>
            @endforeach
        </x-repeater>
    </div>
</div>
