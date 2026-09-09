@props([
    'type' => 'text',
    'label' => null,
    'error' => null,
    'hint' => null,
])

<div class="w-full">
    @if($label)
        <label
            @if($attributes->has('id')) for="{{ $attributes->get('id') }}" @endif
            class="mb-2 block text-sm font-medium text-[var(--color-slate-700)]"
        >
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'block w-full min-h-11 rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none fz-transition placeholder:text-[var(--color-text-subtle)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)] ' . ($error ? 'border-[var(--color-danger-400)] focus:border-[var(--color-danger-400)] focus:ring-red-50' : ''),
        ]) }}
    >

    @if($error)
        <p class="mt-1.5 text-xs font-medium text-[var(--color-danger-600)]">
            {{ $error }}
        </p>
    @elseif($hint)
        <p class="mt-1.5 text-xs text-[var(--color-text-muted)]">
            {{ $hint }}
        </p>
    @endif
</div>
