@props([
    'title' => 'آزمون',
    'course' => null,
    'questions' => null,
    'duration' => null,
    'date' => null,
    'status' => null,
    'href' => '#',
])

<article class="fz-surface-interactive min-w-0 p-5">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            @if($course)
                <p class="text-xs font-semibold text-[var(--color-text-muted)]">{{ $course }}</p>
            @endif

            <h3 class="mt-1 truncate text-lg font-black text-[var(--color-text)]">
                <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                    {{ $title }}
                </a>
            </h3>
        </div>

        @if($status)
            <x-ui.badge variant="warning">{{ $status }}</x-ui.badge>
        @endif
    </div>

    <div class="mt-5 flex flex-wrap gap-2">
        @if($questions)
            <span class="rounded-full bg-[var(--color-background-soft)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                {{ $questions }} سؤال
            </span>
        @endif

        @if($duration)
            <span class="rounded-full bg-[var(--color-background-soft)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                {{ $duration }}
            </span>
        @endif

        @if($date)
            <span class="rounded-full bg-[var(--color-background-soft)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                {{ $date }}
            </span>
        @endif
    </div>
</article>