@props(['title' => null, 'href' => null, 'linkText' => 'همه'])
<section {{ $attributes->class(['tp-card']) }}>
    @if ($title)
        <h3 class="tp-card__title">{{ $title }}@if ($href)<a href="{{ $href }}">{{ $linkText }}</a>@endif</h3>
    @endif
    {{ $slot }}
</section>