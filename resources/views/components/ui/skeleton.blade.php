@props([
'variant' => 'text',
'width' => null,
'height' => null,
])

@php
    $variants = [
        'text' => 'h-4 w-full rounded-md',
        'title' => 'h-6 w-2/3 rounded-md',
        'avatar' => 'h-11 w-11 rounded-full',
        'circle' => 'rounded-full aspect-square',
        'button' => 'h-11 w-28 rounded-full',
        'card' => 'h-48 w-full rounded-xl',
        'image' => 'h-48 w-full rounded-xl',
        'block' => 'h-24 w-full rounded-xl',
    ];

    $classes = collect([
        'ui-skeleton',
        $variants[$variant] ?? $variants['text'],
        $attributes->get('class'),
    ])->filter()->implode(' ');

    $style = collect([
        $width ? "width: {$width}" : null,
        $height ? "height: {$height}" : null,
    ])->filter()->implode('; ');
@endphp

<span
    {{ $attributes->except('class')->merge([
        'class' => $classes,
        'aria-hidden' => 'true',
        'role' => 'presentation',
    ]) }}
    @if($style)
    style="{{ $style }}"
    @endif
></span>
