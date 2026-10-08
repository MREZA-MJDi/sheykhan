@props([
    'title' => null,
    'description' => null,
    'open' => false,
    'closeable' => true,
])

<div x-data="{ open: @js($open) }" class="relative">
    @isset($trigger)
        <span @click="open = true">
            {{ $trigger }}
        </span>
    @endisset

    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[var(--z-modal)] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="{{ $closeable ? 'open = false' : '' }}"
    >
        <div
            class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm"
            @click="{{ $closeable ? 'open = false' : '' }}"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="translate-y-2 scale-[.98] opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            class="relative z-10 w-full max-w-lg overflow-hidden rounded-[var(--radius-2xl)] border border-[var(--color-border)] bg-white shadow-[var(--shadow-xl)]"
            @click.stop
        >
            @if($title || $description)
                <div class="border-b border-[var(--color-border)] px-5 py-4 sm:px-6">
                    @if($title)
                        <h2 class="text-lg font-black text-[var(--color-text)]">{{ $title }}</h2>
                    @endif

                    @if($description)
                        <p class="mt-1 text-sm leading-7 text-[var(--color-text-muted)]">{{ $description }}</p>
                    @endif
                </div>
            @endif

            <div class="p-5 sm:p-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>