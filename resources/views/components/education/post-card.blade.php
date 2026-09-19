@props([
    'title',
    'excerpt' => null,
    'category' => null,
    'date' => null,
    'image' => null,
    'href' => '#',
])

<article class="home-card group flex h-full flex-col overflow-hidden">
    <a href="{{ $href }}" class="block">
        <div class="relative aspect-[16/9] overflow-hidden bg-[var(--color-slate-100)]">
            @if($image)
                <img src="{{ $image }}" alt="{{ $title }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.04]" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>
            @else
                <div class="flex h-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(104,121,245,.18),transparent_40%),#f3f5fb] text-[var(--color-primary-600)]">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 18.5v-13Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7 16 3-3 2.5 2 2.5-4 2 3"/>
                    </svg>
                </div>
            @endif

            @if($category)
                <span class="absolute right-4 top-4 rounded-full border border-white/50 bg-white/90 px-3 py-1.5 text-[11px] font-bold text-[var(--color-primary-700)] shadow-sm backdrop-blur">
                    {{ $category }}
                </span>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            @if($date)
                <time class="text-[11px] font-semibold text-[var(--color-text-muted)]">{{ $date }}</time>
            @endif
            <span class="ms-auto text-[11px] font-bold text-[var(--color-primary-600)]">محتوا</span>
        </div>

        <h3 class="mt-3 text-lg font-black leading-snug text-[var(--color-text)]">
            <a href="{{ $href }}" class="transition hover:text-[var(--color-primary-600)]">{{ $title }}</a>
        </h3>

        @if($excerpt)
            <p class="mt-2 line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">{{ $excerpt }}</p>
        @endif

        <a href="{{ $href }}" class="mt-auto pt-5 text-sm font-black text-[var(--color-primary-600)]">
            مطالعه مقاله ←
        </a>
    </div>
</article>
