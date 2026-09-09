@props([
    'padding' => true,
    'interactive' => false,
])

@php
    $classes = 'bg-[var(--color-surface)] border border-[var(--color-border)] rounded-[var(--radius-xl)] shadow-[var(--shadow-sm)]';

    if ($padding) {
        $classes .= ' p-5 sm:p-6';
    }

    if ($interactive) {
        $classes .= ' fz-surface-interactive';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
