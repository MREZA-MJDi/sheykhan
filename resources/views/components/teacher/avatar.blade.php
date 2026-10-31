@props(['name' => '', 'size' => 36, 'photo' => null])
@php
    $hues = ['#4a5fe0', '#22b573', '#ff9f43', '#8b5cf6', '#f0566b', '#14b8c4'];
    $len = mb_strlen($name);
    $initials = collect(preg_split('/\s+/u', trim($name)))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode("\u{200C}");
    $style = "--s:{$size}px;--c1:{$hues[$len % 6]};--c2:{$hues[($len + 2) % 6]};" . ($photo ? "background-image:url('{$photo}');" : '');
@endphp
<span {{ $attributes->class(['tp-av']) }} style="{{ $style }}" @if($photo) role="img" aria-label="{{ $name }}" @endif>{{ $photo ? '' : $initials }}</span>