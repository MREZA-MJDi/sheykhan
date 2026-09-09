@props([
'value' => 0,
'label' => null,
'showValue' => true,
'size' => 'md',
'variant' => 'brand',
])

@php
    $value = max(0, min(100, (int) $value));

    $sizes = [
        'sm' => [
            'track' => 'h-1.5',
            'text' => 'text-xs',
            'gap' => 'mt-1.5',
        ],
        'md' => [
            'track' => 'h-2',
            'text' => 'text-sm',
            'gap' => 'mt-2',
        ],
        'lg' => [
            'track' => 'h-3',
            'text' => 'text-sm',
            'gap' => 'mt-2.5',
        ],
    ];

    $variants = [
        'brand' => 'bg-[var(--color-brand-600)]',
        'success' => 'bg-[var(--color-success-600)]',
        'warning' => 'bg-[var(--color-warning-500)]',
        'danger' => 'bg-[var(--color-danger-600)]',
        'info' => 'bg-[var(--color-info-600)]',
    ];

    $selectedSize = $sizes[$size] ?? $sizes['md'];
    $selectedVariant = $variants[$variant] ?? $variants['brand'];
@endphp

<div
    {{ $attributes->except('class')->merge([
        'class' => 'w-full',
        'dir' => 'rtl',
    ]) }}
>
    @if($label || $showValue)
        <div class="flex items-center justify-between gap-4">

            @if($label)
                <span
                    class="{{ $selectedSize['text'] }} font-medium text-[var(--color-text-secondary)]"
                >
                    {{ $label }}
                </span>
            @else
                <span></span>
            @endif

            @if($showValue)
                <span
                    class="{{ $selectedSize['text'] }} font-semibold tabular-nums text-[var(--color-text-primary)]"
                >
                    {{ $value }}٪
                </span>
            @endif

        </div>
    @endif

    <div
        class="{{ $selectedSize['gap'] }} {{ $selectedSize['track'] }} w-full overflow-hidden rounded-full bg-[var(--color-neutral-200)]"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="100"
        @if($label)
        aria-label="{{ $label }}"
        @else
        aria-label="میزان پیشرفت"
        @endif
    >
        <div
            class="h-full rounded-full {{ $selectedVariant }} transition-[width] duration-500 ease-out"
            style="width: {{ $value }}%;"
        ></div>
    </div>
</div>
