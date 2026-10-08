@props([
    'title' => 'پیشرفت دوره',
    'value' => 0,
    'total' => null,
    'completed' => null,
    'href' => '#',
])

@php
    $value = max(0, min(100, (float) $value));
@endphp

<article class="fz-surface p-5">
    <div class="flex items-start justify-between gap-4">
        <h3 class="min-w-0 truncate text-base font-black text-[var(--color-text)]">
            <a href="{{ $href }}" class="hover:text-[var(--color-primary-600)]">{{ $title }}</a>
        </h3>

        <span class="shrink-0 text-sm font-black text-[var(--color-primary-600)]">
            {{ number_format($value, 0) }}٪
        </span>
    </div>

    <div class="mt-4 h-2 overflow-hidden rounded-full bg-[var(--color-slate-100)]" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $value }}" aria-label="پیشرفت {{ $title }}">
        <div class="h-full rounded-full bg-[var(--color-primary-600)] transition-all duration-500" style="width: {{ $value }}%"></div>
    </div>

    @if($completed !== null || $total !== null)
        <div class="mt-3 flex justify-between gap-3 text-xs text-[var(--color-text-muted)]">
            @if($completed !== null)<span>{{ $completed }} تکمیل شده</span>@endif
            @if($total !== null)<span>{{ $total }} مورد</span>@endif
        </div>
    @endif
</article>