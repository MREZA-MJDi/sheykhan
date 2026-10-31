@props(['href'=>'#','label'=>'','hint'=>'','icon'=>'•','tone'=>'#4a5fe0'])
<a class="teacher-quick-action" href="{{ $href }}" style="--k:{{ $tone }}">
  <span class="ik xl" aria-hidden="true">{{ $icon }}</span><b>{{ $label }}</b><small>{{ $hint }}</small>
</a>