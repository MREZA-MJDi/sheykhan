@props([
    'icon',
    'title',
    'description',
    'href' => null,
])

<article {{ $attributes->class([
    'home-feature-card home-card h-full p-5 sm:p-6',
]) }}>
    <div class="relative z-[1] flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
        {!! $icon !!}
    </div>

    <h3 class="relative z-[1] mt-5 text-lg font-black text-[var(--color-text)]">
        {{ $title }}
    </h3>

    <p class="relative z-[1] mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
        {{ $description }}
    </p>

    @if($href)
        <a href="{{ $href }}" class="relative z-[1] mt-5 inline-flex text-sm font-black text-[var(--color-primary-600)]">
            بیشتر بدانید ←
        </a>
    @endif
</article>
