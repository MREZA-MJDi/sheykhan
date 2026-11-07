@php
    $heroSlides = collect($homeHeroSlides ?? [])
        ->filter(fn ($slide) => filled($slide['image'] ?? null))
        ->values();
@endphp

<section class="shx-public-hero" aria-labelledby="shx-public-hero-title">
    <span class="shx-public-hero__shape shx-public-hero__shape--circle" aria-hidden="true"></span>
    <span class="shx-public-hero__shape shx-public-hero__shape--square" aria-hidden="true"></span>
    <span class="shx-public-hero__shape shx-public-hero__shape--triangle" aria-hidden="true"></span>
    <div class="wrap shx-public-hero__grid">
        <div class="shx-public-hero__copy">
            <span class="shx-public-eyebrow">آموزش، تمرین و پیشرفت</span>
            <h1 id="shx-public-hero-title">مغزت را <em>محک</em> بزن، مسیرت را هوشمندانه انتخاب کن.</h1>
            <p class="shx-public-hero__lead">کلاس زنده، آزمون و کارنامه‌ای که مسیر یادگیری را روشن‌تر می‌کند. شروعش فقط یک سؤال است.</p>
            <div class="shx-public-hero__actions">
                <a class="shx-public-button shx-public-button--blue" href="#hero-challenge">شروع چالش هوش <span aria-hidden="true">←</span></a>
                <a class="shx-public-button shx-public-button--outline" href="{{ route('courses.index') }}">دیدن دوره‌ها</a>
            </div>
            <div class="shx-public-hero__chips" aria-label="امکانات سامانه"><span>دوره‌های آموزشی</span><span>کلاس و تمرین</span><span>گزارش پیشرفت</span></div>
        </div>
        <div class="shx-public-hero__challenge">
            @if(!empty($heroChallenge))
                <x-public.hero-challenge :challenge="$heroChallenge" />
            @else
                <div class="shx-public-puzzle shx-public-puzzle--empty" id="hero-challenge">
                    <span class="shx-public-puzzle__eyebrow">چالش هوش شیخان</span>
                    <h2>آماده‌ای مهارت حل مسئله‌ات را محک بزنی؟</h2>
                    <p>چالش جدیدی در حال آماده‌سازی است. فعلاً دوره‌های منتشرشده را ببین.</p>
                    <a class="shx-public-button shx-public-button--light" href="{{ route('courses.index') }}">مشاهده دوره‌ها</a>
                </div>
            @endif
        </div>
    </div>
</section>

@if($heroSlides->isNotEmpty())
    <section class="shx-public-banners" aria-label="بنرهای ویژه شیخان">
        <div class="wrap"><div class="shx-public-banner-grid">
            @foreach($heroSlides as $slide)
                @if(filled($slide['ctaUrl'] ?? null))<a class="shx-public-banner" href="{{ $slide['ctaUrl'] }}">@else<article class="shx-public-banner">@endif
                    <picture>
                        @if(filled($slide['mobileImage'] ?? null))<source srcset="{{ $slide['mobileImage'] }}" media="(max-width: 640px)">@endif
                        <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] ?: 'بنر شیخان' }}" loading="lazy" decoding="async" style="object-position: {{ (int) ($slide['cropX'] ?? 50) }}% {{ (int) ($slide['cropY'] ?? 50) }}%;">
                    </picture>
                    <span class="shx-public-banner__overlay">
                        @if(filled($slide['academy'] ?? null))<small>{{ $slide['academy'] }}</small>@endif
                        @if(filled($slide['title'] ?? null))<strong>{{ $slide['title'] }}</strong>@endif
                        @if(filled($slide['description'] ?? null))<span>{{ $slide['description'] }}</span>@endif
                        @if(filled($slide['ctaLabel'] ?? null))<b>{{ $slide['ctaLabel'] }} ←</b>@endif
                    </span>
                @if(filled($slide['ctaUrl'] ?? null))</a>@else</article>@endif
            @endforeach
        </div></div>
    </section>
@endif
