<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-mono text-muted">{{ $perubahan->id_perubahan }} &middot; {{ $perubahan->nama_layanan_digital }}</p>
            <h1 class="text-2xl font-bold text-text-strong">Evaluasi Perubahan (F5)</h1>
            <p class="text-sm text-muted mt-1">MPR04: ringkasan capaian indikator keberhasilan, hasil akhir perubahan, lesson learned, dan rekomendasi solusi</p>
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

    {{-- Ringkasan Indikator Keberhasilan dari F3 --}}
    <div class="mb-6 rounded-xl border border-border bg-surface overflow-hidden" id="ringkasan-indikator-f3">
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-border">
            <div>
                <h2 class="text-base font-semibold text-text-strong">Ringkasan Target & Realisasi Indikator Keberhasilan (F3)</h2>
                <p class="text-xs text-muted mt-0.5">Diambil dari perencanaan Indikator Keberhasilan pada Formulir 3.</p>
            </div>
            <a href="{{ route('mpr.perencanaan.form', [$konteks, $perubahan, 'tab' => 'indikator_keberhasilan']) }}"
                class="text-xs font-medium text-accent hover:text-primary transition-colors shrink-0">
                {{ $isEditable ? 'Kelola di F3' : 'Lihat di F3' }} &rarr;
            </a>
        </div>

        @if($indikatorList->isNotEmpty())
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-soft">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">No</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Indikator Keberhasilan</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Satuan</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Target</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase tracking-wider">Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($indikatorList as $ik)
                        <tr>
                            <td class="px-5 py-3 text-muted text-xs">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3 text-text font-medium">{{ $ik->indikator }}</td>
                            <td class="px-5 py-3 text-text">{{ $ik->satuan ?: '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono text-text">{{ $ik->target !== null ? rtrim(rtrim((string) $ik->target, '0'), '.') : '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono text-text-strong font-semibold">{{ $ik->realisasi !== null ? rtrim(rtrim((string) $ik->realisasi, '0'), '.') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-5 py-8 text-center text-xs text-muted">
                Belum ada indikator keberhasilan yang diisi pada F3 Perencanaan.
            </div>
        @endif
    </div>

    <form wire:submit="save" class="rounded-xl border border-border bg-surface p-6 space-y-5">
        <div class="max-w-sm">
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Hasil Evaluasi Perubahan</label>
            <select wire:model="hasil" id="hasil" @disabled(!$isEditable)
                class="w-full rounded-lg border border-border bg-field px-4 py-2.5 text-text text-sm focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-60">
                <option value="">— Pilih Hasil —</option>
                <option value="Berhasil">Berhasil</option>
                <option value="Tidak Berhasil">Tidak Berhasil</option>
            </select>
            @error('hasil') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Lesson Learned (Pembelajaran)</label>
            <x-textarea-auto wire:model="lesson_learned" rows="4" :disabled="!$isEditable" />
            @error('lesson_learned') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">Rekomendasi / Solusi Tindak Lanjut</label>
            <x-textarea-auto wire:model="rekomendasi_solusi" rows="4" :disabled="!$isEditable" />
            @error('rekomendasi_solusi') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
        </div>

        @if($isEditable)
            <div class="flex justify-end pt-4 border-t border-border">
                <button type="submit" id="simpan-evaluasi"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow transition-all cursor-pointer">
                    Simpan Evaluasi
                </button>
            </div>
        @endif
    </form>
</div>
