@extends('layouts.owner')

@section('title', 'بنرهای صفحه اصلی | شیخان')
@section('header-title', 'بنرهای صفحه اصلی')

@section('content')
    @php
        $bannerSlots = $academy->homeBanners->keyBy('slot');
    @endphp

    <div class="banner-page space-y-5">
        <div class="banner-page-head">
            <div>
                <span class="banner-kicker">{{ $academy->name }}</span>
                <h1>ویژوال اسلایدر صفحه اصلی</h1>
                <p>
                    تا ۳ بنر برای لندینگ انتخاب کن. تصویر، متن و دکمه اختیاری هستند و فقط بنرهای فعال در صفحه اصلی نمایش داده می‌شوند.
                </p>
            </div>

            <a href="{{ route('owner.academy.edit', $academy) }}" class="course-secondary-btn">
                تنظیمات آموزشگاه
            </a>
        </div>

        <form
            method="POST"
            action="{{ route('owner.academy.banners.update', $academy) }}"
            enctype="multipart/form-data"
            class="grid gap-4"
        >
            @csrf
            @method('PATCH')

            @for($slot = 1; $slot <= 3; $slot++)
                @php
                    $banner = $bannerSlots->get($slot);
                    $currentMedia = $banner?->media;
                    $currentImage = $currentMedia?->url();
                @endphp

                <section class="banner-slot-card">
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
                        <div class="banner-preview">
                            @if($currentImage)
                                <img src="{{ $currentImage }}" alt="{{ $banner?->title ?: 'بنر جایگاه ' . $slot }}">
                            @else
                                <div class="banner-preview-empty">
                                    <strong>هنوز تصویری برای این جایگاه انتخاب نشده</strong>
                                    <span>یک تصویر عریض انتخاب کن تا در لندینگ نمایش داده شود.</span>
                                </div>
                            @endif
                        </div>

                        <div class="grid gap-3">
                            <label class="banner-field">
                                <span>انتخاب از تصاویر عمومی آموزشگاه</span>
                                <select name="banners[{{ $slot }}][media_id]">
                                    <option value="">— تصویر موجود را انتخاب کن —</option>
                                    @foreach($academy->media as $media)
                                        <option
                                            value="{{ $media->id }}"
                                            @selected((string) old("banners.$slot.media_id", $banner?->media_id) === (string) $media->id)
                                        >
                                            {{ $media->original_name ?: 'تصویر #' . $media->id }}
                                        </option>
                                    @endforeach
                                </select>
                                <small>فقط تصاویر عمومی و فعال آموزشگاه در این فهرست هستند.</small>
                            </label>

                            <label class="banner-field">
                                <span>یا تصویر جدید انتخاب کن</span>
                                <input type="file" name="banners[{{ $slot }}][image]" accept="image/jpeg,image/png,image/webp,image/avif">
                                <small>JPG، PNG، WEBP یا AVIF · حداکثر ۵ مگابایت</small>
                            </label>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="banner-field">
                                    <span>عنوان</span>
                                    <input type="text" name="banners[{{ $slot }}][title]" value="{{ old("banners.$slot.title", $banner?->title) }}" placeholder="مثلاً شروع یک فصل تازه">
                                </label>

                                <label class="banner-field">
                                    <span>متن دکمه</span>
                                    <input type="text" name="banners[{{ $slot }}][cta_label]" value="{{ old("banners.$slot.cta_label", $banner?->cta_label) }}" placeholder="مشاهده دوره‌ها">
                                </label>
                            </div>

                            <label class="banner-field">
                                <span>توضیح کوتاه</span>
                                <textarea name="banners[{{ $slot }}][description]" rows="3" placeholder="یک جمله کوتاه و ملموس برای روی بنر">{{ old("banners.$slot.description", $banner?->description) }}</textarea>
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
                    <strong>نمایش روی Landing</strong>
                    <span>ترتیب نمایش همان جایگاه ۱ تا ۳ است؛ بنر غیرفعال یا بدون تصویر نمایش داده نمی‌شود.</span>
                </div>

                <button type="submit" class="course-primary-btn">
                    ذخیره ۳ بنر
                </button>
            </div>
        </form>
    </div>
@endsection
