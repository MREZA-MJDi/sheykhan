@props([
'name' => null,
'value' => null,
'label' => null,
'description' => null,
'checked' => false,
'disabled' => false,
'required' => false,
])

@php
    $id = $attributes->get('id')
        ?? ($name ? $name . '-' . uniqid() : 'checkbox-' . uniqid());
@endphp

<div
    class="flex items-start gap-3"
    dir="rtl"
>
    <div class="flex h-6 items-center">
        <input
            id="{{ $id }}"
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @disabled($disabled)
            @required($required)
            {{ $attributes->except(['id', 'class'])->merge([
                'class' => '
                    h-5 w-5
                    shrink-0
                    cursor-pointer
                    appearance-none
                    rounded-md
                    border
                    border-[var(--color-border-strong)]
                    bg-[var(--color-surface)]
                    transition-all
                    duration-150
                    checked:border-[var(--color-brand-600)]
                    checked:bg-[var(--color-brand-600)]
                    focus-visible:outline-none
                    focus-visible:ring-4
                    focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                    disabled:cursor-not-allowed
                    disabled:opacity-50
                    indeterminate:border-[var(--color-brand-600)]
                    indeterminate:bg-[var(--color-brand-600)]
                '
            ]) }}
        >

        <style>
            input[type="checkbox"].farzin-checkbox {
                background-image: none;
            }

            input[type="checkbox"].farzin-checkbox:checked {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='m3.5 8 3 3 6-6' stroke='white' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
                background-position: center;
                background-repeat: no-repeat;
                background-size: 75%;
            }
        </style>
    </div>

    @if($label || $description || $slot->isNotEmpty())
        <div class="min-w-0 flex-1">
            @if($label)
                <label
                    for="{{ $id }}"
                    class="block cursor-pointer text-sm font-semibold leading-6 text-[var(--color-text-primary)] {{ $disabled ? 'cursor-not-allowed opacity-50' : '' }}"
                >
                    {{ $label }}
                </label>
            @endif

            @if($description)
                <p class="mt-0.5 text-xs leading-5 text-[var(--color-text-muted)]">
                    {{ $description }}
                </p>
            @endif

            @if($slot->isNotEmpty())
                <div class="{{ $label || $description ? 'mt-1' : '' }}">
                    {{ $slot }}
                </div>
            @endif
        </div>
    @endif
</div>
