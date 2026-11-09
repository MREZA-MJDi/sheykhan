@extends('layouts.seo-specialist')

@section('title', 'مرکز مدیریت SEO | شیخان')
@section('header-title', 'مرکز مدیریت SEO')

@section('content')
@php
    $typeLabels = [
        'course' => 'دوره',
        'content' => 'محتوای آموزشگاه',
        'blog' => 'مقاله مجله',
        'product' => 'محصول',
        'teacher' => 'صفحه مدرس',
    ];
    $statusLabels = [
        '' => 'همه وضعیت‌ها',
        'attention' => 'نیازمند اقدام',
        'ready' => 'تنظیمات کامل',
        'unconfigured' => 'تنظیم‌نشده',
        'noindex' => 'خارج از نتایج جست‌وجو',
    ];
@endphp

<div class="owner-seo-page seo-workspace">
    <header class="owner-seo-hero">
        <div class="seo-hero-copy">
            <span class="owner-seo-kicker">مرکز کنترل صفحات عمومی</span>
            <h1>مدیریت دیده‌شدن شیخان در جست‌وجو</h1>
            <p>
                تنظیمات صفحات منتشرشدهٔ دوره‌ها، مقاله‌های مجله، محتوای آموزشگاه، محصولات و مدرس‌های عمومی را از یک جا بررسی کن.
                هر تغییر اینجا روی خروجی واقعی صفحه اثر می‌گذارد.
            </p>
        </div>
        <div class="seo-console-hero-actions">
            <a href="{{ route('owner.seo.keywords.index') }}" class="course-action-btn primary">برنامهٔ کلمات کلیدی</a>
            <a href="{{ route('seo.sitemap') }}" target="_blank" rel="noopener noreferrer" class="course-action-btn">نقشهٔ سایت</a>
            <a href="{{ route('seo.robots') }}" target="_blank" rel="noopener noreferrer" class="course-action-btn">robots.txt</a>
        </div>
    </header>

    <nav class="seo-console-switcher" aria-label="بخش‌های مرکز SEO">
        <a href="{{ route('owner.seo.index') }}" class="is-active" aria-current="page">صفحات و متادیتا</a>
        <a href="{{ route('owner.seo.keywords.index') }}">کلمات کلیدی و رتبه‌ها</a>
    </nav>

    <section class="seo-overview-stats" aria-label="خلاصه وضعیت صفحات">
        <article class="seo-overview-stat">
            <span>صفحات عمومی منتشرشده</span>
            <strong>{{ number_format($stats['total'] ?? 0) }}</strong>
            <small>از محتوای واقعی و قابل‌نمایش سایت</small>
        </article>
        <article class="seo-overview-stat">
            <span>نیازمند اقدام</span>
            <strong>{{ number_format($stats['pagesWithFindings'] ?? 0) }}</strong>
            <small>{{ number_format($stats['totalFindings'] ?? 0) }} نکته برای بازبینی</small>
        </article>
        <article class="seo-overview-stat">
            <span>دارای تنظیمات اختصاصی</span>
            <strong>{{ number_format($stats['configured'] ?? 0) }}</strong>
            <small>حداقل یکی از فیلدهای SEO ثبت شده</small>
        </article>
        <article class="seo-overview-stat is-warning">
            <span>دارای دستور noindex</span>
            <strong>{{ number_format($stats['noindex'] ?? 0) }}</strong>
            <small>بررسی کن خروج از نتایج عمدی باشد</small>
        </article>
    </section>

    <aside class="seo-honesty-note" role="note">
        <strong>نکته دربارهٔ اعداد:</strong>
        درصد هر صفحه فقط میزان تکمیل فیلدهای متادیتا است؛ رتبهٔ گوگل یا امتیاز موتور جست‌وجو نیست.
        دادهٔ کلیک، نمایش و جایگاه فقط پس از اتصال منبع واقعی مثل Search Console قابل گزارش است.
    </aside>

    <section class="dashboard-panel owner-panel seo-page-list">
        <div class="owner-panel-head">
            <div>
                <h2>صفحات سایت</h2>
                <p>صفحه را پیدا کن، دلیل هشدار را بخوان و همان‌جا تنظیماتش را اصلاح کن.</p>
            </div>
            <span class="seo-result-count">{{ number_format($items->total()) }} نتیجه</span>
        </div>

        <form method="GET" action="{{ route('owner.seo.index') }}" class="seo-page-filters">
            <label class="seo-filter-search">
                <span>جست‌وجوی عنوان یا نشانی</span>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="نام دوره، مقاله، محصول یا URL..." autocomplete="off">
            </label>
            <label>
                <span>نوع صفحه</span>
                <select name="type">
                    <option value="">همه انواع</option>
                    @foreach($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>وضعیت</span>
                <select name="status">
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="seo-filter-actions">
                <button type="submit" class="course-action-btn primary">اعمال فیلتر</button>
                <a href="{{ route('owner.seo.index') }}" class="course-action-btn">پاک‌کردن</a>
            </div>
        </form>

        <div class="seo-filter-chips" aria-label="خلاصه وضعیت‌ها">
            <a href="{{ route('owner.seo.index') }}" class="{{ $filters['status'] === '' && $filters['type'] === '' && $filters['q'] === '' ? 'is-active' : '' }}">همه <span>{{ number_format($counts['all']) }}</span></a>
            <a href="{{ route('owner.seo.index', ['status' => 'attention', 'type' => $filters['type'], 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'attention' ? 'is-active' : '' }}">نیازمند اقدام <span>{{ number_format($counts['attention']) }}</span></a>
            <a href="{{ route('owner.seo.index', ['status' => 'ready', 'type' => $filters['type'], 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'ready' ? 'is-active' : '' }}">تنظیمات کامل <span>{{ number_format($counts['ready']) }}</span></a>
            <a href="{{ route('owner.seo.index', ['status' => 'unconfigured', 'type' => $filters['type'], 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'unconfigured' ? 'is-active' : '' }}">تنظیم‌نشده <span>{{ number_format($counts['unconfigured']) }}</span></a>
            <a href="{{ route('owner.seo.index', ['status' => 'noindex', 'type' => $filters['type'], 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'noindex' ? 'is-active' : '' }}">noindex <span>{{ number_format($counts['noindex']) }}</span></a>
        </div>

        <div class="seo-record-list">
            @forelse($items as $item)
                <article class="seo-record">
                    <div class="seo-record-main">
                        <div class="seo-record-eyebrow">
                            <span class="seo-type">{{ $typeLabels[$item['type']] ?? 'صفحه عمومی' }}</span>
                            @if($item['academy'])
                                <span class="seo-record-academy">{{ $item['academy'] }}</span>
                            @endif
                        </div>
                        <h3>{{ $item['label'] }}</h3>
                        @if(filled($item['url']))
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="seo-record-url" dir="ltr">{{ $item['url'] }} ↗</a>
                        @endif
                        @if(!empty($item['issues']))
                            <details class="seo-record-findings">
                                <summary>
                                    <span>{{ number_format(count($item['issues'])) }} پیشنهاد برای بررسی</span>
                                    <span>نمایش جزئیات</span>
                                </summary>
                                <ul>
                                    @foreach($item['issues'] as $finding)
                                        <li class="is-{{ $finding['severity'] }}">
                                            <strong>{{ $finding['title'] }}</strong>
                                            <p>{{ $finding['detail'] }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            <p class="seo-record-clean">هشدار محتوایی از ممیزی فعلی ثبت نشده است.</p>
                        @endif
                    </div>

                    <div class="seo-record-progress">
                        <div class="seo-progress-heading">
                            <span>تکمیل تنظیمات</span>
                            <strong>{{ $item['score'] }}٪</strong>
                        </div>
                        <div class="seo-progress-track" role="progressbar" aria-label="درصد تکمیل تنظیمات SEO" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $item['score'] }}">
                            <span style="width: {{ $item['score'] }}%"></span>
                        </div>
                        <small>فیلدهای متادیتا و اشتراک‌گذاری</small>
                    </div>

                    <div class="seo-record-status">
                        @if(str_starts_with($item['robots'] ?? 'index,follow', 'noindex'))
                            <span class="seo-status-pill is-danger">noindex</span>
                            <small>درخواست عدم نمایش در نتایج</small>
                        @elseif($item['status'] === 'ready')
                            <span class="seo-status-pill is-success">تنظیمات کامل</span>
                            <small>فیلدهای اختصاصی تکمیل شده‌اند</small>
                        @elseif($item['status'] === 'partial')
                            <span class="seo-status-pill is-warning">نیازمند تکمیل</span>
                            <small>بخشی از فیلدها ثبت شده است</small>
                        @else
                            <span class="seo-status-pill is-neutral">تنظیم‌نشده</span>
                            <small>از مقدار پیش‌فرض صفحه استفاده می‌شود</small>
                        @endif
                    </div>

                    <div class="seo-record-action">
                        <a href="{{ route('owner.seo.edit', [$item['type'], $item['id']]) }}" class="course-action-btn primary">مدیریت صفحه</a>
                    </div>
                </article>
            @empty
                <div class="seo-empty-state">
                    <span class="seo-empty-icon" aria-hidden="true">⌕</span>
                    <strong>صفحه‌ای با این فیلتر پیدا نشد</strong>
                    <p>فیلترها را پاک کن یا عبارت جست‌وجو را کوتاه‌تر بنویس.</p>
                    <a href="{{ route('owner.seo.index') }}" class="course-action-btn">نمایش همه صفحات</a>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="seo-pagination">{{ $items->links() }}</div>
        @endif
    </section>
</div>
@endsection
