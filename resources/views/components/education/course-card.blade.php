@props([
    'title' => 'دوره آموزشی',
    'description' => 'توضیحات دوره در این بخش نمایش داده می‌شود.',
    'image' => null,
    'category' => null,
    'teacher' => null,
    'lessons' => null,
    'duration' => null,
    'price' => null,
    'level' => null,
    'href' => '#',
])

<article class="fz-surface-interactive group flex h-full flex-col overflow-hidden">

    <a href="{{ $href }}" class="block">

        <div class="relative aspect-[16/9] overflow-hidden bg-[var(--color-slate-100)]">

            @if($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                >
            @else
                <div class="flex h-full items-center justify-center text-[var(--color-text-subtle)]">
                    <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 5h16v14H4z"/>
                        <path d="m4 15 4-4 3 3 3-4 6 6"/>
                    </svg>
                </div>
            @endif

            @if($category)
                <span class="absolute right-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-medium text-[var(--color-primary-700)] shadow-sm backdrop-blur">
                    {{ $category }}
                </span>
            @endif

        </div>

    </a>

    <div class="flex flex-1 flex-col p-5">

        @if($level)
            <span class="mb-2 text-xs font-medium text-[var(--color-text-muted)]">
                سطح {{ $level }}
            </span>
        @endif

        <h3 class="text-lg font-bold text-[var(--color-text)]">
            <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                {{ $title }}
            </a>
        </h3>

        <p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--color-text-muted)]">
            {{ $description }}
        </p>

        @if($teacher)
            <div class="mt-4 flex items-center gap-2 text-sm text-[var(--color-text-muted)]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="8" r="3"/>
                    <path d="M5 20c.8-4 3-6 7-6s6.2 2 7 6"/>
                </svg>

                <span>{{ $teacher }}</span>
            </div>
        @endif

        <div class="mt-auto pt-5">

            <div class="flex flex-wrap items-center gap-4 border-t border-[var(--color-border)] pt-4 text-xs text-[var(--color-text-muted)]">

                @if($lessons)
                    <span>{{ $lessons }} درس</span>
                @endif

                @if($duration)
                    <span>{{ $duration }}</span>
                @endif

                @if($price !== null)
                    <span class="ms-auto font-bold text-[var(--color-primary-600)]">
                        {{ $price }}
                    </span>
                @endif

            </div>

        </div>

    </div>

</article>