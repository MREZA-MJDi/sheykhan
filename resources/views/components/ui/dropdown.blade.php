@props([
'label' => 'گزینه‌ها',
'align' => 'right',
'width' => 'w-56',
])

@php
    $alignmentClass = match ($align) {
        'left' => 'start-0',
        'center' => 'left-1/2 -translate-x-1/2',
        default => 'end-0',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    x-on:click.outside="open = false"
    class="relative inline-flex"
    dir="rtl"
>
    <div
        class="inline-flex overflow-hidden rounded-xl border border-[var(--color-border-strong)] bg-[var(--color-surface)] shadow-[var(--shadow-xs)]"
    >
        {{-- Main action --}}
        @isset($trigger)
            {{ $trigger }}
        @else
            <button
                type="button"
                class="inline-flex min-h-10 items-center justify-center px-4 py-2 text-sm font-semibold text-[var(--color-text-secondary)] transition-colors duration-200 hover:bg-[var(--color-neutral-50)] hover:text-[var(--color-text-primary)] focus-visible:relative focus-visible:z-10 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]"
            >
                {{ $label }}
            </button>
        @endisset

        {{-- Toggle --}}
        <button
            type="button"
            x-on:click="open = !open"
            :aria-expanded="open.toString()"
            aria-haspopup="menu"
            aria-label="باز کردن منو"
            class="inline-flex min-h-10 w-10 items-center justify-center border-r border-[var(--color-border)] text-[var(--color-text-secondary)] transition-colors duration-200 hover:bg-[var(--color-neutral-50)] hover:text-[var(--color-text-primary)] focus-visible:relative focus-visible:z-10 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]"
        >
            <svg
                x-bind:class="{ 'rotate-180': open }"
                class="h-4 w-4 transition-transform duration-200"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
    </div>

    {{-- Menu --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        role="menu"
        tabindex="-1"
        class="absolute {{ $alignmentClass }} top-[calc(100%+0.5rem)] z-[var(--z-dropdown)] {{ $width }} overflow-hidden rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] py-1 shadow-[var(--shadow-lg)]"
    >
        {{ $slot }}
    </div>
</div>
