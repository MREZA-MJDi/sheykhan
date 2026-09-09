@props([
'size' => 'default',
])

@php
    $sizes = [
        'sm' => 'max-w-3xl',
        'default' => 'max-w-7xl',
        'lg' => 'max-w-[90rem]',
        'full' => 'max-w-none',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['default'];
@endphp

<div
    {{ $attributes->merge([
        'class' => "mx-auto w-full {$sizeClass} px-4 sm:px-6 lg:px-8",
    ]) }}
    dir="rtl"
>
    {{ $slot }}
</div>
