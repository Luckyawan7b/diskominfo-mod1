@props(['disabled' => false, 'rows' => 2, 'value' => null])

@php
    $minHeight = match((int) $rows) {
        1 => '42px',
        2 => '68px',
        3 => '94px',
        4 => '120px',
        5 => '146px',
        default => ($rows * 26 + 16) . 'px',
    };
@endphp

<textarea 
    x-data="{
        resize() {
            $el.style.height = 'auto';
            const minH = parseInt('{{ $minHeight }}') || 68;
            if ($el.scrollHeight > 0) {
                $el.style.height = Math.max($el.scrollHeight + 6, minH) + 'px';
            } else {
                $el.style.height = minH + 'px';
            }
        }
    }" 
    x-init="
        $nextTick(() => resize());
        let observer = new IntersectionObserver((entries) => {
            if (entries[0] && entries[0].isIntersecting) {
                resize();
            }
        });
        observer.observe($el);
        $watch('$el.value', () => resize());
    "
    x-on:input="resize()"
    x-on:focus="resize()"
    x-on:click="resize()"
    rows="{{ $rows }}"
    style="min-height: {{ $minHeight }};"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'w-full rounded-lg border border-border bg-field px-3.5 py-2.5 text-text text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed resize-none overflow-hidden']) }}
>{{ $value ?? $slot }}</textarea>

