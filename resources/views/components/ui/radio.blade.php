@props([
    'label' => null,
    'description' => null,
])

<label class="flex cursor-pointer items-start gap-3 rounded-xl p-2 transition hover:bg-[var(--color-background-soft)]">
    <input
        type="radio"
        {{ $attributes->merge([
            'class' => 'mt-0.5 h-4 w-4 shrink-0 border-[var(--color-border-strong)] text-[var(--color-primary-600)] focus:ring-4 focus:ring-[var(--color-primary-100)]',
        ]) }}
    >

    @if($label || $description)
        <span class="min-w-0">
            @if($label)
                <span class="block text-sm font-bold text-[var(--color-slate-800)]">{{ $label }}</span>
            @endif

            @if($description)
                <span class="mt-0.5 block text-xs leading-6 text-[var(--color-text-muted)]">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>