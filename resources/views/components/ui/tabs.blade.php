@props([
    'active' => null,
])

<div
    x-data="{ active: @js($active) }"
    {{ $attributes->class(['min-w-0']) }}
>
    {{ $slot }}
</div>