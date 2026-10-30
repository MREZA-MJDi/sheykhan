@props([
    'type' => 'text',
    'label' => null,
    'error' => null,
    'hint' => null,
])

@php
    $inputId = $attributes->get('id') ?: 'ui-input-' . (string) \Illuminate\Support\Str::uuid();
    $messageId = $error ? $inputId . '-error' : ($hint ? $inputId . '-hint' : null);
    $describedBy = collect([$attributes->get('aria-describedby'), $messageId])
        ->filter()
        ->implode(' ');
@endphp

<div class="w-full">
    @if($label)
        <label
            for="{{ $inputId }}"
            class="mb-2 block text-sm font-bold text-[var(--color-slate-700)]"
        >
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $inputId }}"
        type="{{ $type }}"
        {{ $attributes->except(['id', 'aria-describedby', 'aria-invalid'])->class([
            'ui-control px-4 text-sm outline-none placeholder:text-[var(--color-text-subtle)]',
            'border-[var(--color-danger-400)] focus:border-[var(--color-danger-400)]' => $error,
        ]) }}
        @if($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if($error) aria-invalid="true" @endif
    >

    @if($error)
        <p id="{{ $messageId }}" class="mt-1.5 text-xs font-semibold text-[var(--color-danger-600)]">
            {{ $error }}
        </p>
    @elseif($hint)
        <p id="{{ $messageId }}" class="mt-1.5 text-xs text-[var(--color-text-muted)]">
            {{ $hint }}
        </p>
    @endif
</div>