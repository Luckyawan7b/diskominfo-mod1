@props([
    'index'            => 0,
    'total'            => 1,
    'title'            => 'Baris',
    'subtitle'         => null,
    'isEditable'       => false,
    'saveAction'       => null,
    'saveLabel'        => 'Simpan Baris Ini',
    'saveId'           => null,
    'removeAction'     => null,
    'removeParams'     => null,
    'removeTitle'      => 'Hapus Data',
    'removeMessage'    => 'Hapus baris ini?',
    'removeSubMessage' => 'Data baris ini akan dihapus.',
    'moveUpAction'     => null,
    'moveDownAction'   => null,
])

@php
    $paramsRemove = $removeParams ?? [$index];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-surface p-6']) }}>
    <div class="flex items-center justify-between gap-2 mb-5 pb-3 border-b border-border">
        <div class="flex items-center gap-2.5">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-primary text-xs font-bold shrink-0">
                {{ $index + 1 }}
            </span>
            <span class="text-sm font-bold text-text-strong tracking-wide">
                {{ $title }}
                @if(filled($subtitle))
                    &mdash; <span class="font-normal text-muted text-xs">{{ $subtitle }}</span>
                @endif
            </span>
        </div>

        @if($isEditable)
            <div class="flex items-center gap-3">
                @if($moveUpAction)
                    <button type="button"
                        wire:click="{{ $moveUpAction }}({{ $index }})"
                        @disabled($index === 0)
                        title="Pindah ke atas"
                        class="text-xs text-muted hover:text-text disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                        &uarr; Atas
                    </button>
                @endif

                @if($moveDownAction)
                    <button type="button"
                        wire:click="{{ $moveDownAction }}({{ $index }})"
                        @disabled($index >= $total - 1)
                        title="Pindah ke bawah"
                        class="text-xs text-muted hover:text-text disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                        &darr; Bawah
                    </button>
                @endif

                @if($removeAction)
                    <button type="button"
                        @click="$dispatch('confirm-action', {
                            wireId: $wire.id,
                            action: @js($removeAction),
                            params: @js($paramsRemove),
                            title: @js($removeTitle),
                            message: @js($removeMessage),
                            subMessage: @js($removeSubMessage),
                            confirmText: 'Ya, Hapus',
                            type: 'danger'
                        })"
                        class="text-xs text-danger hover:opacity-80 hover:underline cursor-pointer flex items-center gap-1">
                        Hapus
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div>
        {{ $slot }}
    </div>

    @if($isEditable && $saveAction)
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-border">
            <button type="button" wire:click="{{ $saveAction }}({{ $index }})" @if($saveId) id="{{ $saveId }}" @endif
                wire:loading.attr="disabled"
                wire:target="{{ $saveAction }}({{ $index }})"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-dark disabled:opacity-50 text-sm font-semibold text-white shadow transition-all cursor-pointer">
                <span wire:loading.remove wire:target="{{ $saveAction }}({{ $index }})">
                    {{ $saveLabel }}
                </span>
                <span wire:loading.inline-flex wire:target="{{ $saveAction }}({{ $index }})" class="items-center gap-2">
                    <svg class="w-4 h-4 animate-spin text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Menyimpan...</span>
                </span>
            </button>
        </div>
    @endif
</div>
