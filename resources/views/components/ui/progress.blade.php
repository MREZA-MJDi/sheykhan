@props([
    'value' => 0,
    'label' => null,
])

@php
    $value = max(0, min(100, (float) $value));
@endphp

<div class="w-full">
    @if($label)
        <div class="mb-2 flex items-center justify-between gap-4">
            <span class="text-sm font-bold text-[var(--color-slate-700)]">{{ $label }}</span>
            <span class="text-xs font-black text-[var(--color-text-muted)]">{{ number_format($value, 0) }}٪</span>
        </div>
    @endif

    <div
        class="h-2 overflow-hidden rounded-full bg-[var(--color-slate-100)]"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="{{ $value }}"
        @if($label) aria-label="{{ $label }}" @endif
    >
        <div
            class="h-full rounded-full bg-[var(--color-primary-600)] transition-[width] duration-500 ease-out"
            style="width: {{ $value }}%"
        ></div>
    </div>
</div>