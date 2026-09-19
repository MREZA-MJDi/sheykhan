@props([
    'value',
    'label',
    'caption' => null,
    'icon' => null,
])

<div class="rounded-2xl border border-[var(--color-border)] bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl">
                {{ $value }}
            </div>
            <div class="mt-1 text-sm font-semibold text-[var(--color-text-muted)]">
                {{ $label }}
            </div>
            @if($caption)
                <p class="mt-2 text-xs leading-6 text-[var(--color-text-muted)]">{{ $caption }}</p>
            @endif
        </div>

        @if($icon)
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
