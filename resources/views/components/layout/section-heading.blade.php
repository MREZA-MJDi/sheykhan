@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'href' => null,
    'linkLabel' => null,
    'center' => false,
])

<div {{ $attributes->class([$center ? 'mx-auto max-w-3xl text-center' : 'max-w-3xl']) }}>
    @if($eyebrow)
        <div class="mb-3 inline-flex items-center gap-2 text-xs font-bold tracking-[0.12em] text-[var(--color-primary-600)]">
            <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-primary-600)]"></span>
            {{ $eyebrow }}
        </div>
    @endif

    <div class="{{ $center ? '' : 'flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between' }}">
        <div class="{{ $center ? 'mx-auto' : 'min-w-0' }}">
            <h2 class="text-2xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-3xl lg:text-4xl">
                {{ $title }}
            </h2>

            @if($description)
                <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--color-text-secondary)] sm:text-base {{ $center ? 'mx-auto' : '' }}">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if($href && $linkLabel)
            <a
                href="{{ $href }}"
                class="inline-flex shrink-0 items-center gap-2 text-sm font-bold text-[var(--color-primary-600)] transition hover:text-[var(--color-primary-700)]"
            >
                <span>{{ $linkLabel }}</span>
                <span aria-hidden="true">←</span>
            </a>
        @endif
    </div>
</div>
