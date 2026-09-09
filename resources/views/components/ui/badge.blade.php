@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'bg-[var(--color-slate-100)] text-[var(--color-slate-700)]',
        'primary' => 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]',
        'success' => 'bg-green-50 text-green-700',
        'warning' => 'bg-amber-50 text-amber-700',
        'danger' => 'bg-red-50 text-red-700',
        'info' => 'bg-cyan-50 text-cyan-700',
    ];
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ' . ($variants[$variant] ?? $variants['neutral']),
    ]) }}
>
    {{ $slot }}
</span>
