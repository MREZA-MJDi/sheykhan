@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'href' => null,
    'linkLabel' => null,
    'center' => false,
])

<div class="{{ $center ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl' }}">
    @if($eyebrow)
        <div class="mb-3 text-xs font-bold tracking-[0.16em] text-[var(--color-primary-600)]">
            {{ $eyebrow }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl lg:text-4xl">
                {{ $title }}
            </h2>

            @if($description)
                <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--color-text-muted)] sm:text-base">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if($href && $linkLabel)
            <a
                href="{{ $href }}"
                class="shrink-0 text-sm font-bold text-[var(--color-primary-600)] transition hover:text-[var(--color-primary-700)]"
            >
                {{ $linkLabel }} ←
            </a>
        @endif
    </div>
</div>
