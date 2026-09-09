@props([
'label' => null,
'hint' => null,
'error' => null,
'required' => false,
])

@php
    $inputId = $attributes->get('id') ?? $attributes->get('name') ?? 'input-' . uniqid();

    $inputClasses = collect([
        'ui-input',
        $error
            ? 'border-[var(--color-danger-500)] focus:border-[var(--color-danger-500)] focus:ring-[color-mix(in_srgb,var(--color-danger-200)_70%,transparent)]'
            : null,
        $attributes->get('class'),
    ])->filter()->implode(' ');
@endphp

<div
    class="ui-field"
    dir="rtl"
>
    @if($label)
        <label
            for="{{ $inputId }}"
            class="ui-label"
        >
            {{ $label }}

            @if($required)
                <span
                    class="mr-1 text-[var(--color-danger-600)]"
                    aria-hidden="true"
                >
                    *
                </span>
            @endif
        </label>
    @endif

    <input
        id="{{ $inputId }}"
        @required($required)
        {{ $attributes->except('class')->merge([
            'class' => $inputClasses,
        ]) }}
        @if($error)
        aria-invalid="true"
        aria-describedby="{{ $inputId }}-error"
        @elseif($hint)
        aria-describedby="{{ $inputId }}-hint"
        @endif
    >

    @if($error)
        <p
            id="{{ $inputId }}-error"
            class="text-xs font-medium leading-5 text-[var(--color-danger-600)]"
        >
            {{ $error }}
        </p>
    @elseif($hint)
        <p
            id="{{ $inputId }}-hint"
            class="text-xs leading-5 text-[var(--color-text-muted)]"
        >
            {{ $hint }}
        </p>
    @endif
</div>
