@props(['at'])
@php
  if (!$at) {
      $tone = 'b';
      $text = 'بدون مهلت';
  } else {
      $hours = now()->diffInHours($at, false);
      [$tone, $text] = match (true) {
          $hours < 0 => ['r', 'مهلت گذشته'],
          $hours < 24 => ['r', 'کمتر از ۲۴ ساعت'],
          $hours < 72 => ['a', ceil($hours / 24).' روز دیگر'],
          default => ['b', ceil($hours / 24).' روز دیگر'],
      };
  }
@endphp
<span {{ $attributes->class(['tag', $tone]) }}>{{ $text }}</span>
