@props([
    'title' => 'کلاس آنلاین',
    'course' => null,
    'teacher' => null,
    'date' => null,
    'time' => null,
    'status' => 'به‌زودی',
    'href' => '#',
])

<article class="home-card group relative overflow-hidden p-5 sm:p-6">
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-[var(--color-primary-600)] to-[var(--color-accent-500)]"></div>

    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            @if($course)
                <p class="text-[11px] font-bold text-[var(--color-primary-600)]">{{ $course }}</p>
            @endif
            <h3 class="mt-1 text-lg font-black leading-snug text-[var(--color-text)]">
                <a href="{{ $href }}" class="transition hover:text-[var(--color-primary-600)]">{{ $title }}</a>
            </h3>
        </div>

        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/>
            </svg>
        </span>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-3 rounded-2xl bg-[var(--color-background-soft)] p-4 text-xs">
        <div>
            <span class="text-[var(--color-text-muted)]">تاریخ</span>
            <strong class="mt-1 block text-[var(--color-text)]">{{ $date ?: '—' }}</strong>
        </div>
        <div>
            <span class="text-[var(--color-text-muted)]">ساعت</span>
            <strong class="mt-1 block text-[var(--color-text)]">{{ $time ?: '—' }}</strong>
        </div>
        @if($teacher)
            <div class="col-span-2 border-t border-[var(--color-border)] pt-3">
                <span class="text-[var(--color-text-muted)]">مدرس</span>
                <strong class="ms-2 text-[var(--color-text)]">{{ $teacher }}</strong>
            </div>
        @endif
    </div>

    <div class="mt-5 flex items-center justify-between gap-3">
        <x-ui.badge variant="{{ $status === 'در حال برگزاری' ? 'danger' : 'success' }}">{{ $status }}</x-ui.badge>
        <a href="{{ $href }}" class="text-xs font-black text-[var(--color-primary-600)]">مشاهده کلاس ←</a>
    </div>
</article>
