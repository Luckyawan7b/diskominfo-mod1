<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-strong">Daftar Pengetahuan — {{ $konteks->tahun_penilaian }}</h1>
            <p class="text-sm text-muted mt-1">Kelola inventarisasi dan dokumentasi pengetahuan layanan</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('konteks-mpn.form', $konteks) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm text-text hover:bg-surface-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Hub Konteks
            </a>
            @if ($isEditable)
                <a href="{{ route('mpn.pengetahuan.form', ['konteks' => $konteks, 'pengetahuan' => 'new']) }}" wire:navigate
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-sm font-semibold text-white shadow-lg transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pengetahuan Baru
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-success-bg border border-success/30 text-success text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="rounded-xl border border-border bg-surface overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-surface-soft">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider w-12">No</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Nama Pengetahuan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Sub Fitur</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Layanan Prioritas</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Terdokumentasi</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-muted uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($pengetahuans as $i => $item)
                    <tr class="hover:bg-surface-soft transition-colors" wire:key="pengetahuan-{{ $item->id }}">
                        <td class="px-5 py-4 text-text">{{ $i + 1 }}</td>
                        <td class="px-5 py-4">
                            <span class="text-text-strong font-medium block">{{ $item->nama_pengetahuan }}</span>
                        </td>
                        <td class="px-5 py-4 text-text">
                            {{ $item->nama_sub_fitur ?: '-' }}
                        </td>
                        <td class="px-5 py-4 text-text">
                            {{ $item->layanan_prioritas ?: '-' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($item->apakah_terdokumentasi)
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-bold bg-success-bg border border-success text-success">
                                    Ya
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-bold bg-danger-bg border border-danger text-danger">
                                    Tidak
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('mpn.pengetahuan.form', ['konteks' => $konteks, 'pengetahuan' => $item->id]) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-accent hover:bg-accent/10 text-sm font-medium transition-colors">
                                {{ $isEditable ? 'Edit' : 'Lihat' }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            @if ($isEditable)
                                <button type="button"
                                    @click="$dispatch('confirm-action', {
                                        wireId: $wire.id,
                                        action: 'deletePengetahuan',
                                        params: [{{ $item->id }}],
                                        title: 'Hapus Pengetahuan',
                                        message: 'Apakah Anda yakin ingin menghapus pengetahuan ini?',
                                        subMessage: 'Tindakan ini akan memindahkan data ke tempat sampah.',
                                        confirmText: 'Ya, Hapus',
                                        type: 'danger'
                                    })"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-danger hover:bg-danger/10 text-sm font-medium transition-colors cursor-pointer ml-1">
                                    Hapus
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p class="text-muted text-sm">Belum ada daftar pengetahuan</p>
                                <p class="text-muted opacity-75 text-xs mt-1">Klik "Tambah Pengetahuan Baru" untuk memulai
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
