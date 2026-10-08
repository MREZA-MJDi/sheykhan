@props([
    'name' => 'نام مدرس',
    'role' => 'مدرس',
    'avatar' => null,
    'bio' => null,
    'courses' => null,
    'href' => '#',
])

<article class="edu-card group h-full min-w-0 p-5 sm:p-6">
    <div class="flex items-start gap-4">
        <div class="relative shrink-0">
            <x-ui.avatar :src="$avatar" :alt="$name" size="lg" />

            <span
                class="absolute -bottom-1 -left-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-[var(--color-success-500)] text-white shadow-sm"
                title="مدرس تاییدشده"
                aria-label="مدرس تاییدشده"
            >
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m7 12 3 3 7-7"/>
                </svg>
            </span>
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="truncate text-base font-black text-[var(--color-text)]">
                <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                    {{ $name }}
                </a>
            </h3>

            <p class="mt-1 truncate text-xs font-semibold text-[var(--color-text-muted)]">
                {{ $role }}
            </p>
        </div>
    </div>

    @if($bio)
        <p class="mt-5 line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">
            {{ $bio }}
        </p>
    @endif

    <div class="mt-5 flex items-center justify-between gap-3 border-t border-[var(--color-border)] pt-4">
        <span class="text-xs font-semibold text-[var(--color-text-muted)]">
            {{ $courses ?? 0 }} دوره آموزشی
        </span>

        <a
            href="{{ $href }}"
            class="inline-flex items-center gap-1.5 text-xs font-black text-[var(--color-primary-600)]"
        >
            <span>مشاهده</span>
            <span aria-hidden="true">←</span>
        </a>
    </div>
</article>