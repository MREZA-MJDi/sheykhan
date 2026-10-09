@extends('layouts.owner')

@section('title', 'مرکز سایت | شیخان')
@section('header-title', 'مرکز سایت')

@section('content')
<div class="website-center page-enter space-y-6">
    <section class="website-center-hero">
        <div>
            <span class="website-kicker">کنترل حضور عمومی</span>
            <h1>ظاهر و محتوای قابل‌دیدن آموزشگاه را از یکجا مدیریت کن.</h1>
            <p>
                بنر صفحه اصلی، تصویرها و وضعیت حضور عمومی آموزشگاه را ببین و قبل از انتشار، نتیجه را بررسی کن.
            </p>
        </div>

        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="website-open-btn">
            مشاهده سایت ↗
        </a>
    </section>

    @forelse($academies as $academy)
        @php
            $activeBanners = $academy->homeBanners->where('is_active', true)->count();
            $publicImages = $academy->media->where('visibility', 'public')->where('status', 'active')->filter(fn ($media) => str_starts_with((string) $media->mime_type, 'image/'))->count();
        @endphp

        <section class="website-academy-card">
            <div class="website-academy-head">
                <div>
                    <span>آموزشگاه</span>
                    <h2>{{ $academy->name }}</h2>
                    <p>{{ $academy->city ?: 'موقعیت ثبت نشده' }}</p>
                </div>
                <div class="website-academy-stats">
                    <span><b>{{ $activeBanners }}</b> بنر فعال</span>
                    <span><b>{{ $publicImages }}</b> تصویر عمومی</span>
                </div>
            </div>

            <div class="website-banner-rail">
                @for($slot = 1; $slot <= 3; $slot++)
                    @php
                        $banner = $academy->homeBanners->firstWhere('slot', $slot);
                        $media = $banner?->media;
                    @endphp

                    <article class="website-banner-mini">
                        <div class="website-banner-mini-media">
                            @if($media?->url())
                                <img src="{{ $media->url() }}" alt="" loading="lazy" style="object-position: {{ $banner->crop_x ?? 50 }}% {{ $banner->crop_y ?? 50 }}%;">
                            @else
                                <div class="website-banner-empty">جایگاه {{ $slot }}</div>
                            @endif
                            <span class="website-banner-badge">{{ $banner?->is_active ? 'فعال' : 'غیرفعال' }}</span>
                        </div>
                        <div class="website-banner-mini-body">
                            <strong>{{ $banner?->title ?: 'بنر جایگاه ' . $slot }}</strong>
                            <span>{{ $media?->original_name ?: 'تصویری انتخاب نشده' }}</span>
                        </div>
                    </article>
                @endfor
            </div>

            <div class="website-academy-actions">
                <a href="{{ route('owner.academy.banners.edit', $academy) }}" class="course-primary-btn">
                    مدیریت تصاویر و بنرها
                </a>
                <a href="{{ route('owner.academy.edit', $academy) }}" class="course-secondary-btn">
                    اطلاعات آموزشگاه
                </a>
            </div>
        </section>
    @empty
        <div class="dashboard-panel p-8 text-center text-sm text-slate-500">
            هنوز آموزشگاه فعالی برای این حساب ثبت نشده است.
        </div>
    @endforelse
</div>
@endsection
