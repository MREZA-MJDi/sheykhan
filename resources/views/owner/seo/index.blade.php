@extends('layouts.owner')

@section('title', 'SEO و دیده‌شدن | شیخان')
@section('header-title', 'SEO و دیده‌شدن')

@section('content')
<div class="owner-seo-page space-y-6">
    <section class="owner-seo-hero">
        <div>
            <span class="owner-seo-kicker">رشد ارگانیک</span>
            <h1>کنترل SEO آموزشگاه، بدون پیچیدگی.</h1>
            <p>عنوان، توضیح، تصویر اشتراک‌گذاری، آدرس اصلی و داده‌های ساختاریافته را برای صفحات متعلق به خودت مدیریت کن.</p>
        </div>

        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="website-open-btn">مشاهده سایت ↗</a>
    </section>

    <section class="grid gap-3 sm:grid-cols-3">
        <article class="owner-seo-stat">
            <span>صفحات قابل مدیریت</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>آموزشگاه + دوره</small>
        </article>
        <article class="owner-seo-stat">
            <span>آماده</span>
            <strong>{{ $stats['ready'] }}</strong>
            <small>امتیاز ۸۰٪ به بالا</small>
        </article>
        <article class="owner-seo-stat warning">
            <span>نیازمند تکمیل</span>
            <strong>{{ $stats['needsWork'] }}</strong>
            <small>بهبود metadata پیشنهاد می‌شود</small>
        </article>
    </section>

    <section class="dashboard-panel owner-panel">
        <div class="owner-panel-head">
            <div>
                <h2>وضعیت صفحات</h2>
                <p>امتیاز بر اساس کامل بودن فیلدهای اصلی SEO محاسبه شده است.</p>
            </div>
        </div>

        <div class="owner-seo-list mt-4">
            @forelse($items as $item)
                <article class="owner-seo-row">
                    <div class="owner-seo-main">
                        <span class="owner-seo-type">{{ $item['type'] === 'academy' ? 'آموزشگاه' : 'دوره' }}</span>
                        <strong>{{ $item['label'] }}</strong>
                        @if($item['academy'])
                            <small>{{ $item['academy'] }}</small>
                        @endif
                    </div>

                    <div class="owner-seo-score">
                        <div class="owner-seo-score-top">
                            <span>وضعیت SEO</span>
                            <strong>{{ $item['score'] }}٪</strong>
                        </div>
                        <div class="owner-seo-track"><span style="width: {{ $item['score'] }}%"></span></div>
                    </div>

                    <div class="owner-seo-status {{ $item['status'] }}">
                        {{ match($item['status']) {
                            'ready' => 'آماده',
                            'partial' => 'ناقص',
                            default => 'تنظیم نشده',
                        } }}
                    </div>

                    <a href="{{ route('owner.seo.edit', [$item['type'], $item['id']]) }}" class="course-action-btn primary">
                        ویرایش
                    </a>
                </article>
            @empty
                <div class="owner-empty">صفحه‌ای برای مدیریت SEO پیدا نشد.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
