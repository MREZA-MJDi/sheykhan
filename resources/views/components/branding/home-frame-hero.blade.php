@props(['courseCards' => []])

@php
    $slides = collect($courseCards ?? [])
        ->filter(fn ($course) => is_array($course) && filled($course['title'] ?? null))
        ->take(4)
        ->values();
    $slideCount = $slides->count();
@endphp

<section
    class="home-frame-hero"
    data-frame-hero
    role="region"
    aria-roledescription="carousel"
    aria-label="دوره‌های منتخب شیخان"
>
    <div class="home-frame-hero__slides">
        @forelse($slides as $slide)
            <article
                class="home-frame-hero__slide {{ $loop->first ? 'is-active' : '' }}"
                data-frame-slide
                role="group"
                aria-roledescription="اسلاید"
                aria-label="{{ \App\Support\PersianUi::digits($loop->iteration) }} از {{ \App\Support\PersianUi::digits($slideCount) }}"
                aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                @unless($loop->first) inert @endunless
            >
                <div class="home-frame-hero__media" aria-hidden="true">
                    @if(filled($slide['image'] ?? null))
                        <img
                            src="{{ $slide['image'] }}"
                            alt=""
                            decoding="async"
                            @if($loop->first)
                                loading="eager"
                                fetchpriority="high"
                            @else
                                loading="lazy"
                            @endif
                            data-frame-image
                        >
                    @endif
                </div>

                <div class="home-frame-hero__scrim" aria-hidden="true"></div>
                <div class="home-frame-hero__content">
                    <div class="home-frame-hero__eyebrow">
                        <span class="home-frame-hero__signal" aria-hidden="true"></span>
                        <span>{{ $slide['category'] ?: 'مسیر یادگیری شیخان' }}</span>
                        <span class="home-frame-hero__eyebrow-rule" aria-hidden="true"></span>
                        <span>۰{{ \App\Support\PersianUi::digits($loop->iteration) }}</span>
                    </div>

                    @if($loop->first)
                        <h1 class="home-frame-hero__title">
                            <span class="home-frame-hero__hash" aria-hidden="true">#</span>{{ $slide['title'] }}
                        </h1>
                    @else
                        <h2 class="home-frame-hero__title">
                            <span class="home-frame-hero__hash" aria-hidden="true">#</span>{{ $slide['title'] }}
                        </h2>
                    @endif

                    <p class="home-frame-hero__manifesto">آموزش خوب، مسیر روشن‌تری برای آینده می‌سازد.</p>

                    @if(filled($slide['description'] ?? null))
                        <p class="home-frame-hero__description">
                            {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $slide['description'])), 170) }}
                        </p>
                    @else
                        <p class="home-frame-hero__description">
                            از یادگیری و تمرین تا سنجش پیشرفت؛ یک مسیر منظم برای قدم بعدی تو.
                        </p>
                    @endif

                    <div class="home-frame-hero__actions">
                        <a class="home-frame-hero__cta" href="{{ $slide['href'] ?: route('courses.index') }}">
                            <span>ورود به مسیر</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M19 12H5m6 6-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="square" stroke-linejoin="miter"/>
                            </svg>
                        </a>
                        <span class="home-frame-hero__meta">LEARN <i>/</i> PRACTICE <i>/</i> GROW</span>
                    </div>
                </div>
                <span class="home-frame-hero__vertical-mark" aria-hidden="true">SHEYKHAN / LEARNING SYSTEM</span>
            </article>
        @empty
            <article class="home-frame-hero__slide home-frame-hero__slide--fallback is-active" data-frame-slide aria-hidden="false" role="group">
                <div class="home-frame-hero__media" aria-hidden="true"></div>
                <div class="home-frame-hero__scrim" aria-hidden="true"></div>
                <div class="home-frame-hero__content">
                    <div class="home-frame-hero__eyebrow">
                        <span class="home-frame-hero__signal" aria-hidden="true"></span>
                        <span>آکادمی شیخان</span>
                        <span class="home-frame-hero__eyebrow-rule" aria-hidden="true"></span>
                        <span>LEARNING SYSTEM</span>
                    </div>
                    <h1 class="home-frame-hero__title">آموزش خوب، <em>مسیر روشن‌تر.</em></h1>
                    <p class="home-frame-hero__manifesto">از یادگیری تا پیشرفت؛ همه‌چیز در یک مسیر.</p>
                    <p class="home-frame-hero__description">
                        دوره‌ها، کلاس‌ها، تمرین‌ها و منابع آموزشی شیخان را یک‌جا ببین و مسیرت را شروع کن.
                    </p>
                    <div class="home-frame-hero__actions">
                        <a class="home-frame-hero__cta" href="{{ route('courses.index') }}">
                            <span>کشف مسیرهای یادگیری</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M19 12H5m6 6-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="square" stroke-linejoin="miter"/>
                            </svg>
                        </a>
                        <span class="home-frame-hero__meta">LEARN <i>/</i> PRACTICE <i>/</i> GROW</span>
                    </div>
                </div>
                <span class="home-frame-hero__vertical-mark" aria-hidden="true">SHEYKHAN / LEARNING SYSTEM</span>
            </article>
        @endforelse
    </div>

    <div class="home-frame-hero__texture" aria-hidden="true"></div>
    <div class="home-frame-hero__corner home-frame-hero__corner--tl" aria-hidden="true"></div>
    <div class="home-frame-hero__corner home-frame-hero__corner--tr" aria-hidden="true"></div>
    <div class="home-frame-hero__corner home-frame-hero__corner--bl" aria-hidden="true"></div>
    <div class="home-frame-hero__corner home-frame-hero__corner--br" aria-hidden="true"></div>
    <div class="home-frame-hero__impact" data-frame-impact aria-hidden="true"></div>

    <div class="home-frame-hero__topline" aria-hidden="true">
        <span>SHK <i>/</i> ACADEMY</span>
        <span>PUBLIC LEARNING PLATFORM <b>·</b> ۱۴۰۵</span>
    </div>

    <div class="home-frame-hero__footer">
        <div class="home-frame-hero__signature">
            <span class="home-frame-hero__signature-mark" aria-hidden="true">ش</span>
            <span><strong>شیخان</strong><small>آموزش · رشد · آینده</small></span>
        </div>

        @if($slideCount > 1)
            <div class="home-frame-hero__controls" aria-label="کنترل اسلایدهای دوره">
                <button type="button" data-frame-prev aria-label="اسلاید قبلی">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M19 12H5m6 6-6-6 6-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="square"/>
                    </svg>
                </button>
                <div class="home-frame-hero__pagination">
                    @foreach($slides as $slide)
                        <button
                            type="button"
                            data-frame-goto="{{ $loop->index }}"
                            class="{{ $loop->first ? 'is-active' : '' }}"
                            aria-label="نمایش {{ \App\Support\PersianUi::digits($loop->iteration) }}: {{ $slide['title'] }}"
                            aria-current="{{ $loop->first ? 'true' : 'false' }}"
                        ><span></span></button>
                    @endforeach
                </div>
                <button type="button" data-frame-next aria-label="اسلاید بعدی">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="square"/>
                    </svg>
                </button>
            </div>
            <div class="home-frame-hero__counter" aria-live="polite">
                <span data-frame-current>۰۱</span>
                <i>/</i>
                <span>{{ \App\Support\PersianUi::digits($slideCount) }}</span>
            </div>
        @else
            <div class="home-frame-hero__footer-note">
                <span class="home-frame-hero__signal" aria-hidden="true"></span>
                <span>مسیر یادگیری از همین‌جا شروع می‌شود</span>
            </div>
        @endif
    </div>

    <span class="home-frame-hero__edge-label" aria-hidden="true">FRAME / EXPERIENCE 01</span>
</section>
