@props([
    'title' => 'عنوان درس',
    'section' => null,
    'type' => 'ویدئو',
    'duration' => null,
    'completed' => false,
    'href' => '#',
])

<article class="group flex items-center gap-4 rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-surface)] p-4 transition-all hover:border-[var(--color-primary-200)] hover:shadow-sm">

    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">

        @if($completed)
            ✓
        @else
            <span class="text-sm font-bold">▶</span>
        @endif

    </div>

    <div class="min-w-0 flex-1">

        @if($section)
            <p class="text-xs text-[var(--color-text-subtle)]">
                {{ $section }}
            </p>
        @endif

        <h3 class="mt-1 truncate text-sm font-bold">
            <a href="{{ $href }}">
                {{ $title }}
            </a>
        </h3>

        <div class="mt-1 flex gap-3 text-xs text-[var(--color-text-muted)]">
            <span>{{ $type }}</span>

            @if($duration)
                <span>{{ $duration }}</span>
            @endif
        </div>

    </div>

    @if($completed)
        <x-ui.badge variant="success">
            تکمیل شد
        </x-ui.badge>
    @endif

</article>