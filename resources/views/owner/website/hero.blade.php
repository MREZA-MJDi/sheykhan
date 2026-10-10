@extends('layouts.owner')

@section('title', 'تصویر Hero صفحه اصلی | شیخان')
@section('header-title', 'تصویر Hero صفحه اصلی')

@section('content')
    @php
        $desktopImage = $heroConfig['image'] ?? '';
        $mobileImage = $heroConfig['mobileImage'] ?: $desktopImage;
        $defaultImage = $heroConfig['defaultImage'] ?? $desktopImage;
        $fallbackImage = asset('images/home/hero-fallback.svg');
        $cropX = max(0, min(100, (int) old('crop_x', $hero?->crop_x ?? 50)));
        $cropY = max(0, min(100, (int) old('crop_y', $hero?->crop_y ?? 46)));
        $mobileCropX = max(0, min(100, (int) old('mobile_crop_x', $hero?->mobile_crop_x ?? 54)));
        $mobileCropY = max(0, min(100, (int) old('mobile_crop_y', $hero?->mobile_crop_y ?? 50)));
    @endphp

    <div class="website-hero-editor">
        <header class="website-hero-editor-head">
            <div>
                <span class="website-kicker">تصویر اصلی سایت عمومی</span>
                <h1>قاب درست برای دسکتاپ و موبایل</h1>
                <p>تصویر بزرگ بالای صفحه را اینجا آپلود یا جایگزین کن. فایل اصلی تغییر نمی‌کند؛ فقط نقطهٔ تمرکز برای هر اندازهٔ صفحه ذخیره می‌شود.</p>
            </div>
            <div class="website-hero-editor-actions">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="course-secondary-btn">مشاهده سایت ↗</a>
                <a href="{{ route('owner.website.index') }}" class="course-secondary-btn">بازگشت به مرکز سایت</a>
            </div>
        </header>

        @if(session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <strong class="block mb-2">تصویر ذخیره نشد؛ موارد زیر را بررسی کن:</strong>
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('owner.website.hero.update') }}"
            enctype="multipart/form-data"
            class="website-hero-editor-form"
            data-home-hero-editor
            data-current-desktop="{{ $desktopImage }}"
            data-current-mobile="{{ $heroConfig['mobileImage'] ?? '' }}"
            data-default-image="{{ $defaultImage }}"
            data-fallback-image="{{ $fallbackImage }}"
        >
            @csrf
            @method('PATCH')

            <section class="website-hero-preview-grid" aria-label="پیش‌نمایش تصویر">
                <article class="website-hero-preview-card">
                    <div class="website-hero-preview-heading">
                        <div><strong>نمای دسکتاپ</strong><span>قاب عریض · ۱۶:۹</span></div>
                        <span class="website-hero-viewport-badge">Desktop</span>
                    </div>
                    <div class="website-hero-preview website-hero-preview--desktop">
                        <img
                            src="{{ $desktopImage }}"
                            alt=""
                            data-home-hero-preview-desktop
                            data-fallback-src="{{ $fallbackImage }}"
                            style="object-position: {{ $cropX }}% {{ $cropY }}%;"
                        >
                        <div class="website-hero-preview-overlay"></div>
                        <div class="website-hero-preview-copy">
                            <span>شیخان؛ آموزش، رشد، آینده</span>
                            <strong>آینده‌ات را با یادگیری بساز</strong>
                            <small>از دوره و کلاس تا تمرین و آزمون</small>
                        </div>
                    </div>
                    <p class="website-hero-preview-caption" data-home-hero-desktop-info>تصویر فعلی در کادر عریض نمایش داده می‌شود.</p>
                </article>

                <article class="website-hero-preview-card">
                    <div class="website-hero-preview-heading">
                        <div><strong>نمای موبایل</strong><span>قاب عمودی · ۴:۵</span></div>
                        <span class="website-hero-viewport-badge">Mobile</span>
                    </div>
                    <div class="website-hero-preview website-hero-preview--mobile">
                        <img
                            src="{{ $mobileImage }}"
                            alt=""
                            data-home-hero-preview-mobile
                            data-fallback-src="{{ $fallbackImage }}"
                            style="object-position: {{ $mobileCropX }}% {{ $mobileCropY }}%;"
                        >
                        <div class="website-hero-preview-overlay"></div>
                        <div class="website-hero-preview-copy">
                            <span>شیخان؛ آموزش، رشد، آینده</span>
                            <strong>آینده‌ات را با یادگیری بساز</strong>
                            <small>از دوره و کلاس تا تمرین و آزمون</small>
                        </div>
                    </div>
                    <p class="website-hero-preview-caption" data-home-hero-mobile-info>
                        {{ $heroConfig['mobileImage'] ? 'تصویر اختصاصی موبایل فعال است.' : 'در موبایل همان تصویر اصلی با قاب‌بندی جداگانه استفاده می‌شود.' }}
                    </p>
                </article>
            </section>

            <section class="website-hero-editor-panel">
                <div class="website-hero-editor-section-head">
                    <h2>۱. انتخاب تصویر</h2>
                    <p>تصویر جدید تا زمان ذخیره فقط در پیش‌نمایش مرورگر دیده می‌شود.</p>
                </div>

                <div class="website-hero-upload-grid">
                    <label class="website-hero-upload-field">
                        <span>تصویر اصلی دسکتاپ</span>
                        <input
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            data-home-hero-file="desktop"
                            data-max-size-mb="8"
                            data-min-width="1200"
                            data-min-height="600"
                            aria-describedby="home-hero-desktop-help home-hero-desktop-feedback"
                        >
                        <small id="home-hero-desktop-help">JPG، PNG، WEBP یا AVIF · حداکثر ۸ مگابایت · حداقل ۱۲۰۰×۶۰۰ · پیشنهاد ۱۹۲۰×۱۰۸۰</small>
                        <span id="home-hero-desktop-feedback" class="website-hero-upload-feedback" data-home-hero-feedback="desktop" role="status" aria-live="polite"></span>
                    </label>

                    <label class="website-hero-upload-field">
                        <span>تصویر جداگانه موبایل <em>اختیاری</em></span>
                        <input
                            type="file"
                            name="mobile_image"
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            data-home-hero-file="mobile"
                            data-max-size-mb="5"
                            data-min-width="600"
                            data-min-height="800"
                            aria-describedby="home-hero-mobile-help home-hero-mobile-feedback"
                        >
                        <small id="home-hero-mobile-help">JPG، PNG، WEBP یا AVIF · حداکثر ۵ مگابایت · حداقل ۶۰۰×۸۰۰ · پیشنهاد ۹۰۰×۱۱۲۵. اگر انتخاب نکنی، تصویر اصلی استفاده می‌شود.</small>
                        <span id="home-hero-mobile-feedback" class="website-hero-upload-feedback" data-home-hero-feedback="mobile" role="status" aria-live="polite"></span>
                    </label>
                </div>

                @if($hero?->media)
                    <p class="website-hero-current-file">تصویر دسکتاپ فعلی: <strong>{{ $hero->media->original_name }}</strong></p>
                @endif
                @if($hero?->mobileMedia)
                    <p class="website-hero-current-file">تصویر موبایل فعلی: <strong>{{ $hero->mobileMedia->original_name }}</strong></p>
                @endif

                <label class="website-hero-check">
                    <input type="checkbox" name="remove_mobile_image" value="1" @checked(old('remove_mobile_image')) data-home-hero-remove-mobile>
                    <span><strong>حذف تصویر اختصاصی موبایل</strong><small>نسخهٔ موبایل از این به بعد از تصویر اصلی استفاده می‌کند، ولی نقطهٔ تمرکز موبایل حفظ می‌شود.</small></span>
                </label>

                <label class="website-hero-check website-hero-check--restore">
                    <input type="checkbox" name="restore_default" value="1" @checked(old('restore_default')) data-home-hero-restore>
                    <span><strong>بازگشت به تصویر پیش‌فرض شیخان</strong><small>هر دو تصویر اختصاصی حذف می‌شوند و تصویر پیش‌فرض استفاده می‌شود. این گزینه را هم‌زمان با انتخاب فایل جدید فعال نکن.</small></span>
                </label>
            </section>

            <section class="website-hero-editor-panel">
                <div class="website-hero-editor-section-head">
                    <h2>۲. تنظیم نقطهٔ تمرکز</h2>
                    <p>اگر سوژه نزدیک لبهٔ تصویر است، محل نمایش آن را تنظیم کن. این کار فایل اصلی را برش نمی‌دهد یا دوباره فشرده نمی‌کند.</p>
                </div>

                <div class="website-hero-crop-grid">
                    <fieldset class="website-hero-crop-group">
                        <legend>دسکتاپ</legend>
                        <label class="website-hero-range">
                            <span>تمرکز افقی <output data-home-hero-range-output="crop_x">{{ $cropX }}٪</output></span>
                            <input type="range" name="crop_x" min="0" max="100" value="{{ $cropX }}" data-home-hero-range="crop_x">
                        </label>
                        <label class="website-hero-range">
                            <span>تمرکز عمودی <output data-home-hero-range-output="crop_y">{{ $cropY }}٪</output></span>
                            <input type="range" name="crop_y" min="0" max="100" value="{{ $cropY }}" data-home-hero-range="crop_y">
                        </label>
                        <button type="button" class="banner-reset-btn" data-home-hero-reset-crop="desktop">مرکز کادر دسکتاپ</button>
                    </fieldset>

                    <fieldset class="website-hero-crop-group">
                        <legend>موبایل</legend>
                        <label class="website-hero-range">
                            <span>تمرکز افقی <output data-home-hero-range-output="mobile_crop_x">{{ $mobileCropX }}٪</output></span>
                            <input type="range" name="mobile_crop_x" min="0" max="100" value="{{ $mobileCropX }}" data-home-hero-range="mobile_crop_x">
                        </label>
                        <label class="website-hero-range">
                            <span>تمرکز عمودی <output data-home-hero-range-output="mobile_crop_y">{{ $mobileCropY }}٪</output></span>
                            <input type="range" name="mobile_crop_y" min="0" max="100" value="{{ $mobileCropY }}" data-home-hero-range="mobile_crop_y">
                        </label>
                        <button type="button" class="banner-reset-btn" data-home-hero-reset-crop="mobile">مرکز کادر موبایل</button>
                    </fieldset>
                </div>
            </section>

            <div class="website-hero-savebar">
                <div><strong>پس از ذخیره چه می‌شود؟</strong><span>تصویر عمومی به‌روزرسانی می‌شود، کش صفحه اصلی پاک می‌شود و اگر فایل جدید خراب باشد، تصویر جایگزین محلی نمایش داده می‌شود.</span></div>
                <button type="submit" class="course-primary-btn" data-home-hero-submit>ذخیره و انتشار تصویر</button>
            </div>
        </form>
    </div>
@endsection
