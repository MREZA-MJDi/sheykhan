@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'rows' => 4,
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

    <textarea
        rows="{{ $rows }}"
        {{ $attributes->class([
            'ui-control min-h-28 resize-y px-4 py-3 text-sm leading-7 outline-none placeholder:text-[var(--color-text-subtle)]',
            'border-[var(--color-danger-400)] focus:border-[var(--color-danger-400)]' => $error,
        ]) }}
        @if($error) aria-invalid="true" @endif
    >{{ $slot }}</textarea>

    @if($error)
        <p class="mt-1.5 text-xs font-semibold text-[var(--color-danger-600)]">{{ $error }}</p>
    @elseif($hint)
        <p class="mt-1.5 text-xs text-[var(--color-text-muted)]">{{ $hint }}</p>
    @endif
</div>