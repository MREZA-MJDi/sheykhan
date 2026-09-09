@props([
    'label' => 'در حال بارگذاری...',
])

<div
    {{ $attributes->merge([
        'class' => 'flex items-center justify-center gap-3 py-8 text-sm text-[var(--color-text-muted)]',
    ]) }}
>
    <span class="h-5 w-5 animate-spin rounded-full border-2 border-[var(--color-slate-200)] border-t-[var(--color-primary-600)]"></span>

    <span>{{ $label }}</span>
</div>
