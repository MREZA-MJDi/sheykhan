@props([
    'type' => 'info',
    'title' => null,
])

@php
    $styles = [
        'info' => 'border-cyan-200 bg-cyan-50 text-cyan-900',
        'success' => 'border-green-200 bg-green-50 text-green-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-red-200 bg-red-50 text-red-900',
    ];
@endphp

<div
    {{ $attributes->merge([
        'class' => 'rounded-[var(--radius-lg)] border px-4 py-3 ' . ($styles[$type] ?? $styles['info']),
    ]) }}
>
    @if($title)
        <div class="font-semibold">
            {{ $title }}
        </div>
    @endif

    <div class="{{ $title ? 'mt-1' : '' }} text-sm leading-6">
        {{ $slot }}
    </div>
</div>
