{{-- Label + optional help bubble + control (slot) + optional hint. --}}
@props(['label', 'help' => null, 'hint' => null])
<label {{ $attributes->class(['tp-field']) }}>
    <span>{{ $label }}@if ($help)<span class="tp-help" tabindex="0" role="note" data-tip="{{ $help }}">؟</span>@endif</span>
    {{ $slot }}
    @if ($hint)<small>{{ $hint }}</small>@endif
</label>