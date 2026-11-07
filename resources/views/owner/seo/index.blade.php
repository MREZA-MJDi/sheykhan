@extends('layouts.seo-specialist')

@section('title', 'SEO و دیده‌شدن | شیخان')
@section('header-title', 'SEO و دیده‌شدن')

@section('content')
<div class="owner-seo-page space-y-6">
    <section class="owner-seo-hero">
        <div>
            <span class="owner-seo-kicker">موتور ممیزی و کنترل ارگانیک</span>
            <h1>{{ ($isGlobal ?? false) ? 'مرکز کنترل SEO شیخان' : 'کنترل SEO آموزشگاه' }}</h1>
            <p>{{ ($isGlobal ?? false) ? 'متادیتا و داده‌های ساختاریافتهٔ صفحات عمومی و منتشرشده را از یک مرکز مدیریت کن؛ ممیزی زیر پیشنهادهای قابل اقدام را از رکوردهای واقعی استخراج می‌کند.' : 'عنوان، توضیح، تصویر اشتراک‌گذاری، آدرس اصلی و داده‌های ساختاریافته را برای صفحات عمومی مدیریت کن.' }}</p>
        </div>

        <div class="seo-console-hero-actions">
            <a href="{{ route('account.guide', ['role' => 'seo']) }}" class="course-action-btn">راهنمای پنل SEO</a>
            <a href="{{ route('owner.seo.keywords.index') }}" class="course-action-btn primary">مدیریت کلمات کلیدی و رتبه‌ها ↗</a>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="website-open-btn">مشاهده سایت ↗</a>
        </div>
    </section>

    <nav class="seo-console-switcher" aria-label="بخش‌های پنل SEO">
        <a href="{{ route('owner.seo.index') }}" class="is-active">ممیزی صفحات و متادیتا</a>
        <a href="{{ route('owner.seo.keywords.index') }}">فهرست کلمات کلیدی، خوشه‌ها و تاریخچهٔ رتبه</a>
    </nav>

    <section class="dashboard-panel owner-panel p-4 sm:p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-slate-900">ممیزی کیفیت فنی SEO</h2>
                <p class="mt-1 max-w-3xl text-xs leading-6 text-slate-600">
                    این ممیزی وضعیت متادیتای ذخیره‌شده، پیشنهادهای طول عنوان/توضیح، تنظیمات noindex و برخی داده‌های ساختاریافته را بررسی می‌کند.
                    این گزارش جای خزیدن واقعی موتور جستجو، PageSpeed، Search Console یا بررسی رتبه را نمی‌گیرد.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('seo.robots') }}" target="_blank" rel="noopener noreferrer" class="course-action-btn">بررسی robots.txt ↗</a>
                <a href="{{ route('seo.sitemap') }}" target="_blank" rel="noopener noreferrer" class="course-action-btn">بررسی sitemap.xml ↗</a>
            </div>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <span class="block text-xs text-slate-500">پیشنهاد قابل بررسی</span>
                <strong class="mt-1 block text-2xl font-black text-slate-900">{{ $stats['totalFindings'] ?? 0 }}</strong>
                <small class="mt-1 block text-xs text-slate-500">جمع هشدارها و پیشنهادهای نگارشی</small>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <span class="block text-xs text-slate-500">صفحه دارای پیشنهاد</span>
                <strong class="mt-1 block text-2xl font-black text-slate-900">{{ $stats['pagesWithFindings'] ?? 0 }}</strong>
                <small class="mt-1 block text-xs text-slate-500">بر اساس صفحات عمومی منتشرشده</small>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <span class="block text-xs text-slate-500">صفحات با متادیتای تکراری</span>
                <strong class="mt-1 block text-2xl font-black text-slate-900">{{ $stats['duplicatePages'] ?? 0 }}</strong>
                <small class="mt-1 block text-xs text-slate-500">عنوان، توضیحات یا canonical مشترک</small>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                <span class="block text-xs text-amber-800">صفحه با noindex</span>
                <strong class="mt-1 block text-2xl font-black text-amber-900">{{ $stats['noindex'] ?? 0 }}</strong>
                <small class="mt-1 block text-xs text-amber-800">بررسی کن که تصمیم عمدی باشد</small>
            </div>
        </div>
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
            <span>پوشش تنظیمات اختصاصی</span>
            <strong>{{ $stats['ready'] }}</strong>
            <small>حداقل ۸۰٪ فیلدهای قابل تنظیم پر شده‌اند؛ این عدد امتیاز رتبه‌بندی نیست</small>
        </article>
        <article class="owner-seo-stat warning">
            <span>تنظیمات ناقص</span>
            <strong>{{ $stats['needsWork'] }}</strong>
            <small>صفحات با پوشش فیلدهای اختصاصی کمتر از ۸۰٪</small>
        </article>
        <article class="owner-seo-stat">
            <span>قابل ایندکس</span>
            <strong>{{ $stats['indexable'] ?? 0 }}</strong>
            <small>صفحات منتشرشده بدون دستور noindex</small>
        </article>
    </section>

    <section class="dashboard-panel owner-panel">
        <div class="owner-panel-head">
            <div>
                <h2>وضعیت صفحات</h2>
                <p>درصد هر ردیف میزان تکمیل تنظیمات اختصاصی ذخیره‌شده است، نه پیش‌بینی رتبه در گوگل. برای پیشنهادها، هر ردیف را باز کن.</p>
                <p class="mt-2 text-xs text-slate-500">
                    آخرین ممیزی: {{ isset($scannedAt) ? \\App\\Support\\PersianUi::dateTime($scannedAt) : 'نامشخص' }}
                    @if($latestContentUpdateAt)
                        · آخرین به‌روزرسانی محتوای عمومی: {{ \\App\\Support\\PersianUi::dateTime($latestContentUpdateAt) }}
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs leading-6 text-blue-900">
            این بخش متادیتا و صفحات منتشرشدهٔ واقعی شیخان را ممیزی می‌کند. آمار بازدید، کلیک و رتبهٔ زندهٔ گوگل فقط پس از اتصال رسمی Search Console قابل نمایش است؛ تا آن زمان هیچ عدد تخمینی به‌عنوان آمار واقعی نشان داده نمی‌شود.
        </div>

        <form method="GET" action="{{ route('owner.seo.index') }}" class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[minmax(0,1.5fr)_minmax(150px,1fr)_minmax(160px,1fr)_auto_auto] md:items-end">
            <label class="grid gap-1 text-xs font-bold text-slate-700">
                جست‌وجوی صفحه
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="عنوان، آموزشگاه یا آدرس صفحه" class="ui-control px-3 py-2">
            </label>
            <label class="grid gap-1 text-xs font-bold text-slate-700">
                نوع محتوا
                <select name="type" class="ui-control px-3 py-2">
                    <option value="">همه انواع</option>
                    @foreach(['course' => 'دوره', 'content' => 'محتوای آموزشگاه', 'blog' => 'مقاله', 'product' => 'محصول', 'teacher' => 'مدرس'] as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1 text-xs font-bold text-slate-700">
                وضعیت ممیزی
                <select name="status" class="ui-control px-3 py-2">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach(['ready' => 'پوشش مناسب', 'partial' => 'ناقص', 'empty' => 'بدون تنظیم اختصاصی', 'noindex' => 'noindex', 'findings' => 'دارای پیشنهاد'] as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="course-action-btn primary">اعمال فیلتر</button>
            <a href="{{ route('owner.seo.index') }}" class="course-action-btn">پاک‌کردن</a>
        </form>

        <p class="mt-3 text-xs text-slate-500">
            {{ \\App\\Support\\PersianUi::digits($filteredTotal ?? $stats['total']) }} صفحه با فیلتر فعلی پیدا شد؛ هر بار حداکثر ۲۵ صفحه نمایش داده می‌شود.
        </p>

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
                        @if(!empty($item['issues']))
                            <details class="mt-2 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs">
                                <summary class="cursor-pointer font-bold text-amber-900">{{ count($item['issues']) }} پیشنهاد برای بررسی</summary>
                                <ul class="mt-2 space-y-2">
                                    @foreach($item['issues'] as $finding)
                                        <li class="leading-6 {{ $finding['severity'] === 'critical' ? 'text-rose-700' : ($finding['severity'] === 'warning' ? 'text-amber-800' : 'text-slate-600') }}">
                                            <strong>{{ $finding['title'] }}</strong>
                                            <span class="block">{{ $finding['detail'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
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
        @if($items->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-4">{{ $items->links() }}</div>
        @endif
    </section>
</div>
@endsection
