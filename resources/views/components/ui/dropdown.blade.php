@props([
    'align' => 'right',
    'width' => 'min-w-56',
])

<div x-data="{ open: false }" class="relative inline-flex">
    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        :aria-expanded="open.toString()"
        aria-haspopup="menu"
        {{ $trigger->attributes->class(['inline-flex items-center justify-center']) }}
    >
        {{ $trigger }}
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        @keydown.escape.window="open = false"
        class="absolute top-[calc(100%+0.5rem)] z-[var(--z-dropdown)] {{ $width }} overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-white p-1.5 shadow-[var(--shadow-lg)] {{ $align === 'left' ? 'left-0' : 'right-0' }}"
        role="menu"
    >
        {{ $content }}
    </div>
</div>