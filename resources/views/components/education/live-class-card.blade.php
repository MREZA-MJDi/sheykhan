@props([
    'title' => 'کلاس آنلاین',
    'course' => null,
    'teacher' => null,
    'date' => null,
    'time' => null,
    'status' => 'به‌زودی',
    'href' => '#',
])

<article class="group relative overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-sm transition-all hover:border-[var(--color-primary-200)] hover:shadow-md">

    <div class="absolute inset-x-0 top-0 h-1 bg-[var(--color-primary-600)]"></div>

    <div class="flex items-start justify-between gap-4">

        <div class="min-w-0">

            @if($course)
                <p class="text-xs text-[var(--color-text-muted)]">
                    {{ $course }}
                </p>
            @endif

            <h3 class="mt-1 text-lg font-bold">
                <a href="{{ $href }}" class="hover:text-[var(--color-primary-600)]">
                    {{ $title }}
                </a>
            </h3>

        </div>

        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
            ●
        </span>

    </div>

    <div class="mt-5 space-y-3 text-sm text-[var(--color-text-muted)]">

        @if($teacher)
            <div class="flex justify-between gap-4">
                <span>مدرس</span>
                <strong class="text-[var(--color-text)]">{{ $teacher }}</strong>
            </div>
        @endif

        @if($date)
            <div class="flex justify-between gap-4">
                <span>تاریخ</span>
                <strong class="text-[var(--color-text)]">{{ $date }}</strong>
            </div>
        @endif

        @if($time)
            <div class="flex justify-between gap-4">
                <span>ساعت</span>
                <strong class="text-[var(--color-text)]">{{ $time }}</strong>
            </div>
        @endif

    </div>

    <div class="mt-5 border-t border-[var(--color-border)] pt-4">

        <div class="flex items-center justify-between gap-3">

            <x-ui.badge variant="success">
                {{ $status }}
            </x-ui.badge>

            <a
                href="{{ $href }}"
                class="text-sm font-bold text-[var(--color-primary-600)] transition-colors hover:text-[var(--color-primary-700)]"
            >
                ورود به کلاس
            </a>

        </div>

    </div>

</article>