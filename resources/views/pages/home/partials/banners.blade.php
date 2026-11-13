@if(isset($homeBanners) && $homeBanners->isNotEmpty())
    <section class="sh-home-section sh-home-banners" aria-labelledby="sh-home-banners-title">
        <div class="sh-section-head">
            <div>
                <span class="sh-eyebrow">خبرهای مهم</span>
                <h2 id="sh-home-banners-title">اطلاعیه‌ها و پیشنهادهای ویژه</h2>
                <p>بنرهای مستقلی که مدیر آموزشگاه منتشر کرده است.</p>
            </div>
        </div>
        <div class="home-banner-grid">
            @foreach($homeBanners as $banner)
                <article class="home-banner-card">
                    <div class="home-banner-card__media">
                        <img
                            src="{{ $banner['image'] }}"
                            alt="{{ $banner['title'] }}"
                            loading="lazy"
                            style="object-position: {{ (int) ($banner['cropX'] ?? 50) }}% {{ (int) ($banner['cropY'] ?? 50) }}%;"
                        >
                    </div>
                    <div class="home-banner-card__copy">
                        <span>{{ $banner['academy'] ?: 'آکادمی شیخان' }}</span>
                        <h3>{{ $banner['title'] }}</h3>
                        @if(filled($banner['description'] ?? null))
                            <p>{{ $banner['description'] }}</p>
                        @endif
                        @if(filled($banner['ctaUrl'] ?? null) && filled($banner['ctaLabel'] ?? null))
                            <a href="{{ $banner['ctaUrl'] }}">{{ $banner['ctaLabel'] }} <b>←</b></a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
