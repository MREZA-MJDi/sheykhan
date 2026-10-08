@if($paginator->hasPages())
    @php
        $page = (int) $paginator->currentPage();
        $last = (int) $paginator->lastPage();
    @endphp

    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="صفحه‌بندی">
        @if($paginator->onFirstPage())
            <span class="inline-flex min-h-10 items-center rounded-xl border border-[var(--color-border)] bg-[var(--color-background-soft)] px-3.5 text-xs font-bold text-[var(--color-text-subtle)]" aria-disabled="true">
                قبلی
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                class="inline-flex min-h-10 items-center rounded-xl border border-[var(--color-border)] bg-white px-3.5 text-xs font-black text-[var(--color-text)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]"
                rel="prev"
            >
                قبلی
            </a>
        @endif

        @for($i = max(1, $page - 2); $i <= min($last, $page + 2); $i++)
            @if($i === $page)
                <span class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-3 text-xs font-black text-white" aria-current="page">
                    {{ App\Support\PersianUi::digits($i) }}
                </span>
            @else
                <a
                    href="{{ $paginator->url($i) }}"
                    class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-white px-3 text-xs font-bold text-[var(--color-text-muted)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]"
                >
                    {{ App\Support\PersianUi::digits($i) }}
                </a>
            @endif
        @endfor

        @if($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                class="inline-flex min-h-10 items-center rounded-xl border border-[var(--color-border)] bg-white px-3.5 text-xs font-black text-[var(--color-text)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]"
                rel="next"
            >
                بعدی
            </a>
        @else
            <span class="inline-flex min-h-10 items-center rounded-xl border border-[var(--color-border)] bg-[var(--color-background-soft)] px-3.5 text-xs font-bold text-[var(--color-text-subtle)]" aria-disabled="true">
                بعدی
            </span>
        @endif
    </nav>
@endif