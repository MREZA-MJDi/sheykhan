@props([
    'title' => 'کلاس آموزشی',
    'course' => null,
    'teacher' => null,
    'date' => null,
    'time' => null,
    'students' => null,
    'status' => null,
    'href' => '#',
])

<article class="fz-surface-interactive min-w-0 p-5">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <span class="text-xs font-semibold text-[var(--color-text-muted)]">
                {{ $course ?? 'کلاس آموزشی' }}
            </span>

            <h3 class="mt-1 truncate text-base font-black text-[var(--color-text)]">
                <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                    {{ $title }}
                </a>
            </h3>
        </div>

        @if($status)
            <x-ui.badge variant="primary">{{ $status }}</x-ui.badge>
        @endif
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3 rounded-2xl bg-[var(--color-background-soft)] p-4 text-xs">
        @if($teacher)
            <div>
                <span class="block text-[var(--color-text-muted)]">مدرس</span>
                <strong class="mt-1 block truncate text-[var(--color-text)]">{{ $teacher }}</strong>
            </div>
        @endif

        @if($date)
            <div>
                <span class="block text-[var(--color-text-muted)]">تاریخ</span>
                <strong class="mt-1 block text-[var(--color-text)]">{{ $date }}</strong>
            </div>
        @endif

        @if($time)
            <div>
                <span class="block text-[var(--color-text-muted)]">ساعت</span>
                <strong class="mt-1 block text-[var(--color-text)]">{{ $time }}</strong>
            </div>
        @endif

        @if($students !== null)
            <div>
                <span class="block text-[var(--color-text-muted)]">دانش‌آموز</span>
                <strong class="mt-1 block text-[var(--color-text)]">{{ $students }}</strong>
            </div>
        @endif
    </div>
</article>