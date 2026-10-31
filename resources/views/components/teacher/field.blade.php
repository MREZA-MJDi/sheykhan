{{-- Reusable field wrapper. A div avoids invalid nested labels (e.g. file dropzones). --}}
@props(['label', 'help' => null, 'hint' => null])
<div {{ $attributes->class(['tp-field']) }}>
    <span>{{ $label }}@if ($help)<span class="tp-help" tabindex="0" role="note" data-tip="{{ $help }}">؟</span>@endif</span>
    {{ $slot }}
    @if ($hint)<small>{{ $hint }}</small>@endif
</div>