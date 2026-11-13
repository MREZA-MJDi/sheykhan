@if(isset($homeHeroSlides) && $homeHeroSlides->isNotEmpty())
    <section class="sh-home-section sh-home-highlights" aria-labelledby="sh-home-highlights-title">
        <div class="sh-section-head">
            <div>
                <span class="sh-eyebrow">روایت‌های آموزشگاه</span>
                <h2 id="sh-home-highlights-title">ویژه‌های شیخان</h2>
                <p>تصاویر و پیام‌هایی که از پنل مدیریت آموزشگاه منتشر شده‌اند.</p>
            </div>
        </div>
        <div class="sh-home-highlight-grid">
            @foreach($homeHeroSlides as $slide)
                <article
                    class="sh-home-highlight-card"
                    data-home-hero-slide
                    style="--hero-crop-x: {{ (int) ($slide['cropX'] ?? 50) }}%; --hero-crop-y: {{ (int) ($slide['cropY'] ?? 46) }}%; --hero-mobile-crop-x: {{ (int) ($slide['mobileCropX'] ?? 54) }}%; --hero-mobile-crop-y: {{ (int) ($slide['mobileCropY'] ?? 50) }}%;"
                >
                    <div class="sh-home-highlight-media">
                        <picture>
                            @if(filled($slide['mobileImage'] ?? null))
                                <source srcset="{{ $slide['mobileImage'] }}" media="(max-width: 640px)" data-home-hero-mobile-source>
                            @endif
                            <img
                                src="{{ $slide['image'] }}"
                                data-home-hero-image
                                data-desktop-src="{{ $slide['image'] }}"
                                @if(filled($slide['fallbackImage'] ?? null)) data-fallback-src="{{ $slide['fallbackImage'] }}" @endif
                                alt=""
                                loading="lazy"
                                decoding="async"
                            >
                        </picture>
                        <span class="sh-home-highlight-number">{{ \App\Support\PersianUi::digits($loop->iteration) }}</span>
                    </div>
                    <div class="sh-home-highlight-copy">
                        <span class="sh-home-highlight-eyebrow">{{ $slide['academy'] ?: 'آکادمی شیخان' }}</span>
                        <h3>{{ $slide['title'] ?: 'قدم بعدی یادگیری را با شیخان بردار' }}</h3>
                        @if(filled($slide['description'] ?? null))
                            <p>{{ $slide['description'] }}</p>
                        @endif
                        @if(filled($slide['ctaUrl'] ?? null) && filled($slide['ctaLabel'] ?? null))
                            <a href="{{ $slide['ctaUrl'] }}" class="sh-text-link">{{ $slide['ctaLabel'] }} <span>←</span></a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
