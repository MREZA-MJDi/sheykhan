@props(['color' => 'blue', 'icon' => 'i-chart', 'label', 'value', 'hint' => null, 'href' => null])
@if ($href)
  <a href="{{ $href }}" {{ $attributes->class(['st', $color]) }}>
@else
  <div {{ $attributes->class(['st', $color]) }}>
@endif
  <div class="ic"><svg class="i" aria-hidden="true"><use href="#{{ $icon }}"/></svg></div>
  <small>{{ $label }}</small>
  <b>{{ $value }}</b>
  @if ($hint)<span>{{ $hint }}</span>@endif
@if ($href)</a>@else</div>@endif
