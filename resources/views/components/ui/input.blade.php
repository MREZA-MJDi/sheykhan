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
            class="mb-2 block text-sm font-bold text-[var(--color-slate-700)]"
        >
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        {{ $attributes->class([
            'ui-control px-4 text-sm outline-none placeholder:text-[var(--color-text-subtle)]',
            'border-[var(--color-danger-400)] focus:border-[var(--color-danger-400)]' => $error,
        ]) }}
        @if($error) aria-invalid="true" @endif
    >

    @if($error)
        <p class="mt-1.5 text-xs font-semibold text-[var(--color-danger-600)]">
            {{ $error }}
        </p>
    @elseif($hint)
        <p class="mt-1.5 text-xs text-[var(--color-text-muted)]">
            {{ $hint }}
        </p>
    @endif
</div>