@props([
    'title' => null,
    'description' => null,
])

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[var(--z-modal)] flex items-center justify-center p-4"
>
    <div
        class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <div
        class="relative z-10 w-full max-w-lg overflow-hidden rounded-[var(--radius-xl)] bg-white shadow-[var(--shadow-xl)]"
        @click.stop
    >
        @if($title || $description)
            <div class="border-b border-[var(--color-border)] px-5 py-4 sm:px-6">
                @if($title)
                    <h2 class="text-lg font-bold text-[var(--color-text)]">
                        {{ $title }}
                    </h2>
                @endif

                @if($description)
                    <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        <div class="p-5 sm:p-6">
            {{ $slot }}
        </div>
    </div>
</div>
