@props([
    'text',
])

<span
    x-data="{ show: false }"
    class="relative inline-flex"
    @mouseenter="show = true"
    @mouseleave="show = false"
    @focusin="show = true"
    @focusout="show = false"
>
    <span
        {{ $attributes->class(['inline-flex']) }}
        aria-describedby="ui-tooltip-{{ IlluminateSupportStr::slug($text) }}"
    >
        {{ $slot }}
    </span>

    <span
        id="ui-tooltip-{{ IlluminateSupportStr::slug($text) }}"
        x-cloak
        x-show="show"
        x-transition.opacity
        role="tooltip"
        class="pointer-events-none absolute bottom-full right-1/2 z-[var(--z-dropdown)] mb-2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-semibold text-white shadow-lg"
    >
        {{ $text }}
    </span>
</span>