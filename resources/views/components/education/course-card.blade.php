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
    'grades' => [],
    'previewLessons' => [],
    'href' => '#',
])

<article class="edu-card group flex h-full min-w-0 flex-col">
    <a
        href="{{ $href }}"
        class="block"
        aria-label="مشاهده دوره {{ $title }}"
    >
        <div class="relative aspect-[16/9] overflow-hidden bg-[var(--color-slate-100)]">
            @if($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.045]"
                    loading="lazy"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent"></div>
            @else
                <div class="flex h-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(104,121,245,.20),transparent_38%),linear-gradient(135deg,#eef2ff,#f7fbff)] text-[var(--color-primary-600)]">
                    <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M4 5h16v14H4z"/>
                        <path d="m4 15 4-4 3 3 3-4 6 6"/>
                    </svg>
                </div>
            @endif

            <div class="absolute inset-x-4 top-4 flex items-start justify-between gap-3">
                @if($category)
                    <span class="home-card-badge">
                        <span class="home-badge-dot"></span>
                        {{ $category }}
                    </span>
                @endif

                @if($price === 'رایگان')
                    <span class="home-card-badge is-free">پیش‌نمایش رایگان</span>
                @endif
            </div>
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 flex-wrap gap-1.5">
                @foreach(array_slice(array_filter($grades), 0, 3) as $grade)
                    <span class="rounded-full bg-[var(--color-background-soft)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-text-tertiary)]">
                        {{ $grade }}
                    </span>
                @endforeach

                @if(empty($grades) && $level)
                    <span class="rounded-full bg-[var(--color-background-soft)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-text-tertiary)]">
                        سطح {{ $level }}
                    </span>
                @endif
            </div>

            @if($price !== null)
                <span class="ms-auto shrink-0 text-sm font-black text-[var(--color-primary-600)]">
                    {{ $price }}
                </span>
            @endif
        </div>

        <h3 class="mt-4 text-xl font-black leading-snug text-[var(--color-text)]">
            <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                {{ $title }}
            </a>
        </h3>

        @if($description)
            <p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                {{ $description }}
            </p>
        @endif

        @if($teacher)
            <div class="mt-4 flex min-w-0 items-center gap-2 text-xs font-semibold text-[var(--color-text-muted)]">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-600)]" aria-hidden="true">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <circle cx="12" cy="8" r="3"/><path d="M5 20c.8-4 3-6 7-6s6.2 2 7 6"/>
                    </svg>
                </span>
                <span class="truncate">{{ $teacher }}</span>
            </div>
        @endif

        @if($previewLessons)
            <div class="home-course-lessons">
                <span class="home-course-lessons-label">داخل این مسیر</span>
                <div class="home-course-lessons-list">
                    @foreach(array_slice($previewLessons, 0, 3) as $previewLesson)
                        <span class="{{ $previewLesson['is_free'] ? 'is-preview' : '' }}">
                            <i aria-hidden="true">✓</i>
                            {{ $previewLesson['title'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-auto pt-5">
            <div class="flex flex-wrap items-center gap-3 border-t border-[var(--color-border)] pt-4 text-xs font-semibold text-[var(--color-text-muted)]">
                @if($lessons)
                    <span>{{ $lessons }} درس</span>
                @endif

                @if($duration)
                    <span>{{ $duration }}</span>
                @endif

                <span class="ms-auto font-black text-[var(--color-primary-600)]">
                    مشاهده دوره ←
                </span>
            </div>
        </div>
    </div>
</article>