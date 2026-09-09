@props([
'label' => null,
'hint' => null,
'error' => null,
'required' => false,
])

@php
    $textareaId = $attributes->get('id')
        ?? $attributes->get('name')
        ?? 'textarea-' . uniqid();

    $textareaClasses = collect([
        'ui-textarea',
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
            for="{{ $textareaId }}"
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

    <textarea
        id="{{ $textareaId }}"
        @required($required)
        {{ $attributes->except('class')->merge([
            'class' => $textareaClasses,
        ]) }}
        @if($error)
        aria-invalid="true"
        aria-describedby="{{ $textareaId }}-error"
        @elseif($hint)
        aria-describedby="{{ $textareaId }}-hint"
        @endif
    >{{ $slot }}</textarea>

    @if($error)
        <p
            id="{{ $textareaId }}-error"
            class="text-xs font-medium leading-5 text-[var(--color-danger-600)]"
        >
            {{ $error }}
        </p>
    @elseif($hint)
        <p
            id="{{ $textareaId }}-hint"
            class="text-xs leading-5 text-[var(--color-text-muted)]"
        >
            {{ $hint }}
        </p>
    @endif
</div>
