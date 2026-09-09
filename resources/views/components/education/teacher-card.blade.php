@props([
    'name' => 'نام مدرس',
    'role' => 'مدرس',
    'avatar' => null,
    'bio' => null,
    'courses' => null,
    'href' => '#',
])

<article class="fz-surface-interactive group h-full p-5">

    <div class="flex items-start gap-4">

        <x-ui.avatar
            :src="$avatar"
            :alt="$name"
            size="lg"
        />

        <div class="min-w-0 flex-1">

            <h3 class="truncate text-base font-bold">
                <a
                    href="{{ $href }}"
                    class="transition-colors hover:text-[var(--color-primary-600)]"
                >
                    {{ $name }}
                </a>
            </h3>

            <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                {{ $role }}
            </p>

        </div>

    </div>

    @if($bio)
        <p class="mt-4 line-clamp-3 text-sm leading-7 text-[var(--color-text-muted)]">
            {{ $bio }}
        </p>
    @endif

    @if($courses)
        <div class="mt-5 border-t border-[var(--color-border)] pt-4 text-xs text-[var(--color-text-muted)]">
            {{ $courses }} دوره آموزشی
        </div>
    @endif

</article>