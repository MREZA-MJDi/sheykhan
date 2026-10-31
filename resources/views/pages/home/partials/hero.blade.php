@php
    // Hero slides come only from HomeHeroSlide; academy banners are rendered separately below.
    $heroSlides = collect($homeHeroSlides ?? [])
        ->filter(fn ($slide) => filled($slide['image'] ?? null))
        ->values();

    $fallbackImage = asset('images/home/hero-fallback.svg');

    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([[
            'image' => $fallbackImage,
            'mobileImage' => null,
            'academy' => 'شیخان؛ آموزش، رشد، آینده',
            'title' => null,
            'description' => null,
            'ctaLabel' => null,
            'ctaUrl' => null,
            'cropX' => 50,
            'cropY' => 46,
            'mobileCropX' => 54,
            'mobileCropY' => 50,
        ]]);
    }
@endphp

<section
    class="sheykhan-home-hero"
    role="region"
    aria-label="معرفی شیخان"
    aria-labelledby="sheykhan-home-hero-title"
    aria-roledescription="carousel"
    data-home-hero
>
    <h1 id="sheykhan-home-hero-title" class="sr-only">آینده‌ات را با یادگیری بساز | شیخان</h1>

    <div class="sheykhan-home-hero__slides">
        @foreach($heroSlides as $slide)
            <article
                class="sheykhan-home-hero__slide{{ $loop->first ? ' is-active' : '' }}"
                data-home-hero-slide
                aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                aria-label="اسلاید {{ $loop->iteration }} از {{ $loop->count }}"
                style="--hero-crop-x: {{ (int) ($slide['cropX'] ?? 50) }}%; --hero-crop-y: {{ (int) ($slide['cropY'] ?? 46) }}%; --hero-mobile-crop-x: {{ (int) ($slide['mobileCropX'] ?? 54) }}%; --hero-mobile-crop-y: {{ (int) ($slide['mobileCropY'] ?? 50) }}%;"
            >
                <div class="sheykhan-home-hero__media" aria-hidden="true">
                    <picture>
                        @if(filled($slide['mobileImage'] ?? null))
                            <source
                                srcset="{{ $slide['mobileImage'] }}"
                                media="(max-width: 640px)"
                                data-home-hero-mobile-source
                            >
                        @endif
                        <img
                            src="{{ $slide['image'] }}"
                            data-home-hero-image
                            data-desktop-src="{{ $slide['image'] }}"
                            data-fallback-src="{{ $fallbackImage }}"
                            alt=""
                            decoding="async"
                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
                        >
                    </picture>
                </div>

                <div class="sheykhan-home-hero__overlay" aria-hidden="true"></div>

                <div class="sheykhan-home-hero__content">
                    <p class="sheykhan-home-hero__eyebrow">
                        <span aria-hidden="true"></span>
                        {{ $slide['academy'] ?: 'شیخان؛ آموزش، رشد، آینده' }}
                    </p>

                    <h2 class="sheykhan-home-hero__title" @if(filled($slide['title'] ?? null)) data-hero-slide-title @endif>
                        @if(filled($slide['title'] ?? null))
                            {{ $slide['title'] }}
                        @else
                            آینده‌ات را با <span>یادگیری</span> بساز
                        @endif
                    </h2>

                    <p class="sheykhan-home-hero__description">
                        {{ $slide['description'] ?: 'از دوره و کلاس تا تمرین و آزمون؛ مسیر یادگیری‌ات را یکپارچه و روشن دنبال کن.' }}
                    </p>

                    <div class="sheykhan-home-hero__actions">
                        @if(filled($slide['ctaUrl'] ?? null) && filled($slide['ctaLabel'] ?? null))
                            <a href="{{ $slide['ctaUrl'] }}" class="sheykhan-home-hero__cta">
                                {{ $slide['ctaLabel'] }}
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M19 12H5m6 6-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('courses.index') }}" class="sheykhan-home-hero__cta">
                                مشاهده دوره‌ها
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M19 12H5m6 6-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @endif

                        <a
                            href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                            class="sheykhan-home-hero__secondary"
                        >
                            ورود به فضای یادگیری
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <span class="sheykhan-home-hero__corner sheykhan-home-hero__corner--top" aria-hidden="true"></span>
    <span class="sheykhan-home-hero__corner sheykhan-home-hero__corner--bottom" aria-hidden="true"></span>

    @if($heroSlides->count() > 1)
        <div class="sheykhan-home-hero__controls" aria-label="کنترل اسلایدر">
            <button type="button" data-home-hero-prev class="sheykhan-home-hero__arrow" aria-label="اسلاید قبلی">
                <span aria-hidden="true">‹</span>
            </button>

            <div class="sheykhan-home-hero__dots">
                @foreach($heroSlides as $slide)
                    <button
                        type="button"
                        data-home-hero-dot
                        data-index="{{ $loop->index }}"
                        class="sheykhan-home-hero__dot{{ $loop->first ? ' is-active' : '' }}"
                        aria-label="نمایش اسلاید {{ $loop->iteration }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                    ></button>
                @endforeach
            </div>

            <span class="sheykhan-home-hero__count" data-home-hero-count aria-hidden="true">۱ / {{ \App\Support\PersianUi::digits($heroSlides->count()) }}</span>

            <button type="button" data-home-hero-next class="sheykhan-home-hero__arrow" aria-label="اسلاید بعدی">
                <span aria-hidden="true">›</span>
            </button>
        </div>
    @endif
</section>
