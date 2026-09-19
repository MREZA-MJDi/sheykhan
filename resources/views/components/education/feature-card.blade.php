@props([
    'icon',
    'title',
    'description',
    'href' => null,
])

<article class="group h-full rounded-2xl border border-[var(--color-border)] bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[var(--shadow-lg)]">
    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
        {!! $icon !!}
    </div>

    <h3 class="mt-5 text-lg font-bold text-[var(--color-text)]">
        {{ $title }}
    </h3>

    <p class="mt-2 text-sm leading-7 text-[var(--color-text-muted)]">
        {{ $description }}
    </p>

    @if($href)
        <a href="{{ $href }}" class="mt-5 inline-flex text-sm font-bold text-[var(--color-primary-600)]">
            بیشتر بدانید ←
        </a>
    @endif
</article>
