@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'bg-[var(--color-slate-100)] text-[var(--color-slate-700)]',
        'primary' => 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]',
        'success' => 'bg-[var(--color-success-50)] text-[var(--color-success-700)]',
        'warning' => 'bg-[var(--color-warning-50)] text-[var(--color-warning-700)]',
        'danger' => 'bg-[var(--color-danger-50)] text-[var(--color-danger-700)]',
        'info' => 'bg-[var(--color-accent-50)] text-[var(--color-accent-700)]',
    ];

    $variantClass = $variants[$variant] ?? $variants['neutral'];
@endphp

<span
    {{ $attributes->class([
        'inline-flex min-h-6 items-center justify-center gap-1 rounded-full px-2.5 py-1 text-xs font-extrabold leading-none',
        $variantClass,
    ]) }}
>
    {{ $slot }}
</span>