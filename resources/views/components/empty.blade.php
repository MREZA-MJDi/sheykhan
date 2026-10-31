@props(['icon' => 'i-file', 'text', 'href' => null, 'action' => null])
<div {{ $attributes->class(['empty']) }}>
  <svg class="i" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
  <p>{{ $text }}</p>
  @if ($href)<a class="btn s" href="{{ $href }}">{{ $action }}</a>@endif
</div>
