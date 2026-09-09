@props([
    'align' => 'right',
])

<div
    x-data="{ open: false }"
    class="relative"
>
    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        {{ $trigger->attributes->merge([
            'class' => 'inline-flex items-center justify-center',
        ]) }}
    >
        {{ $trigger }}
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute z-[var(--z-dropdown)] mt-2 min-w-48 overflow-hidden rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-white p-1 shadow-[var(--shadow-lg)] {{ $align === 'left' ? 'left-0' : 'right-0' }}"
    >
        {{ $content }}
    </div>
</div>
