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

        <h3 class="text-base font-bold">
            <a href="{{ $href }}" class="hover:text-[var(--color-primary-600)]">
                {{ $title }}
            </a>
        </h3>

        <span class="text-sm font-bold text-[var(--color-primary-600)]">
            {{ number_format($value) }}٪
        </span>

    </div>

    <div class="mt-4 h-2 overflow-hidden rounded-full bg-[var(--color-slate-100)]">
        <div
            class="h-full rounded-full bg-[var(--color-primary-600)] transition-all duration-500"
            style="width: {{ $value }}%"
        ></div>
    </div>

    @if($completed !== null || $total !== null)

        <div class="mt-3 flex justify-between text-xs text-[var(--color-text-muted)]">

            @if($completed !== null)
                <span>{{ $completed }} تکمیل شده</span>
            @endif

            @if($total !== null)
                <span>{{ $total }} مورد</span>
            @endif

        </div>

    @endif

</article>