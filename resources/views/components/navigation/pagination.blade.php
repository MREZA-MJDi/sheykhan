@if($paginator->hasPages())
    <nav
        class="flex items-center justify-center"
        aria-label="صفحه‌بندی"
    >
        <div class="inline-flex items-center gap-1.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-1.5 shadow-[var(--shadow-xs)]">

            {{-- Previous --}}
            @if($paginator->onFirstPage())
                <span
                    class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-[var(--color-text-disabled)]"
                    aria-disabled="true"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>

                    <span class="sr-only">صفحه قبل</span>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors duration-200 hover:bg-[var(--color-neutral-100)] hover:text-[var(--color-text-primary)]"
                    aria-label="صفحه قبل"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </a>
            @endif

            {{-- Current --}}
            <span
                class="flex h-9 min-w-9 items-center justify-center rounded-lg bg-[var(--color-brand-600)] px-2.5 text-sm font-bold text-white"
                aria-current="page"
            >
                {{ $paginator->currentPage() }}
            </span>

            {{-- Next --}}
            @if($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors duration-200 hover:bg-[var(--color-neutral-100)] hover:text-[var(--color-text-primary)]"
                    aria-label="صفحه بعد"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            @else
                <span
                    class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-[var(--color-text-disabled)]"
                    aria-disabled="true"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </span>
            @endif

        </div>
    </nav>
@endif
