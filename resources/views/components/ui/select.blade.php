@props([
    'label' => null,
    'error' => null,
    'placeholder' => null,
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

    <select
        {{ $attributes->merge([
            'class' => 'block w-full min-h-11 rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none fz-transition focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]',
        ]) }}
    >
        @if($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if($error)
        <p class="mt-1.5 text-xs font-medium text-[var(--color-danger-600)]">
            {{ $error }}
        </p>
    @endif
</div>
