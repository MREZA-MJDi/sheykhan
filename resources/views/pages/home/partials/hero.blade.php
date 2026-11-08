@php
    $heroSlides = collect($homeHeroSlides ?? [])
        ->filter(fn ($slide) => filled($slide['image'] ?? null))
        ->values();
    $hasHeroSlides = $heroSlides->isNotEmpty();
@endphp

<section class="sheykhan-home-hero" data-home-hero aria-label="اسلایدر اصلی شیخان" aria-labelledby="shx-public-hero-title">
    @if($hasHeroSlides)
        <div class="sheykhan-home-hero__slides">
            @foreach($heroSlides as $slide)
                <article class="sheykhan-home-hero__slide {{ $loop->first ? 'is-active' : '' }}"
                    data-home-hero-slide aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                    <div class="sheykhan-home-hero__media">
                        <picture>
                            @if(filled($slide['mobileImage'] ?? null))
                                <source data-home-hero-mobile-source srcset="{{ $slide['mobileImage'] }}" media="(max-width: 640px)">
                            @endif
                            <img
                                data-home-hero-image
                                data-desktop-src="{{ $slide['image'] }}"
                                src="{{ $slide['image'] }}"
                                alt="{{ $slide['title'] ?: 'تصویر معرفی شیخان' }}"
                                style="--hero-crop-x: {{ (int) ($slide['cropX'] ?? 50) }}%; --hero-crop-y: {{ (int) ($slide['cropY'] ?? 46) }}%; --hero-mobile-crop-x: {{ (int) ($slide['mobileCropX'] ?? 54) }}%; --hero-mobile-crop-y: {{ (int) ($slide['mobileCropY'] ?? 50) }}%;"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                decoding="async"
                                @if($loop->first) fetchpriority="high" @endif
                            >
                        </picture>
                    </div>
                    <div class="sheykhan-home-hero__overlay"></div>
                    <div class="sheykhan-home-hero__content">
                        <div class="sheykhan-home-hero__copy">
                            <p class="sheykhan-home-hero__eyebrow"><span></span>آموزش، تمرین و پیشرفت</p>
                            @if(filled($slide['title'] ?? null))
                                <h1 id="{{ $loop->first ? 'shx-public-hero-title' : '' }}" class="sheykhan-home-hero__title" data-hero-slide-title>{{ $slide['title'] }}</h1>
                            @else
                                <h1 id="{{ $loop->first ? 'shx-public-hero-title' : '' }}" class="sheykhan-home-hero__title" data-hero-slide-title>مغزت را <span>محک</span> بزن، مسیرت را هوشمندانه انتخاب کن.</h1>
                            @endif
                            <p class="sheykhan-home-hero__description">{{ $slide['description'] ?: 'کلاس زنده، آزمون و کارنامه‌ای که مسیر یادگیری را روشن‌تر می‌کند. شروعش فقط یک سؤال است.' }}</p>
                            <div class="sheykhan-home-hero__actions">
                                <a class="sheykhan-home-hero__cta" href="{{ $slide['ctaUrl'] ?: route('courses.index') }}">
                                    {{ $slide['ctaLabel'] ?: 'مشاهده دوره‌ها' }} <span aria-hidden="true">←</span>
                                </a>
                                <a class="sheykhan-home-hero__secondary" href="#hero-challenge">شروع چالش هوش</a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <article class="sheykhan-home-hero__slide is-active" data-home-hero-slide aria-hidden="false">
            <div class="sheykhan-home-hero__overlay"></div>
            <div class="sheykhan-home-hero__content">
                <div class="sheykhan-home-hero__copy">
                    <p class="sheykhan-home-hero__eyebrow"><span></span>آموزش، تمرین و پیشرفت</p>
                    <h1 id="shx-public-hero-title" class="sheykhan-home-hero__title" data-hero-slide-title>مغزت را <span>محک</span> بزن، مسیرت را هوشمندانه انتخاب کن.</h1>
                    <p class="sheykhan-home-hero__description">کلاس زنده، آزمون و کارنامه‌ای که مسیر یادگیری را روشن‌تر می‌کند. شروعش فقط یک سؤال است.</p>
                    <div class="sheykhan-home-hero__actions">
                        <a class="sheykhan-home-hero__cta" href="{{ route('courses.index') }}">مشاهده دوره‌ها ←</a>
                        <a class="sheykhan-home-hero__secondary" href="#hero-challenge">شروع چالش هوش</a>
                    </div>
                </div>
            </div>
        </article>
    @endif

    <div class="sheykhan-home-hero__challenge">
        @if(!empty($heroChallenge))
            <x-public.hero-challenge :challenge="$heroChallenge" />
        @else
            <div class="shx-public-puzzle shx-public-puzzle--empty" id="hero-challenge">
                <span class="shx-public-puzzle__eyebrow">چالش هوش شیخان</span>
                <h2>آماده‌ای مهارت حل مسئله‌ات را محک بزنی؟</h2>
                <p>چالش تازه‌ای در حال آماده‌سازی است. فعلاً دوره‌های منتشرشده را ببین.</p>
                <a class="shx-public-button shx-public-button--light" href="{{ route('courses.index') }}">مشاهده دوره‌ها</a>
            </div>
        @endif
    </div>

    @if($heroSlides->count() > 1)
        <div class="sheykhan-home-hero__controls" aria-label="کنترل اسلایدر">
            <button class="sheykhan-home-hero__arrow" type="button" data-home-hero-prev aria-label="اسلاید قبلی">‹</button>
            <div class="sheykhan-home-hero__dots">
                @foreach($heroSlides as $slide)
                    <button class="sheykhan-home-hero__dot {{ $loop->first ? 'is-active' : '' }}" type="button"
                        data-home-hero-dot data-index="{{ $loop->index }}" aria-label="نمایش اسلاید {{ \App\Support\PersianUi::digits($loop->iteration) }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <span class="sheykhan-home-hero__count" data-home-hero-count>۱ / {{ \App\Support\PersianUi::digits($heroSlides->count()) }}</span>
            <button class="sheykhan-home-hero__arrow" type="button" data-home-hero-next aria-label="اسلاید بعدی">›</button>
        </div>
    @endif
</section>
