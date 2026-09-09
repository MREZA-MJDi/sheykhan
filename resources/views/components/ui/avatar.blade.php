@props([
    'src' => null,
    'alt' => '',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-lg',
    ];
@endphp

@if($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        {{ $attributes->merge([
            'class' => "shrink-0 rounded-full object-cover {$sizes[$size]}",
        ]) }}
    >
@else
    <div
        {{ $attributes->merge([
            'class' => "flex shrink-0 items-center justify-center rounded-full bg-[var(--color-primary-100)] font-bold text-[var(--color-primary-700)] {$sizes[$size]}",
        ]) }}
    >
        {{ $slot }}
    </div>
@endif
