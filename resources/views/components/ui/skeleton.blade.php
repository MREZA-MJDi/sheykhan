@props([
    'class' => '',
])

<div
    {{ $attributes->class([
        'animate-pulse rounded-[var(--radius-md)] bg-[var(--color-slate-200)]',
        $class,
    ]) }}
    aria-hidden="true"
></div>