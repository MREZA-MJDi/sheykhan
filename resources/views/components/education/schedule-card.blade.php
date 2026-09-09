@props([
    'title' => 'برنامه کلاس',
    'date' => null,
    'time' => null,
    'teacher' => null,
    'type' => null,
    'status' => null,
    'href' => '#',
])

<article class="fz-surface flex items-center gap-4 p-4">

    <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-700)]">

        @if($date)
            <span class="text-xs">{{ $date }}</span>
        @endif

        @if($time)
            <span class="mt-0.5 text-xs font-bold">{{ $time }}</span>
        @endif

    </div>

    <div class="min-w-0 flex-1">

        <h3 class="truncate text-sm font-bold">
            <a href="{{ $href }}" class="hover:text-[var(--color-primary-600)]">
                {{ $title }}
            </a>
        </h3>

        <div class="mt-1 flex flex-wrap gap-2 text-xs text-[var(--color-text-muted)]">

            @if($teacher)
                <span>{{ $teacher }}</span>
            @endif

            @if($type)
                <span>{{ $type }}</span>
            @endif

        </div>

    </div>

    @if($status)
        <x-ui.badge variant="primary">
            {{ $status }}
        </x-ui.badge>
    @endif

</article>