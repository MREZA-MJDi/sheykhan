@extends('layouts.owner')

@section('title', 'بنرهای صفحه اصلی | شیخان')
@section('header-title', 'بنرهای صفحه اصلی')

@section('content')
    @php
        $bannerSlots = $academy->homeBanners->keyBy('slot');
    @endphp

    <div class="banner-page space-y-5">
        <section class="banner-page-head">
            <div>
                <span class="banner-kicker">{{ $academy->name }}</span>
                <h1>کنترل تصویر صفحه اصلی</h1>
                <p>
                    تصویر را انتخاب کن، نقطهٔ تمرکز را تنظیم کن و پیش از ذخیره، قاب دسکتاپ هیرو را ببین.
                    فایل اصلی دست‌نخورده می‌ماند؛ تصویر در هیرو با رنگ اصلی و برش متناسب با قاب تمام‌عرض نمایش داده می‌شود.
                </p>
                <p class="banner-scope-note">
                    این سه جایگاه، اسلایدهای هیرو در ابتدای صفحه اصلی‌اند. اسلایدهای فعال به ترتیب می‌چرخند؛ عنوان، متن، دکمه و نقطهٔ تمرکز هر اسلاید جداگانه ذخیره می‌شود.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="course-secondary-btn">مشاهده سایت ↗</a>
                <a href="{{ route('owner.website.index') }}" class="course-secondary-btn">مرکز سایت</a>
            </div>
        </section>

        <form
            method="POST"
            action="{{ route('owner.academy.banners.update', $academy) }}"
            enctype="multipart/form-data"
            class="grid gap-4"
            data-banner-editor
        >
            @csrf
            @method('PATCH')

            @for($slot = 1; $slot <= 3; $slot++)
                @php
                    $banner = $bannerSlots->get($slot);
                    $currentMedia = $banner?->media;
                    $currentImage = $currentMedia?->url();
                    $cropX = (int) old("banners.$slot.crop_x", $banner?->crop_x ?? 50);
                    $cropY = (int) old("banners.$slot.crop_y", $banner?->crop_y ?? 50);
                @endphp

                <section class="banner-slot-card" data-banner-slot>
                    <div class="banner-slot-head">
                        <div class="banner-slot-number">{{ $slot }}</div>
                        <div>
                            <span>جایگاه {{ $slot }}</span>
                            <h2>{{ $banner?->title ?: 'بنر شماره ' . $slot }}</h2>
                        </div>

                        <label class="banner-switch">
                            <input
                                type="checkbox"
                                name="banners[{{ $slot }}][is_active]"
                                value="1"
                                @checked(old("banners.$slot.is_active", $banner?->is_active ?? false))
                            >
                            <span>فعال در هیرو</span>
                        </label>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.1fr)_minmax(21rem,.9fr)]">
                        <div class="banner-editor-preview-wrap">
                            <article class="banner-preview-card" data-banner-preview-card>
                                <div
                                    class="banner-preview"
                                    data-banner-preview
                                    data-banner-initial-image="{{ $currentImage ?? '' }}"
                                >
                                    @if($currentImage)
                                        <img src="{{ $currentImage }}" alt="" data-banner-preview-image style="object-position: {{ $cropX }}% {{ $cropY }}%;">
                                    @endif
                                    <div class="banner-preview-empty" data-banner-preview-empty @if($currentImage) hidden @endif>
                                        <strong>هنوز تصویری انتخاب نشده</strong>
                                        <span>پس از انتخاب فایل، قاب دقیق نمایش عمومی اینجا دیده می‌شود.</span>
                                    </div>
                                    <span class="banner-preview-academy">{{ $academy->name }}</span>
                                </div>
                                <div class="banner-preview-body">
                                    <div class="banner-preview-copy">
                                        <h3 data-banner-preview-title @if(blank($banner?->title)) hidden @endif>{{ $banner?->title }}</h3>
                                        <p data-banner-preview-description @if(blank($banner?->description)) hidden @endif>{{ $banner?->description }}</p>
                                    </div>
                                    <span class="banner-preview-cta" data-banner-preview-cta @if(blank($banner?->cta_label) || blank($banner?->cta_url)) hidden @endif>
                                        <span data-banner-preview-cta-label>{{ $banner?->cta_label }}</span><span aria-hidden="true">←</span>
                                    </span>
                                </div>
                            </article>
                            <div class="banner-preview-caption">
                                <strong>قاب دسکتاپ هیرو: تمام‌عرض</strong>
                                <span data-banner-preview-dimensions>پیش‌نمایش قاب دسکتاپ است؛ در موبایل قاب بلندتر می‌شود. نقطه تمرکز را طوری تنظیم کن که سوژه اصلی دیده بماند.</span>
                            </div>
                            <div class="banner-crop-tools">
                                <div class="banner-crop-head">
                                    <div>
                                        <strong>نقطهٔ تمرکز تصویر</strong>
                                        <span>تنظیم با object-position در سایت عمومی هم عیناً استفاده می‌شود؛ فایل اصلی برش یا بازذخیره نمی‌شود.</span>
                                    </div>
                                    <button type="button" class="banner-reset-btn" data-banner-reset>مرکز</button>
                                </div>
                                <label class="banner-range">
                                    <span><b>تمرکز افقی</b><output data-crop-x-output>{{ $cropX }}٪</output></span>
                                    <input type="range" min="0" max="100" value="{{ $cropX }}" name="banners[{{ $slot }}][crop_x]" data-crop-x aria-label="نقطه تمرکز افقی تصویر">
                                </label>
                                <label class="banner-range">
                                    <span><b>تمرکز عمودی</b><output data-crop-y-output>{{ $cropY }}٪</output></span>
                                    <input type="range" min="0" max="100" value="{{ $cropY }}" name="banners[{{ $slot }}][crop_y]" data-crop-y aria-label="نقطه تمرکز عمودی تصویر">
                                </label>
                            </div>
                        </div>
                        <div class="grid gap-3">
                            <label class="banner-field">
                                <span>انتخاب از تصاویر آموزشگاه</span>
                                <select name="banners[{{ $slot }}][media_id]" data-banner-media-select>
                                    <option value="">— تصویر موجود را انتخاب کن —</option>
                                    @foreach($academy->media as $media)
                                        <option
                                            value="{{ $media->id }}"
                                            data-media-url="{{ $media->url() }}"
                                            @selected((string) old("banners.$slot.media_id", $banner?->media_id) === (string) $media->id)
                                        >
                                            {{ $media->original_name ?: 'تصویر #' . $media->id }}
                                        </option>
                                    @endforeach
                                </select>
                                <small>فقط تصاویر عمومی و فعال آموزشگاه.</small>
                            </label>

                            <label class="banner-field">
                                <span>یا تصویر جدید</span>
                                <input
                                    type="file"
                                    name="banners[{{ $slot }}][image]"
                                    accept="image/jpeg,image/png,image/webp,image/avif"
                                    data-banner-file
                                    data-allowed-extensions="jpg,jpeg,png,webp,avif"
                                    data-max-size-mb="5"
                                    data-recommended-width="1920"
                                    data-recommended-height="800"
                                    aria-describedby="banner-file-help-{{ $slot }} banner-file-feedback-{{ $slot }}"
                                >
                                <small id="banner-file-help-{{ $slot }}">JPG، PNG، WEBP یا AVIF · حداکثر ۵ مگابایت · پیشنهاد برای هیرو: ۱۹۲۰×۸۰۰ پیکسل</small>
                                <span id="banner-file-feedback-{{ $slot }}" class="banner-file-feedback" data-banner-file-feedback role="status" aria-live="polite"></span>
                            </label>

                            <div class="banner-file-meta" data-banner-file-meta hidden>
                                <strong data-banner-file-name></strong>
                                <span data-banner-file-size></span>
                                <span data-banner-file-dimensions></span>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="banner-field">
                                    <span>عنوان</span>
                                    <input type="text" name="banners[{{ $slot }}][title]" value="{{ old("banners.$slot.title", $banner?->title) }}" placeholder="عنوان کوتاه" data-banner-title-input>
                                </label>

                                <label class="banner-field">
                                    <span>متن دکمه</span>
                                    <input type="text" name="banners[{{ $slot }}][cta_label]" value="{{ old("banners.$slot.cta_label", $banner?->cta_label) }}" placeholder="مشاهده دوره‌ها" data-banner-cta-label-input>
                                </label>
                            </div>

                            <label class="banner-field">
                                <span>توضیح کوتاه</span>
                                <textarea name="banners[{{ $slot }}][description]" rows="3" placeholder="یک جمله کوتاه" data-banner-description-input>{{ old("banners.$slot.description", $banner?->description) }}</textarea>
                            </label>

                            <label class="banner-field">
                                <span>لینک دکمه</span>
                                <input type="text" name="banners[{{ $slot }}][cta_url]" value="{{ old("banners.$slot.cta_url", $banner?->cta_url) }}" placeholder="/courses یا https://..." data-banner-cta-url-input aria-describedby="banner-cta-url-feedback-{{ $slot }}">
                                <small id="banner-cta-url-feedback-{{ $slot }}" data-banner-cta-url-feedback role="status" aria-live="polite"></small>
                            </label>
                        </div>
                    </div>
                </section>
            @endfor

            <div class="banner-submitbar">
                <div>
                    <strong>پیش‌نمایش واقعی + انتشار</strong>
                    <span>تنظیم قاب‌بندی برای هر بنر جداگانه ذخیره می‌شود.</span>
                </div>
                <button type="submit" class="course-primary-btn">ذخیره تغییرات</button>
            </div>
        </form>
    </div>
@endsection
