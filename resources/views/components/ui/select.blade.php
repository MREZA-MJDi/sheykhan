@props([
    'label' => null,
    'error' => null,
    'placeholder' => null,
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

    <select
        {{ $attributes->class([
            'ui-control appearance-none px-4 text-sm outline-none',
            'border-[var(--color-danger-400)] focus:border-[var(--color-danger-400)]' => $error,
        ]) }}
        @if($error) aria-invalid="true" @endif
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if($error)
        <p class="mt-1.5 text-xs font-semibold text-[var(--color-danger-600)]">
            {{ $error }}
        </p>
    @endif
</div>