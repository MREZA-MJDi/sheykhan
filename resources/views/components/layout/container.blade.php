@props([
    'size' => 'default',
])

@php
    $sizes = [
        'sm' => 'max-w-4xl',
        'default' => 'max-w-7xl',
        'lg' => 'max-w-[1440px]',
        'xl' => 'max-w-[1536px]',
        '2xl' => 'max-w-[1600px]',
        'wide' => 'max-w-[1600px]',
        'full' => 'max-w-none',
    ];

    $maxWidth = $sizes[$size] ?? $sizes['default'];
@endphp

<div
    {{ $attributes->class([
        'mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10',
        $maxWidth,
    ]) }}
>
    {{ $slot }}
</div>
