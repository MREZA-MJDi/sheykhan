@props([
    'active' => null,
])

<div
    x-data="{ active: '{{ $active }}' }"
    {{ $attributes }}
>
    {{ $slot }}
</div>
