@props([
'items' => [],
])

<nav
    dir="rtl"
    class="min-w-0"
    aria-label="مسیر صفحه"
>
    <ol class="flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm">

        <li class="flex shrink-0 items-center">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-2 rounded-md text-[var(--color-text-muted)] transition-colors duration-200 hover:text-[var(--color-brand-600)]"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="m3 10 9-7 9 7"></path>
                    <path d="M5 9.5V21h14V9.5"></path>
                    <path d="M9 21v-6h6v6"></path>
                </svg>

                خانه
            </a>
        </li>

        @foreach($items as $index => $item)
            <li
                class="flex shrink-0 items-center gap-2"
                aria-current="{{ empty($item['url']) && $loop->last ? 'page' : 'false' }}"
            >
                <svg
                    class="h-4 w-4 text-[var(--color-neutral-300)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="m9 18 6-6-6-6"></path>
                </svg>

                @if(!empty($item['url']) && !($loop->last))
                    <a
                        href="{{ $item['url'] }}"
                        class="text-[var(--color-text-muted)] transition-colors duration-200 hover:text-[var(--color-brand-600)]"
                    >
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-semibold text-[var(--color-text-primary)]">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
