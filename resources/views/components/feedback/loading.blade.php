@props([
'label' => 'در حال بارگذاری...',
'size' => 'md',
'fullscreen' => false,
])

@php
    $sizes = [
        'sm' => 'h-4 w-4',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $wrapperClass = $fullscreen
        ? 'fixed inset-0 z-[var(--z-modal)] flex items-center justify-center bg-[var(--color-surface)]/80 backdrop-blur-sm'
        : 'flex items-center justify-center';
@endphp

<div
    {{ $attributes->merge([
        'class' => $wrapperClass,
        'role' => 'status',
        'aria-live' => 'polite',
    ]) }}
    dir="rtl"
>
    <div class="flex flex-col items-center gap-3 text-center">
        <svg
            class="{{ $sizeClass }} animate-spin text-[var(--color-brand-600)]"
            viewBox="0 0 24 24"
            fill="none"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="9"
                stroke="currentColor"
                stroke-width="3"
            />
            <path
                class="opacity-90"
                fill="currentColor"
                d="M12 3a9 9 0 0 1 8.485 6H17.2A5.5 5.5 0 0 0 12 6.5V3Z"
            />
        </svg>

        @if($label)
            <span class="text-sm font-medium text-[var(--color-text-secondary)]">
                {{ $label }}
            </span>
        @endif
    </div>
</div>
