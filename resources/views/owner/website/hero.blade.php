@extends('layouts.seo-specialist')

@section('title', 'مدیریت اسلایدهای Hero | شیخان')
@section('header-title', 'اسلایدهای Hero صفحه اصلی')

@section('content')
    @php
        $fallbackImage = asset('images/home/hero-fallback.svg');
        $oldSlot = (int) old('slot', 0);
    @endphp

    <div class="website-hero-editor">
        <header class="website-hero-editor-head">
            <div>
                <span class="website-kicker">اسلایدر مستقل سایت عمومی</span>
                <h1>مدیریت اسلایدهای Hero</h1>
                <p>تا ۵ اسلاید مستقل بساز و هرکدام را جداگانه ذخیره کن. ترتیب اسلایدها بر اساس جایگاه است؛ فقط اسلایدهای فعال و دارای تصویر دسکتاپ در سایت می‌چرخند. بنرهای پایین صفحه از این بخش جدا هستند.</p>
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
                <strong class="block mb-2">اسلاید ذخیره نشد؛ موارد زیر را بررسی کن:</strong>
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid gap-6">
            @for($slot = 1; $slot <= $maxSlides; $slot++)
                @php
                    $slide = $slides->get($slot);
                    $desktopImage = $slide?->media?->url();
                    $mobileImage = $slide?->mobileMedia?->url();
                    $desktopPreview = $desktopImage ?: $fallbackImage;
                    $mobilePreview = $mobileImage ?: $desktopPreview;
                    $cropX = max(0, min(100, (int) ($oldSlot === $slot ? old('crop_x', $slide?->crop_x ?? 50) : ($slide?->crop_x ?? 50))));
                    $cropY = max(0, min(100, (int) ($oldSlot === $slot ? old('crop_y', $slide?->crop_y ?? 46) : ($slide?->crop_y ?? 46))));
                    $mobileCropX = max(0, min(100, (int) ($oldSlot === $slot ? old('mobile_crop_x', $slide?->mobile_crop_x ?? 54) : ($slide?->mobile_crop_x ?? 54))));
                    $mobileCropY = max(0, min(100, (int) ($oldSlot === $slot ? old('mobile_crop_y', $slide?->mobile_crop_y ?? 50) : ($slide?->mobile_crop_y ?? 50))));
                    $title = $oldSlot === $slot ? old('title', $slide?->title) : $slide?->title;
                    $description = $oldSlot === $slot ? old('description', $slide?->description) : $slide?->description;
                    $ctaLabel = $oldSlot === $slot ? old('cta_label', $slide?->cta_label) : $slide?->cta_label;
                    $ctaUrl = $oldSlot === $slot ? old('cta_url', $slide?->cta_url) : $slide?->cta_url;
                    $isActive = $oldSlot === $slot ? (bool) old('is_active', $slide?->is_active ?? false) : (bool) ($slide?->is_active ?? false);
                @endphp

                <form
                    method="POST"
                    action="{{ route('owner.website.hero.update') }}"
                    enctype="multipart/form-data"
                    class="website-hero-editor-form"
                    data-home-hero-editor
                    data-current-desktop="{{ $desktopImage ?? '' }}"
                    data-current-mobile="{{ $mobileImage ?? '' }}"
                    data-default-image="{{ $fallbackImage }}"
                    data-fallback-image="{{ $fallbackImage }}"
                    aria-label="ویرایش اسلاید {{ $slot }}"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="slot" value="{{ $slot }}">

                    <div class="website-hero-editor-section-head">
                        <div>
                            <h2>اسلاید {{ $slot }}</h2>
                            <p>{{ $slide ? 'اسلاید ذخیره‌شده؛ تغییرات این جایگاه مستقل ذخیره می‌شوند.' : 'جایگاه خالی؛ برای ساخت اسلاید تصویر انتخاب کن.' }}</p>
                        </div>
                        <label class="website-hero-check">
                            <input type="checkbox" name="is_active" value="1" @checked($isActive)>
                            <span><strong>فعال در اسلایدر</strong><small>جایگاه‌های فعال به ترتیب شماره نمایش داده می‌شوند.</small></span>
                        </label>
                    </div>

                    <section class="website-hero-preview-grid" aria-label="پیش‌نمایش اسلاید {{ $slot }}">
                        <article class="website-hero-preview-card">
                            <div class="website-hero-preview-heading">
                                <div><strong>نمای دسکتاپ</strong><span>قاب عریض · تقریباً ۲:۱</span></div>
                                <span class="website-hero-viewport-badge">Desktop</span>
                            </div>
                            <div class="website-hero-preview website-hero-preview--desktop">
                                <img
                                    src="{{ $desktopPreview }}"
                                    alt=""
                                    data-home-hero-preview-desktop
                                    data-fallback-src="{{ $fallbackImage }}"
                                    style="object-position: {{ $cropX }}% {{ $cropY }}%;"
                                >
                                <div class="website-hero-preview-overlay"></div>
                                <div class="website-hero-preview-copy">
                                    <span>شیخان؛ آموزش، رشد، آینده</span>
                                    <strong>{{ $title ?: 'آینده‌ات را با یادگیری بساز' }}</strong>
                                    <small>{{ $description ?: 'از دوره و کلاس تا تمرین و آزمون' }}</small>
                                </div>
                            </div>
                            <p class="website-hero-preview-caption" data-home-hero-desktop-info>
                                {{ $desktopImage ? 'تصویر فعلی در کادر عریض نمایش داده می‌شود.' : 'برای انتشار این اسلاید، تصویر دسکتاپ انتخاب کن.' }}
                            </p>
                        </article>

                        <article class="website-hero-preview-card">
                            <div class="website-hero-preview-heading">
                                <div><strong>نمای موبایل</strong><span>قاب عمودی · ۹:۱۶</span></div>
                                <span class="website-hero-viewport-badge">Mobile</span>
                            </div>
                            <div class="website-hero-preview website-hero-preview--mobile">
                                <img
                                    src="{{ $mobilePreview }}"
                                    alt=""
                                    data-home-hero-preview-mobile
                                    data-fallback-src="{{ $fallbackImage }}"
                                    style="object-position: {{ $mobileCropX }}% {{ $mobileCropY }}%;"
                                >
                                <div class="website-hero-preview-overlay"></div>
                                <div class="website-hero-preview-copy">
                                    <span>شیخان؛ آموزش، رشد، آینده</span>
                                    <strong>{{ $title ?: 'آینده‌ات را با یادگیری بساز' }}</strong>
                                    <small>{{ $description ?: 'از دوره و کلاس تا تمرین و آزمون' }}</small>
                                </div>
                            </div>
                            <p class="website-hero-preview-caption" data-home-hero-mobile-info>
                                {{ $mobileImage ? 'تصویر اختصاصی موبایل فعال است.' : 'در موبایل همان تصویر دسکتاپ با قاب‌بندی جداگانه استفاده می‌شود.' }}
                            </p>
                        </article>
                    </section>

                    <section class="website-hero-editor-panel">
                        <div class="website-hero-editor-section-head">
                            <h3>۱. تصویرهای همین اسلاید</h3>
                            <p>هر جایگاه فایل‌های خودش را دارد و به تصاویر بخش Banner وابسته نیست.</p>
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
                                    aria-describedby="home-hero-{{ $slot }}-desktop-help home-hero-{{ $slot }}-desktop-feedback"
                                >
                                <small id="home-hero-{{ $slot }}-desktop-help">JPG، PNG، WEBP یا AVIF · حداکثر ۸ مگابایت · حداقل ۱۲۰۰×۶۰۰</small>
                                <span id="home-hero-{{ $slot }}-desktop-feedback" class="website-hero-upload-feedback" data-home-hero-feedback="desktop" role="status" aria-live="polite"></span>
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
                                    aria-describedby="home-hero-{{ $slot }}-mobile-help home-hero-{{ $slot }}-mobile-feedback"
                                >
                                <small id="home-hero-{{ $slot }}-mobile-help">JPG، PNG، WEBP یا AVIF · حداکثر ۵ مگابایت · حداقل ۶۰۰×۸۰۰</small>
                                <span id="home-hero-{{ $slot }}-mobile-feedback" class="website-hero-upload-feedback" data-home-hero-feedback="mobile" role="status" aria-live="polite"></span>
                            </label>
                        </div>

                        @if($slide?->media)
                            <p class="website-hero-current-file">تصویر دسکتاپ فعلی: <strong>{{ $slide->media->original_name }}</strong></p>
                        @endif
                        @if($slide?->mobileMedia)
                            <p class="website-hero-current-file">تصویر موبایل فعلی: <strong>{{ $slide->mobileMedia->original_name }}</strong></p>
                        @endif

                        <label class="website-hero-check">
                            <input type="checkbox" name="remove_mobile_image" value="1" @checked($oldSlot === $slot ? old('remove_mobile_image') : false) data-home-hero-remove-mobile>
                            <span><strong>حذف تصویر اختصاصی موبایل</strong><small>نسخهٔ موبایل از این به بعد از تصویر دسکتاپ استفاده می‌کند.</small></span>
                        </label>
                    </section>

                    <section class="website-hero-editor-panel">
                        <div class="website-hero-editor-section-head">
                            <h3>۲. متن و لینک اسلاید</h3>
                            <p>این محتوا فقط برای همین اسلاید استفاده می‌شود.</p>
                        </div>
                        <div class="website-hero-upload-grid">
                            <label class="website-hero-upload-field">
                                <span>عنوان اسلاید</span>
                                <input type="text" name="title" maxlength="120" value="{{ $title }}" placeholder="آینده‌ات را با یادگیری بساز">
                            </label>
                            <label class="website-hero-upload-field">
                                <span>متن توضیحی</span>
                                <textarea name="description" rows="3" maxlength="700" placeholder="متن کوتاه و روشن برای معرفی این اسلاید">{{ $description }}</textarea>
                            </label>
                            <label class="website-hero-upload-field">
                                <span>متن دکمه (اختیاری)</span>
                                <input type="text" name="cta_label" maxlength="80" value="{{ $ctaLabel }}" placeholder="مشاهده دوره‌ها">
                            </label>
                            <label class="website-hero-upload-field">
                                <span>لینک دکمه (اختیاری)</span>
                                <input type="text" name="cta_url" maxlength="2048" value="{{ $ctaUrl }}" placeholder="/courses">
                                <small>لینک داخلی مانند /courses یا لینک کامل HTTPS.</small>
                            </label>
                        </div>
                    </section>

                    <section class="website-hero-editor-panel">
                        <div class="website-hero-editor-section-head">
                            <h3>۳. نقطهٔ تمرکز تصویر</h3>
                            <p>قاب دسکتاپ و موبایل جداگانه تنظیم می‌شود؛ فایل اصلی دست‌نخورده می‌ماند.</p>
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

                    @if($slide)
                        <label class="website-hero-check website-hero-check--restore">
                            <input type="checkbox" name="remove_slide" value="1" @checked($oldSlot === $slot ? old('remove_slide') : false) data-home-hero-restore>
                            <span><strong>حذف کامل این اسلاید</strong><small>با ذخیره، این جایگاه از اسلایدر حذف می‌شود و فایل‌های جایگزین‌نشده فقط در صورت استفاده‌نشدن جای دیگری پاک می‌شوند.</small></span>
                        </label>
                    @endif

                    <div class="website-hero-savebar">
                        <div>
                            <strong>اسلاید {{ $slot }}</strong>
                            <span>ذخیرهٔ این فرم، اسلایدهای دیگر و بنرهای پایین صفحه را تغییر نمی‌دهد.</span>
                        </div>
                        <button type="submit" class="course-primary-btn" data-home-hero-submit>ذخیرهٔ اسلاید {{ $slot }}</button>
                    </div>
                </form>
            @endfor
        </div>
    </div>
@endsection
