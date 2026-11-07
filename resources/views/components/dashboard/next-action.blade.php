@props(['action' => null])
@if ($action)
    <section {{ $attributes->merge(['class' => 'next-action']) }} aria-label="کار بعدی">
        <div class="next-action__copy">
            <span class="next-action__eyebrow">کار بعدی</span>
            <p class="next-action__text">{{ data_get($action, 'text') }}</p>
        </div>
        <a class="next-action__btn" href="{{ data_get($action, 'url') }}">{{ data_get($action, 'label') }}</a>
    </section>
@endif
