@props(['href'=>'#','label'=>'','value'=>'0','hint'=>'','icon'=>'•','tone'=>'#4a5fe0'])
<a class="teacher-stat-card" href="{{ $href }}" style="--k:{{ $tone }}" aria-label="{{ $label }}: {{ $value }}. {{ $hint }}">
  <span class="ik xl" aria-hidden="true">{{ $icon }}</span>
  <span><b>{{ $value }}</b><span>{{ $label }}</span><small>{{ $hint }} ←</small></span>
</a>