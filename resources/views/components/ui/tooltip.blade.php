@props([
'text',
'position' => 'top',
])

@php
    $positions = [
        'top' => 'bottom-full right-1/2 mb-2 translate-x-1/2',
        'bottom' => 'top-full right-1/2 mt-2 translate-x-1/2',
        'left' => 'right-full top-1/2 ml-2 -translate-y-1/2',
        'right' => 'left-full top-1/2 mr-2 -translate-y-1/2',
    ];

    $positionClass = $positions[$position] ?? $positions['top'];
@endphp

<span
    x-data="{ show: false }"
    class="relative inline-flex"
    dir="rtl"
>
    <span
        @mouseenter="show = true"
        @mouseleave="show = false"
        @focusin="show = true"
        @focusout="show = false"
        @keydown.escape="show = false"
        class="inline-flex"
    >
        {{ $slot }}
    </span>

    <span
        x-show="show"
        x-cloak
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="translate-y-1 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        role="tooltip"
        class="pointer-events-none absolute {{ $positionClass }} z-[var(--z-popover)] w-max max-w-60 rounded-lg bg-[var(--color-neutral-900)] px-3 py-2 text-xs font-medium leading-5 text-white shadow-[var(--shadow-md)]"
    >
        {{ $text }}
    </span>
</span>
