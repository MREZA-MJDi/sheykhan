@props([
    'text',
])

<div
    class="relative inline-flex group"
>
    {{ $slot }}

    <span
        class="pointer-events-none absolute bottom-full right-1/2 z-[var(--z-dropdown)] mb-2 hidden -translate-y-1/2 translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2.5 py-1.5 text-xs text-white shadow-lg group-hover:block"
    >
        {{ $text }}
    </span>
</div>
