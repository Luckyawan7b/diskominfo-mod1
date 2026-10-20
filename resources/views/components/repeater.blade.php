@props([
    'items'         => [],
    'isEditable'    => false,
    'addAction'     => 'addBlock',
    'addLabel'      => 'Tambah Baris',
    'addId'         => null,
    'emptyTitle'    => 'Belum ada data.',
    'emptySubtitle' => 'Klik tombol di bawah untuk menambahkan yang pertama.',
])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @if(count($items) > 0)
        {{ $slot }}
    @else
        <div class="rounded-xl border border-dashed border-border px-4 py-12 text-center text-muted text-sm bg-surface-soft">
            <p class="font-medium text-text mb-1">{{ $emptyTitle }}</p>
            @if($isEditable && $emptySubtitle)
                <p class="text-xs text-muted">{{ $emptySubtitle }}</p>
            @endif
        </div>
    @endif

    @if($isEditable && $addAction)
        <div>
            <button type="button" wire:click="{{ $addAction }}" @if($addId) id="{{ $addId }}" @endif
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-dashed border-border text-sm text-muted hover:text-text hover:border-border-strong transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $addLabel }}
            </button>
        </div>
    @endif
</div>
