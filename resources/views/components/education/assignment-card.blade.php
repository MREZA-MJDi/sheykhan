@props([
    'title' => 'تکلیف',
    'course' => null,
    'dueDate' => null,
    'status' => null,
    'description' => null,
    'href' => '#',
])

<article class="fz-surface-interactive min-w-0 p-5">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            @if($course)
                <p class="text-xs font-semibold text-[var(--color-text-muted)]">
                    {{ $course }}
                </p>
            @endif

            <h3 class="mt-1 truncate text-base font-black text-[var(--color-text)]">
                <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                    {{ $title }}
                </a>
            </h3>
        </div>

        @if($status)
            <x-ui.badge variant="info">{{ $status }}</x-ui.badge>
        @endif
    </div>

    @if($description)
        <p class="mt-3 line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">
            {{ $description }}
        </p>
    @endif

    @if($dueDate)
        <div class="mt-4 flex items-center justify-between gap-3 border-t border-[var(--color-border)] pt-4 text-xs text-[var(--color-text-muted)]">
            <span>مهلت تحویل</span>
            <strong class="font-black text-[var(--color-text)]">{{ $dueDate }}</strong>
        </div>
    @endif
</article>