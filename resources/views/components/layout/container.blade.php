@props([
'size' => 'default',
])

@php
    $sizes = [
        'sm' => 'max-w-4xl',
        'default' => 'max-w-7xl',
        'lg' => 'max-w-[1440px]',
        'full' => 'max-w-none',
    ];

    $maxWidth = $sizes[$size] ?? $sizes['default'];
@endphp

<div
    {{ $attributes->class([
        'w-full mx-auto px-4 sm:px-6 lg:px-8',
        $maxWidth,
    ]) }}
>
    {{ $slot }}
</div>
