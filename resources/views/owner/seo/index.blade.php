@extends('layouts.owner')

@section('title', 'SEO و دیده‌شدن | شیخان')
@section('header-title', 'SEO و دیده‌شدن')

@section('content')
<div class="owner-seo-page space-y-6">
    <section class="owner-seo-hero">
        <div>
            <span class="owner-seo-kicker">رشد ارگانیک</span>
            <h1>کنترل SEO آموزشگاه، بدون پیچیدگی.</h1>
            <p>{{ ($isGlobal ?? false) ? 'صفحات عمومی و منتشرشده سایت را از یک محل بررسی کن؛ دسترسی این نقش فقط به تنظیمات SEO محدود است.' : 'عنوان، توضیح، تصویر اشتراک‌گذاری، آدرس اصلی و داده‌های ساختاریافته را برای صفحات متعلق به خودت مدیریت کن.' }}</p>
        </div>

        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="website-open-btn">مشاهده سایت ↗</a>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="owner-seo-stat">
            <span>صفحات واقعی در فهرست</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>{{ ($isGlobal ?? false) ? 'صفحات عمومی منتشرشده از دیتابیس' : 'صفحات متعلق به آموزشگاه شما' }}</small>
        </article>
        <article class="owner-seo-stat">
            <span>دارای تنظیمات اختصاصی</span>
            <strong>{{ $stats['configured'] }}</strong>
            <small>حداقل یکی از فیلدهای SEO ذخیره شده</small>
        </article>
        <article class="owner-seo-stat">
            <span>آماده از نظر تکمیل فیلدها</span>
            <strong>{{ $stats['ready'] }}</strong>
            <small>امتیاز تکمیل metadata حداقل ۸۰٪</small>
        </article>
        <article class="owner-seo-stat warning">
            <span>نیازمند تکمیل</span>
            <strong>{{ $stats['needsWork'] }}</strong>
            <small>فیلدهای SEO بیشتری لازم است</small>
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
                        <span class="owner-seo-type">{{ match($item['type']) { 'academy' => 'آموزشگاه', 'course' => 'دوره', 'content' => 'محتوای آموزشگاه', 'blog' => 'مقاله', 'product' => 'محصول', 'teacher' => 'مدرس عمومی', default => 'صفحه' } }}</span>
                        <strong>{{ $item['label'] }}</strong>
                        @if(filled($item['url'] ?? null))
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-sky-700 underline underline-offset-2">نمایش صفحه عمومی ↗</a>
                        @endif
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
                        @if(str_starts_with($item['robots'] ?? 'index,follow', 'noindex'))
                            <small class="block mt-1">noindex — خارج از فهرست نتایج</small>
                        @endif
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
