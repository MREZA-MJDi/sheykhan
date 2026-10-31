@props(['variant' => 'primary', 'size' => null, 'href' => null, 'type' => 'button'])
@php
    $cls = ['tp-btn', 'tp-btn--light' => $variant === 'light', 'tp-btn--ghost-danger' => $variant === 'danger-text', 'tp-btn--sm' => $size === 'sm'];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($cls) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($cls) }}>{{ $slot }}</button>
@endif