@props([
    'class' => '',
])

<div
    {{ $attributes->merge([
        'class' => "animate-pulse rounded-[var(--radius-md)] bg-[var(--color-slate-200)] {$class}",
    ]) }}
></div>
