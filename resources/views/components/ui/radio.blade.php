@props([
    'label' => null,
    'description' => null,
])

<label class="flex cursor-pointer items-start gap-3">
    <input
        type="radio"
        {{ $attributes->merge([
            'class' => 'mt-0.5 h-4 w-4 shrink-0 border-[var(--color-border-strong)] text-[var(--color-primary-600)] focus:ring-4 focus:ring-[var(--color-primary-100)]',
        ]) }}
    >

    @if($label || $description)
        <span class="min-w-0">
            @if($label)
                <span class="block text-sm font-medium text-[var(--color-slate-800)]">
                    {{ $label }}
                </span>
            @endif

            @if($description)
                <span class="mt-0.5 block text-xs text-[var(--color-text-muted)]">
                    {{ $description }}
                </span>
            @endif
        </span>
    @endif
</label>
