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

<article class="fz-surface-interactive p-5">

    <div class="flex items-start justify-between gap-4">

        <div class="min-w-0">

            <span class="text-xs text-[var(--color-text-muted)]">
                {{ $course ?? 'کلاس آموزشی' }}
            </span>

            <h3 class="mt-1 text-base font-bold">
                <a
                    href="{{ $href }}"
                    class="hover:text-[var(--color-primary-600)]"
                >
                    {{ $title }}
                </a>
            </h3>

        </div>

        @if($status)
            <x-ui.badge variant="primary">
                {{ $status }}
            </x-ui.badge>
        @endif

    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 text-sm text-[var(--color-text-muted)] sm:grid-cols-2">

        @if($teacher)
            <div class="flex items-center gap-2">
                <span>مدرس:</span>
                <strong class="text-[var(--color-text)]">{{ $teacher }}</strong>
            </div>
        @endif

        @if($date)
            <div>
                تاریخ: <strong class="text-[var(--color-text)]">{{ $date }}</strong>
            </div>
        @endif

        @if($time)
            <div>
                ساعت: <strong class="text-[var(--color-text)]">{{ $time }}</strong>
            </div>
        @endif

        @if($students)
            <div>
                {{ $students }} دانش‌آموز
            </div>
        @endif

    </div>

</article>