@props([
'spacing' => 'default',
])

@php
    $spacingClasses = [
        'none' => '',
        'sm' => 'py-8 sm:py-10',
        'default' => 'py-12 sm:py-16 lg:py-20',
        'lg' => 'py-16 sm:py-20 lg:py-28',
    ];
@endphp

<section
    {{ $attributes->class([
        'relative',
        $spacingClasses[$spacing] ?? $spacingClasses['default'],
    ]) }}
>
    {{ $slot }}
</section>
