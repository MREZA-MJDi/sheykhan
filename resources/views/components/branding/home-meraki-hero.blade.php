@php
    // Public, active academy banners are the hero carousel source.
    // Keep a single lightweight fallback slide until a banner is configured.
    $heroSlides = collect($homeBanners ?? [])
        ->filter(fn ($banner) => filled($banner['image'] ?? null))
        ->values();

    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([[
            'image' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1920&q=85',
            'academy' => 'شیخان؛ آموزش، رشد، آینده',
            'title' => null,
            'description' => null,
            'ctaLabel' => null,
            'ctaUrl' => null,
            'cropX' => 50,
            'cropY' => 46,
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
    <div class="sheykhan-home-hero__slides">
        @foreach($heroSlides as $slide)
            <article
                class="sheykhan-home-hero__slide{{ $loop->first ? ' is-active' : '' }}"
                data-home-hero-slide
                aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                aria-label="اسلاید {{ $loop->iteration }} از {{ $loop->count }}"
            >
                <div class="sheykhan-home-hero__media" aria-hidden="true">
                    <img
                        src="{{ $slide['image'] }}"
                        alt=""
                        decoding="async"
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                        fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
                        style="object-position: {{ (int) ($slide['cropX'] ?? 50) }}% {{ (int) ($slide['cropY'] ?? 50) }}%;"
                    >
                </div>

                <div class="sheykhan-home-hero__overlay" aria-hidden="true"></div>

                <div class="sheykhan-home-hero__content">
                    <p class="sheykhan-home-hero__eyebrow">
                        <span aria-hidden="true"></span>
                        {{ $slide['academy'] ?: 'شیخان؛ آموزش، رشد، آینده' }}
                    </p>

                    @if($loop->first)
                        <h1 id="sheykhan-home-hero-title" class="sheykhan-home-hero__title" @if(filled($slide['title'] ?? null)) data-banner-title @endif>
                            @if(filled($slide['title'] ?? null))
                                {{ $slide['title'] }}
                            @else
                                آینده‌ات را با <span>یادگیری</span> بساز
                            @endif
                        </h1>
                    @else
                        <h2 class="sheykhan-home-hero__title" @if(filled($slide['title'] ?? null)) data-banner-title @endif>
                            @if(filled($slide['title'] ?? null))
                                {{ $slide['title'] }}
                            @else
                                آینده‌ات را با <span>یادگیری</span> بساز
                            @endif
                        </h2>
                    @endif

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
