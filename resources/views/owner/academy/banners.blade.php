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
                    تصویر را انتخاب کن، قاب آن را با نقطه تمرکز تنظیم کن و قبل از ذخیره نتیجه را همان‌جا ببین.
                    فایل اصلی دست‌نخورده می‌ماند.
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
                            <span>نمایش</span>
                        </label>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.1fr)_minmax(21rem,.9fr)]">
                        <div class="banner-editor-preview-wrap">
                            <div class="banner-preview" data-banner-preview>
                                @if($currentImage)
                                    <img
                                        src="{{ $currentImage }}"
                                        alt=""
                                        data-banner-preview-image
                                        style="object-position: {{ $cropX }}% {{ $cropY }}%;"
                                    >
                                @else
                                    <div class="banner-preview-empty" data-banner-preview-empty>
                                        <strong>هنوز تصویری برای این جایگاه انتخاب نشده</strong>
                                        <span>یک تصویر عریض انتخاب کن تا پیش‌نمایش واقعی قاب را ببینی.</span>
                                    </div>
                                @endif
                            </div>

                            <div class="banner-crop-tools">
                                <div class="banner-crop-head">
                                    <div>
                                        <strong>قاب‌بندی تصویر</strong>
                                        <span>بدون برش فایل اصلی؛ فقط نقطه تمرکز نمایش تغییر می‌کند.</span>
                                    </div>
                                    <button type="button" class="banner-reset-btn" data-banner-reset>مرکز</button>
                                </div>

                                <label class="banner-range">
                                    <span><b>افقی</b><output data-crop-x-output>{{ $cropX }}٪</output></span>
                                    <input type="range" min="0" max="100" value="{{ $cropX }}" name="banners[{{ $slot }}][crop_x]" data-crop-x>
                                </label>

                                <label class="banner-range">
                                    <span><b>عمودی</b><output data-crop-y-output>{{ $cropY }}٪</output></span>
                                    <input type="range" min="0" max="100" value="{{ $cropY }}" name="banners[{{ $slot }}][crop_y]" data-crop-y>
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
                                >
                                <small>JPG، PNG، WEBP یا AVIF · حداکثر ۵ مگابایت</small>
                            </label>

                            <div class="banner-file-meta" data-banner-file-meta hidden>
                                <strong data-banner-file-name></strong>
                                <span data-banner-file-size></span>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="banner-field">
                                    <span>عنوان</span>
                                    <input type="text" name="banners[{{ $slot }}][title]" value="{{ old("banners.$slot.title", $banner?->title) }}" placeholder="عنوان کوتاه">
                                </label>

                                <label class="banner-field">
                                    <span>متن دکمه</span>
                                    <input type="text" name="banners[{{ $slot }}][cta_label]" value="{{ old("banners.$slot.cta_label", $banner?->cta_label) }}" placeholder="مشاهده دوره‌ها">
                                </label>
                            </div>

                            <label class="banner-field">
                                <span>توضیح کوتاه</span>
                                <textarea name="banners[{{ $slot }}][description]" rows="3" placeholder="یک جمله کوتاه">{{ old("banners.$slot.description", $banner?->description) }}</textarea>
                            </label>

                            <label class="banner-field">
                                <span>لینک دکمه</span>
                                <input type="text" name="banners[{{ $slot }}][cta_url]" value="{{ old("banners.$slot.cta_url", $banner?->cta_url) }}" placeholder="/courses یا https://...">
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
