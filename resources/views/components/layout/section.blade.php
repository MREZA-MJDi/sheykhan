@props([
'spacing' => 'default',
])

@php
    $spacings = [
        'sm' => 'py-8 sm:py-10',
        'default' => 'py-12 sm:py-16',
        'lg' => 'py-16 sm:py-20 lg:py-24',
        'none' => 'py-0',
    ];

    $spacingClass = $spacings[$spacing] ?? $spacings['default'];
@endphp

<section
    {{ $attributes->merge([
        'class' => $spacingClass,
    ]) }}
    dir="rtl"
>
    {{ $slot }}
</section>
