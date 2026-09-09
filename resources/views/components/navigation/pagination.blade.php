@if($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2" aria-label="صفحه‌بندی">

        @if($paginator->onFirstPage())
            <span class="rounded-lg border border-[var(--color-border)] px-3 py-2 text-sm text-[var(--color-text-subtle)]">
                قبلی
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm hover:bg-[var(--color-slate-50)]"
            >
                قبلی
            </a>
        @endif

        <span class="rounded-lg bg-[var(--color-primary-600)] px-3 py-2 text-sm font-semibold text-white">
            {{ $paginator->currentPage() }}
        </span>

        @if($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                class="rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-sm hover:bg-[var(--color-slate-50)]"
            >
                بعدی
            </a>
        @else
            <span class="rounded-lg border border-[var(--color-border)] px-3 py-2 text-sm text-[var(--color-text-subtle)]">
                بعدی
            </span>
        @endif

    </nav>
@endif
