@props([
    'padding' => true,
    'interactive' => false,
])

@php
    $classes = $interactive
        ? 'fz-surface-interactive'
        : 'fz-surface';
@endphp

<div {{ $attributes->class([$classes, $padding ? 'p-5 sm:p-6' : '']) }}>
    {{ $slot }}
</div>