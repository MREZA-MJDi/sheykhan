@props([
'label' => null,
'hint' => null,
'error' => null,
'placeholder' => 'انتخاب کنید',
'required' => false,
])

@php
    $selectId = $attributes->get('id')
        ?? $attributes->get('name')
        ?? 'select-' . uniqid();

    $selectClasses = collect([
        'ui-select appearance-none',
        'pl-10',
        $error
            ? 'border-[var(--color-danger-500)] focus:border-[var(--color-danger-500)]'
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
            for="{{ $selectId }}"
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

    <div class="relative">
        <select
            id="{{ $selectId }}"
            @required($required)
            {{ $attributes->except('class')->merge([
                'class' => $selectClasses,
            ]) }}
            @if($error)
            aria-invalid="true"
            aria-describedby="{{ $selectId }}-error"
            @elseif($hint)
            aria-describedby="{{ $selectId }}-hint"
            @endif
        >
            @if($placeholder)
                <option
                    value=""
                    disabled
                    @selected(old($attributes->get('name')) === null)
                    >
                    {{ $placeholder }}
                </option>
            @endif

            {{ $slot }}
        </select>

        {{-- Chevron --}}
        <span
            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[var(--color-text-muted)]"
            aria-hidden="true"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <path d="m6 9 6 6 6-6" />
            </svg>
        </span>
    </div>

    @if($error)
        <p
            id="{{ $selectId }}-error"
            class="text-xs font-medium leading-5 text-[var(--color-danger-600)]"
        >
            {{ $error }}
        </p>
    @elseif($hint)
        <p
            id="{{ $selectId }}-hint"
            class="text-xs leading-5 text-[var(--color-text-muted)]"
        >
            {{ $hint }}
        </p>
    @endif
</div>
