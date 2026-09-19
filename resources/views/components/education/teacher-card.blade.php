@props([
    'name' => 'نام مدرس',
    'role' => 'مدرس',
    'avatar' => null,
    'bio' => null,
    'courses' => null,
    'href' => '#',
])

<article class="group h-full rounded-2xl border border-[var(--color-border)] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[var(--shadow-lg)]">
    <div class="flex items-start gap-4">
        <x-ui.avatar :src="$avatar" :alt="$name" size="lg" />

        <div class="min-w-0 flex-1">
            <h3 class="truncate text-base font-bold">
                <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                    {{ $name }}
                </a>
            </h3>

            <p class="mt-1 text-sm text-[var(--color-text-muted)]">{{ $role }}</p>
        </div>
    </div>

    @if($bio)
        <p class="mt-4 line-clamp-3 text-sm leading-7 text-[var(--color-text-muted)]">
            {{ $bio }}
        </p>
    @endif

    <div class="mt-5 flex items-center justify-between gap-3 border-t border-[var(--color-border)] pt-4 text-xs">
        <span class="text-[var(--color-text-muted)]">
            {{ $courses ?? 0 }} دوره آموزشی
        </span>

        <a href="{{ $href }}" class="font-bold text-[var(--color-primary-600)]">
            مشاهده ←
        </a>
    </div>
</article>
