@props([
'variant' => 'brand',
'style' => 'soft',
'size' => 'md',
])

@php
    $baseClasses = '
        inline-flex
        items-center
        justify-center
        gap-1.5
        rounded-full
        whitespace-nowrap
        font-semibold
        leading-none
        transition-colors
    ';

    $sizes = [
        'sm' => 'min-h-6 px-2 py-0.5 text-xs',
        'md' => 'min-h-7 px-2.5 py-1 text-sm',
        'lg' => 'min-h-8 px-3 py-1.5 text-sm',
    ];

    $variants = [
        'brand' => [
            'soft' => '
                border border-transparent
                bg-[var(--color-brand-100)]
                text-[var(--color-brand-700)]
            ',
            'outline' => '
                border border-[var(--color-brand-500)]
                bg-transparent
                text-[var(--color-brand-700)]
            ',
        ],

        'neutral' => [
            'soft' => '
                border border-transparent
                bg-[var(--color-neutral-100)]
                text-[var(--color-neutral-700)]
            ',
            'outline' => '
                border border-[var(--color-neutral-300)]
                bg-transparent
                text-[var(--color-neutral-700)]
            ',
        ],

        'success' => [
            'soft' => '
                border border-transparent
                bg-[var(--color-success-100)]
                text-[var(--color-success-700)]
            ',
            'outline' => '
                border border-[var(--color-success-500)]
                bg-transparent
                text-[var(--color-success-700)]
            ',
        ],

        'warning' => [
            'soft' => '
                border border-transparent
                bg-[var(--color-warning-100)]
                text-[var(--color-warning-700)]
            ',
            'outline' => '
                border border-[var(--color-warning-500)]
                bg-transparent
                text-[var(--color-warning-700)]
            ',
        ],

        'danger' => [
            'soft' => '
                border border-transparent
                bg-[var(--color-danger-100)]
                text-[var(--color-danger-700)]
            ',
            'outline' => '
                border border-[var(--color-danger-500)]
                bg-transparent
                text-[var(--color-danger-700)]
            ',
        ],

        'info' => [
            'soft' => '
                border border-transparent
                bg-[var(--color-info-100)]
                text-[var(--color-info-700)]
            ',
            'outline' => '
                border border-[var(--color-info-500)]
                bg-transparent
                text-[var(--color-info-700)]
            ',
        ],
    ];

    $selectedVariant = $variants[$variant] ?? $variants['brand'];
    $selectedStyle = $selectedVariant[$style] ?? $selectedVariant['soft'];
    $selectedSize = $sizes[$size] ?? $sizes['md'];

    $classes = collect([
        $baseClasses,
        $selectedSize,
        $selectedStyle,
        $attributes->get('class'),
    ])->filter()->implode(' ');
@endphp

<span
    {{ $attributes->except('class')->merge(['class' => $classes]) }}
>
    @if($slot->isNotEmpty())
        {{ $slot }}
    @endif
</span>
