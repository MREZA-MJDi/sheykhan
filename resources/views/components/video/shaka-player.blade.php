@props([
    'manifestUrl' => null,
    'title' => 'پخش ویدئوی آموزشی',
    'poster' => null,
    'watermark' => null,
])

<section
    class="shaka-player-card"
    data-shaka-player-root
    data-manifest-url="{{ $manifestUrl }}"
    lang="fa"
    dir="rtl"
    aria-label="{{ $title }}"
>
    <header class="shaka-player-card__header">
        <h2 class="shaka-player-card__title">{{ $title }}</h2>
        <span class="shaka-player-card__label">پلیر شیخان · آزمایشی</span>
    </header>

    <div class="shaka-player-card__stage" data-shaka-player-container>
        <video
            data-shaka-video
            playsinline
            preload="metadata"
            aria-label="{{ $title }}"
            @if($poster) poster="{{ $poster }}" @endif
        ></video>
        @if($watermark)
            <span class="shaka-player-card__watermark" aria-hidden="true">{{ $watermark }}</span>
        @endif
    </div>

    <p class="shaka-player-card__status" data-shaka-status role="status" aria-live="polite">
        در حال آماده‌سازی پلیر فارسی…
    </p>
</section>
