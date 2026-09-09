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
            <span class="text-sm font-medium text-[var(--color-slate-700)]">
                {{ $label }}
            </span>

            <span class="text-xs font-semibold text-[var(--color-text-muted)]">
                {{ $value }}٪
            </span>
        </div>
    @endif

    <div class="h-2 overflow-hidden rounded-full bg-[var(--color-slate-100)]">
        <div
            class="h-full rounded-full bg-[var(--color-primary-600)] fz-transition"
            style="width: {{ $value }}%"
        ></div>
    </div>
</div>
