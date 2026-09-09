@props([
'href' => null,
'variant' => 'primary',
'size' => 'md',
'type' => 'button',
'disabled' => false,
'loading' => false,
'fullWidth' => false,
])

@php
    $baseClasses = '
        inline-flex items-center justify-center gap-2
        rounded-full
        border
        font-semibold
        text-sm
        leading-none
        whitespace-nowrap
        transition-all duration-200
        focus-visible:outline-none
        focus-visible:ring-4
        disabled:cursor-not-allowed
        disabled:opacity-50
        active:translate-y-px
    ';

    $variants = [
        'primary' => '
            border-[var(--color-brand-600)]
            bg-[var(--color-brand-600)]
            text-white
            shadow-[var(--shadow-sm)]
            hover:border-[var(--color-brand-700)]
            hover:bg-[var(--color-brand-700)]
            focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
        ',

        'secondary' => '
            border-[var(--color-border-strong)]
            bg-[var(--color-surface)]
            text-[var(--color-text-primary)]
            shadow-[var(--shadow-sm)]
            hover:border-[var(--color-neutral-400)]
            hover:bg-[var(--color-neutral-50)]
            hover:text-[var(--color-neutral-950)]
            focus-visible:ring-[color-mix(in_srgb,var(--color-neutral-300)_70%,transparent)]
        ',

        'soft' => '
            border-[var(--color-brand-100)]
            bg-[var(--color-brand-50)]
            text-[var(--color-brand-700)]
            hover:border-[var(--color-brand-200)]
            hover:bg-[var(--color-brand-100)]
            focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
        ',

        'ghost' => '
            border-transparent
            bg-transparent
            text-[var(--color-text-secondary)]
            hover:bg-[var(--color-neutral-100)]
            hover:text-[var(--color-text-primary)]
            focus-visible:ring-[color-mix(in_srgb,var(--color-neutral-200)_70%,transparent)]
        ',

        'danger' => '
            border-[var(--color-danger-600)]
            bg-[var(--color-danger-600)]
            text-white
            shadow-[var(--shadow-sm)]
            hover:border-[var(--color-danger-700)]
            hover:bg-[var(--color-danger-700)]
            focus-visible:ring-[color-mix(in_srgb,var(--color-danger-200)_70%,transparent)]
        ',
    ];

    $sizes = [
        'sm' => 'min-h-9 px-4 py-2 text-xs',
        'md' => 'min-h-11 px-6 py-3',
        'lg' => 'min-h-12 px-7 py-3.5 text-base',
    ];

    $classes = collect([
        $baseClasses,
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $fullWidth ? 'w-full' : null,
        $loading ? 'cursor-wait' : null,
    ])->filter()->implode(' ');
@endphp

@if($href && !$disabled && !$loading)

    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <svg
                class="h-4 w-4 animate-spin"
                viewBox="0 0 24 24"
                fill="none"
                aria-hidden="true"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-90"
                    fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                />
            </svg>
        @endif

        {{ $slot }}
    </a>

@else

    <button
        type="{{ $type }}"
        @disabled($disabled || $loading)
        aria-busy="{{ $loading ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <svg
                class="h-4 w-4 animate-spin"
                viewBox="0 0 24 24"
                fill="none"
                aria-hidden="true"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-90"
                    fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                />
            </svg>
        @endif

        {{ $slot }}
    </button>

@endif
