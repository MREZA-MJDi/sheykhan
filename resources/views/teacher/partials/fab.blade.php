@if (!empty($tpQuick))
<div class="tp-fab">
    <div class="tp-fab__menu" id="tp-fab-menu">
        @foreach ($tpQuick as [$icon, $label, $url, $color])
            <a href="{{ $url }}"><span style="color:{{ $color }}">{{ $icon }}</span>{{ $label }}</a>
        @endforeach
    </div>
    <button class="tp-fab__btn" type="button" aria-label="اقدام سریع" aria-expanded="false" data-tp-fab>＋</button>
</div>
@endif