@props([
    'items' => [],
])

<nav
    class="flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm"
    aria-label="مسیر صفحه"
    dir="rtl"
>
    <a
        href="{{ url('/') }}"
        class="inline-flex shrink-0 items-center gap-2 text-[var(--color-text-muted)] transition-colors hover:text-[var(--color-text)]"
    >
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10.707 2.293a1 1 0 0 0-1.414 0l-7 7a1 1 0 0 0 1.414 1.414L4 10.414V17a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-6.586l.293.293a1 1 0 0 0 1.414-1.414l-7-7Z"/>
        </svg>
        <span>خانه</span>
    </a>

    @foreach($items as $item)
        <span class="shrink-0 text-[var(--color-text-subtle)]" aria-hidden="true">/</span>

        @if(!empty($item['url']))
            <a
                href="{{ $item['url'] }}"
                class="shrink-0 text-[var(--color-text-muted)] transition-colors hover:text-[var(--color-text)]"
            >
                {{ $item['label'] }}
            </a>
        @else
            <span class="max-w-[16rem] truncate font-bold text-[var(--color-text)]" aria-current="page">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>