@php
    // Editorial hero inspired by the supplied Meraki UI component.
    // Keep the shared navigation in layouts.app; this component owns only the hero.
    $heroImage = 'https://images.unsplash.com/photo-1556761175-b413da4baf72?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1920&q=85';
@endphp

<section class="sheykhan-home-hero" aria-labelledby="sheykhan-home-hero-title" data-home-hero>
    <div class="sheykhan-home-hero__media" aria-hidden="true">
        <img
            src="{{ $heroImage }}"
            alt=""
            loading="eager"
            decoding="async"
            fetchpriority="high"
        >
    </div>

    <div class="sheykhan-home-hero__overlay" aria-hidden="true"></div>
    <span class="sheykhan-home-hero__corner sheykhan-home-hero__corner--top" aria-hidden="true"></span>
    <span class="sheykhan-home-hero__corner sheykhan-home-hero__corner--bottom" aria-hidden="true"></span>

    <div class="sheykhan-home-hero__content">
        <p class="sheykhan-home-hero__eyebrow">
            <span aria-hidden="true"></span>
            شیخان؛ آموزش، رشد، آینده
        </p>

        <h1 id="sheykhan-home-hero-title" class="sheykhan-home-hero__title">
            آینده‌ات را با <span>یادگیری</span> بساز
        </h1>

        <p class="sheykhan-home-hero__description">
            از دوره و کلاس تا تمرین و آزمون؛ مسیر یادگیری‌ات را یکپارچه و روشن دنبال کن.
        </p>

        <div class="sheykhan-home-hero__actions">
            <a href="{{ route('courses.index') }}" class="sheykhan-home-hero__cta">
                مشاهده دوره‌ها
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M19 12H5m6 6-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <a
                href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                class="sheykhan-home-hero__secondary"
            >
                ورود به فضای یادگیری
            </a>
        </div>
    </div>
</section>
