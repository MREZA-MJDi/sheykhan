@props([
    'title',
    'excerpt' => null,
    'category' => null,
    'date' => null,
    'image' => null,
    'href' => '#',
])

<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-[var(--color-border)] bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[var(--shadow-lg)]">
    <a href="{{ $href }}" class="block">
        <div class="relative aspect-[16/9] overflow-hidden bg-[var(--color-slate-100)]">
            @if($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    loading="lazy"
                >
            @else
                <div class="flex h-full items-center justify-center text-[var(--color-text-subtle)]">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 18.5v-13Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7 16 3-3 2.5 2 2.5-4 2 3"/>
                    </svg>
                </div>
            @endif

            @if($category)
                <span class="absolute right-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-[var(--color-primary-700)] shadow-sm backdrop-blur">
                    {{ $category }}
                </span>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        @if($date)
            <time class="text-xs text-[var(--color-text-muted)]">{{ $date }}</time>
        @endif

        <h3 class="mt-2 text-lg font-bold text-[var(--color-text)]">
            <a href="{{ $href }}" class="transition hover:text-[var(--color-primary-600)]">
                {{ $title }}
            </a>
        </h3>

        @if($excerpt)
            <p class="mt-2 line-clamp-3 text-sm leading-7 text-[var(--color-text-muted)]">
                {{ $excerpt }}
            </p>
        @endif

        <a href="{{ $href }}" class="mt-auto pt-5 text-sm font-bold text-[var(--color-primary-600)]">
            مطالعه مقاله ←
        </a>
    </div>
</article>
