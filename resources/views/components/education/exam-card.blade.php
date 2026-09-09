@props([
    'title' => 'آزمون',
    'course' => null,
    'questions' => null,
    'duration' => null,
    'date' => null,
    'status' => null,
    'href' => '#',
])

<article class="fz-surface-interactive p-5">

    <div class="flex items-start justify-between gap-4">

        <div>
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

        @if($status)
            <x-ui.badge variant="warning">
                {{ $status }}
            </x-ui.badge>
        @endif

    </div>

    <div class="mt-5 flex flex-wrap gap-4 text-sm text-[var(--color-text-muted)]">

        @if($questions)
            <span>{{ $questions }} سؤال</span>
        @endif

        @if($duration)
            <span>{{ $duration }}</span>
        @endif

        @if($date)
            <span>{{ $date }}</span>
        @endif

    </div>

</article>