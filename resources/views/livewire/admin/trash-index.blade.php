<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Tempat Sampah</h1>
            <p class="text-sm text-muted mt-1">Data yang dihapus disimpan di sini dan dapat dipulihkan</p>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div x-data="{ tab: @entangle('activeTab') }" class="mb-6">
        <div class="flex flex-wrap gap-1 p-1 rounded-xl bg-surface-soft border border-border">
            @foreach([
                'layanan'         => 'Layanan',
                'mr_konteks'      => 'Konteks MR',
                'mr_risiko'       => 'Risiko',
                'mpn_konteks'     => 'Konteks MPN',
                'mpn_pengetahuan' => 'Pengetahuan',
                'user'            => 'User',
            ] as $key => $label)
                <button
                    wire:click="$set('activeTab', '{{ $key }}')"
                    class="flex-1 px-3 py-2 rounded-lg text-xs font-medium transition-all cursor-pointer
                        {{ $activeTab === $key
                            ? 'bg-surface text-text-strong shadow border border-border'
                            : 'text-muted hover:text-text' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>



    {{-- Tabel Data Terhapus --}}
    <div class="rounded-xl border border-border bg-surface overflow-hidden">
        @if($trashedData->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-surface-soft flex items-center justify-center border border-border">
                    <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <p class="text-muted text-sm">Tempat sampah untuk kategori ini kosong.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase">Nama / Identitas</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase hidden sm:table-cell">Dihapus oleh</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase hidden sm:table-cell">Dihapus pada</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-muted uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($trashedData as $item)
                        @php
                            // Tentukan nama tampilan sesuai tipe data
                            $displayName = match($activeTab) {
                                'layanan'         => $item->nama_layanan ?? 'Layanan #' . $item->id,
                                'mr_konteks'      => ($item->layanan->nama_layanan ?? 'Layanan?') . ' — ' . ($item->nama_upr ?? '-') . ' (' . $item->tahun_penilaian . ')',
                                'mr_risiko'       => $item->kode_risiko . ' — ' . \Illuminate\Support\Str::limit($item->peristiwa_risiko ?? '', 50),
                                'mpn_konteks'     => ($item->layanan->nama_layanan ?? 'Layanan?') . ' (' . $item->tahun_penilaian . ')',
                                'mpn_pengetahuan' => \Illuminate\Support\Str::limit($item->nama_pengetahuan ?? '-', 60),
                                'user'            => $item->name . ' (' . $item->email . ')',
                                default           => 'Item #' . $item->id,
                            };
                        @endphp
                        <tr class="hover:bg-surface-soft transition-colors">
                            <td class="px-5 py-4">
                                <span class="text-text font-medium">{{ $displayName }}</span>
                            </td>
                            <td class="px-5 py-4 text-muted text-xs hidden sm:table-cell">
                                {{ $item->deletedBy?->name ?? '—' }}
                                @if($item->deletedBy?->nama_dinas)
                                    <div class="text-muted/70">{{ $item->deletedBy->nama_dinas }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-muted text-xs hidden sm:table-cell">
                                {{ $item->deleted_at?->format('d M Y, H:i') ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    {{-- Tombol Pulihkan --}}
                                    <button
                                        @click="$dispatch('confirm-action', {
                                            wireId: $wire.id,
                                            action: 'restore',
                                            params: ['{{ $activeTab }}', {{ $item->id }}],
                                            title: 'Pulihkan Data',
                                            message: 'Yakin ingin memulihkan data ini?',
                                            confirmText: 'Ya, Pulihkan',
                                            type: 'info'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 text-xs font-medium transition-colors cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        Pulihkan
                                    </button>
                                    {{-- Tombol Hapus Permanen --}}
                                    <button
                                        wire:click="openForceDeleteModal('{{ $activeTab }}', {{ $item->id }}, '{{ addslashes($displayName) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-danger/10 text-danger hover:bg-danger/20 border border-danger/20 text-xs font-medium transition-colors cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus Permanen
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Modal Konfirmasi Hapus Permanen --}}
    @if($confirmingForceDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeForceDeleteModal"></div>
            <div class="relative bg-surface border border-border rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-danger/10 flex items-center justify-center border border-danger/20 shrink-0">
                        <svg class="w-5 h-5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-text-strong">Hapus Permanen</h3>
                        <p class="text-sm text-muted mt-1">Tindakan ini <strong class="text-danger">tidak dapat dibatalkan</strong>. Data akan hilang selamanya.</p>
                    </div>
                </div>

                <div class="rounded-lg bg-surface-soft border border-border p-3">
                    <p class="text-xs text-muted mb-1">Item yang akan dihapus permanen:</p>
                    <p class="text-sm font-medium text-text break-words">{{ $confirmingForceDeleteName }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-text mb-1">
                        Ketik ulang nama di bawah ini untuk mengkonfirmasi:
                    </label>
                    <p class="text-xs text-muted font-mono bg-surface-soft border border-border rounded px-2 py-1 mb-2 break-words">{{ $confirmingForceDeleteName }}</p>
                    <input
                        wire:model="forceDeleteInput"
                        type="text"
                        class="w-full rounded-lg border border-border bg-field px-3 py-2 text-text text-sm focus:ring-2 focus:ring-danger focus:outline-none"
                        placeholder="Ketik nama di atas untuk konfirmasi..."
                    >
                    @error('forceDeleteInput')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="closeForceDeleteModal"
                        class="px-4 py-2 rounded-lg border border-border text-sm text-muted hover:bg-surface-soft cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="executeForceDelete"
                        class="px-4 py-2 rounded-lg bg-danger hover:bg-red-700 text-sm font-semibold text-white cursor-pointer transition-colors">
                        Hapus Permanen
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
