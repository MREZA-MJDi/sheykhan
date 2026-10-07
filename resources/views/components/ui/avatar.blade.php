@props([
    'src' => null,
    'alt' => '',
    'size' => 'md',
    'fallback' => null,
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-lg',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $fallbackText = $fallback ?: mb_substr(trim((string) $alt), 0, 1);
@endphp

@if($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        {{ $attributes->class([
            'shrink-0 overflow-hidden rounded-full object-cover bg-[var(--color-slate-100)]',
            $sizeClass,
        ]) }}
        loading="{{ $attributes->get('loading', 'lazy') }}"
    >
@else
    <span
        {{ $attributes->class([
            'flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-[var(--color-primary-100)] font-black text-[var(--color-primary-700)]',
            $sizeClass,
        ]) }}
        aria-hidden="{{ $alt ? 'false' : 'true' }}"
    >
        {{ $fallbackText ?: 'ش' }}
    </span>
@endif