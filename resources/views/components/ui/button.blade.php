@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'block' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold whitespace-nowrap fz-transition focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-[var(--color-primary-600)] text-white hover:bg-[var(--color-primary-700)] active:bg-[var(--color-primary-800)]',
        'secondary' => 'bg-[var(--color-slate-100)] text-[var(--color-slate-800)] hover:bg-[var(--color-slate-200)]',
        'outline' => 'border border-[var(--color-border-strong)] bg-white text-[var(--color-slate-700)] hover:border-[var(--color-primary-300)] hover:text-[var(--color-primary-700)]',
        'ghost' => 'bg-transparent text-[var(--color-slate-700)] hover:bg-[var(--color-slate-100)]',
        'danger' => 'bg-[var(--color-danger-500)] text-white hover:bg-[var(--color-danger-600)]',
        'accent' => 'bg-[var(--color-accent-500)] text-white hover:bg-[var(--color-accent-600)]',
        'link' => 'bg-transparent text-[var(--color-primary-600)] hover:text-[var(--color-primary-700)] underline-offset-4 hover:underline',
    ];

    $sizes = [
        'sm' => 'min-h-9 px-3 text-sm rounded-[var(--radius-md)]',
        'md' => 'min-h-11 px-4 text-sm rounded-[var(--radius-lg)]',
        'lg' => 'min-h-12 px-5 text-base rounded-[var(--radius-lg)]',
        'xl' => 'min-h-14 px-6 text-base rounded-[var(--radius-xl)]',
    ];
@endphp

<button
    type="{{ $type }}"
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => trim("{$base} {$variants[$variant]} {$sizes[$size]} " . ($block ? 'w-full' : '')),
    ]) }}
>
    {{ $slot }}
</button>
