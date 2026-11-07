@extends($isOwner ? 'layouts.owner' : 'layouts.teacher')

@section('title', 'SpotPlayer و لایسنس‌ها | شیخان')
@section('header-title', 'SpotPlayer و لایسنس‌ها')
@section('page_title', 'SpotPlayer و لایسنس‌ها')
@section('page_subtitle', 'مدیریت اتصال و دسترسی امن ویدئوهای دوره‌ها')

@section('content')
@php
    $baseRoute = $isOwner ? 'owner.spotplayer.edit' : 'teacher.spotplayer.index';
    $tabUrl = fn (string $tab) => route($baseRoute, ['tab' => $tab]);
    $digits = fn ($value) => \App\Support\PersianUi::digits((string) $value);
    $statusLabels = [
        'active' => 'فعال',
        'revoked' => 'باطل‌شده',
        'inactive' => 'غیرفعال',
        'issuing' => 'در حال صدور',
        'failed' => 'ناموفق',
        'review_required' => 'نیازمند بررسی',
    ];
    $statusClasses = [
        'active' => 'success',
        'revoked' => 'danger',
        'inactive' => 'muted',
        'issuing' => 'info',
        'failed' => 'danger',
        'review_required' => 'warning',
    ];
    $tabs = [
        'overview' => 'نمای کلی',
        'licenses' => 'دانش‌آموزان و لایسنس‌ها',
        'courses' => 'دوره‌ها',
        'guide' => 'مراحل راه‌اندازی',
    ];
    if ($isOwner) {
        $tabs['settings'] = 'تنظیمات اتصال';
    }
@endphp

<div class="sp-page" dir="rtl">
    <section class="sp-hero">
        <div class="sp-hero-main">
            <div class="sp-brandline">
                <span class="sp-brand-mark">SP</span>
                <span>پخش امن ویدئو</span>
                <span class="sp-divider"></span>
                <span>مرکز مدیریت شیخان</span>
            </div>
            <h1>مرکز SpotPlayer</h1>
            <p>اتصال دوره‌ها، مشاهده لایسنس‌های صادرشده و پیگیری دسترسی دانش‌آموزان؛ همه در یک فضای مشخص.</p>
            <div class="sp-hero-actions">
                <a class="sp-btn sp-btn-primary" href="{{ $tabUrl('licenses') }}">مشاهده لایسنس دانش‌آموزان</a>
                <a class="sp-btn sp-btn-ghost" href="{{ $tabUrl('courses') }}">بررسی اتصال دوره‌ها</a>
            </div>
        </div>
        <div class="sp-connection-card">
            <div class="sp-connection-top">
                <span class="sp-connection-icon">◈</span>
                <span class="sp-status {{ $isConfigured ? 'is-ready' : 'is-pending' }}">
                    <i></i>{{ $isConfigured ? 'تنظیمات سرور ثبت شده' : 'اتصال هنوز کامل نیست' }}
                </span>
            </div>
            <strong>{{ $mode === 'production' ? 'Production' : 'Test' }}</strong>
            <span>{{ $mode === 'production' ? 'حالت صدور لایسنس واقعی' : 'حالت آزمایشی' }}</span>
            @if($isOwner)
                <a href="{{ $tabUrl('settings') }}">مدیریت کلید API و حالت اجرا <span>←</span></a>
            @else
                <small>کلید API و تغییر حالت اجرا فقط در اختیار مدیر آموزشگاه است.</small>
            @endif
        </div>
    </section>

    @if(session('success'))
        <div class="sp-alert sp-alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="sp-alert sp-alert-warning" role="status">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="sp-alert sp-alert-error" role="alert">
            <strong>ذخیره‌سازی کامل نشد.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <nav class="sp-tabs" aria-label="بخش‌های SpotPlayer">
        @foreach($tabs as $key => $label)
            <a href="{{ $tabUrl($key) }}" class="sp-tab {{ $tab === $key ? 'is-active' : '' }}" @if($tab === $key) aria-current="page" @endif>
                {{ $label }}
                @if($key === 'licenses' && $stats['active_licenses'])
                    <span>{{ $digits($stats['active_licenses']) }}</span>
                @endif
                @if($key === 'courses' && $stats['mapped_courses'] < $stats['courses'])
                    <span class="sp-tab-warning">{{ $digits($stats['courses'] - $stats['mapped_courses']) }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    @if($tab === 'overview')
        <section class="sp-kpis" aria-label="خلاصه وضعیت">
            <article class="sp-kpi sp-kpi-cyan">
                <span>دوره‌های قابل مدیریت</span>
                <strong>{{ $digits($stats['courses']) }}</strong>
                <small>دوره‌های {{ $isOwner ? 'آموزشگاه' : 'منتسب به شما' }}</small>
            </article>
            <article class="sp-kpi sp-kpi-green">
                <span>دوره‌های متصل</span>
                <strong>{{ $digits($stats['mapped_courses']) }}</strong>
                <small>دارای شناسه واقعی SpotPlayer</small>
            </article>
            <article class="sp-kpi sp-kpi-blue">
                <span>لایسنس فعال</span>
                <strong>{{ $digits($stats['active_licenses']) }}</strong>
                <small>ثبت‌شده در پایگاه داده شیخان</small>
            </article>
            <article class="sp-kpi {{ $stats['review_required'] ? 'sp-kpi-orange' : 'sp-kpi-green' }}">
                <span>نیازمند بررسی دستی</span>
                <strong>{{ $digits($stats['review_required']) }}</strong>
                <small>{{ $digits($stats['issuing']) }} درخواست در حال صدور</small>
            </article>
        </section>

        <div class="sp-overview-grid">
            <section class="sp-panel">
                <div class="sp-panel-heading">
                    <div><span class="sp-overline">وضعیت دوره‌ها</span><h2>اتصال ویدئوها</h2></div>
                    <a href="{{ $tabUrl('courses') }}">همه دوره‌ها ←</a>
                </div>
                @forelse($courses->take(5) as $course)
                    <div class="sp-course-row">
                        <div class="sp-course-symbol">▶</div>
                        <div class="sp-course-info">
                            <strong>{{ $course->title }}</strong>
                            <small>{{ $course->academy?->name ?: 'آموزشگاه' }} · {{ $digits($course->active_licenses_count) }} لایسنس فعال</small>
                        </div>
                        <span class="sp-status {{ filled($course->spotplayer_course_id) ? 'is-ready' : 'is-pending' }}"><i></i>{{ filled($course->spotplayer_course_id) ? 'متصل' : 'بدون شناسه' }}</span>
                    </div>
                @empty
                    <div class="sp-empty">هنوز دوره‌ای برای نمایش وجود ندارد.</div>
                @endforelse
            </section>

            <section class="sp-panel sp-guide-panel">
                <span class="sp-overline">مسیر شروع</span>
                <h2>برای پخش امن چه کار کنیم؟</h2>
                <ol class="sp-mini-steps">
                    <li><b>۱</b><span>ساخت دوره و بارگذاری ویدئو در پنل ناشر SpotPlayer</span></li>
                    <li><b>۲</b><span>ثبت شناسه همان دوره در فرم دوره شیخان</span></li>
                    <li><b>۳</b><span>بررسی مجاز بودن ثبت‌نام و فعال‌سازی لایسنس توسط دانش‌آموز</span></li>
                </ol>
                <a class="sp-btn sp-btn-primary sp-btn-wide" href="{{ $tabUrl('guide') }}">مشاهده راهنمای کامل</a>
            </section>
        </div>

        @if($stats['review_required'] > 0)
            <div class="sp-callout sp-callout-warning">
                <strong>چند درخواست نیازمند تطبیق است.</strong>
                <p>در این وضعیت، نتیجه درخواست در SpotPlayer قطعی نیست یا اطلاعات محلی کامل نشده است. پیش از هر تلاش مجدد، وضعیت همان دانش‌آموز و دوره را در پنل ناشر بررسی کنید تا لایسنس تکراری صادر نشود.</p>
                <a href="{{ $tabUrl('licenses') }}">رفتن به سوابق صدور ←</a>
            </div>
        @endif

    @elseif($tab === 'licenses')
        <section class="sp-panel">
            <div class="sp-panel-heading">
                <div><span class="sp-overline">دسترسی واقعی دانش‌آموزان</span><h2>لایسنس‌های ثبت‌شده</h2><p>هر ردیف یک اتصال لایسنس به یک دانش‌آموز و یک دوره است.</p></div>
                <span class="sp-count">{{ $digits($licenses->total()) }} رکورد</span>
            </div>
            <form class="sp-filters" method="GET" action="{{ route($baseRoute) }}">
                <input type="hidden" name="tab" value="licenses">
                <label class="sp-search"><span>⌕</span><input name="q" value="{{ $search }}" placeholder="جستجوی نام دانش‌آموز یا دوره"></label>
                <select name="license_status" aria-label="فیلتر وضعیت لایسنس">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" @selected($licenseStatus === 'active')>فعال</option>
                    <option value="revoked" @selected($licenseStatus === 'revoked')>باطل‌شده</option>
                    <option value="inactive" @selected($licenseStatus === 'inactive')>غیرفعال</option>
                </select>
                <button class="sp-btn sp-btn-secondary" type="submit">اعمال فیلتر</button>
                @if($search !== '' || filled($licenseStatus))
                    <a class="sp-reset" href="{{ $tabUrl('licenses') }}">پاک‌کردن</a>
                @endif
            </form>
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead><tr><th>دانش‌آموز</th><th>دوره</th><th>آموزشگاه</th><th>محیط صدور</th><th>تاریخ صدور</th><th>وضعیت</th></tr></thead>
                    <tbody>
                    @forelse($licenses as $license)
                        <tr>
                            <td><strong>{{ $license->student?->name ?: 'حساب حذف‌شده' }}</strong><small>شناسه کاربر: {{ $digits($license->student_id) }}</small></td>
                            <td>{{ $license->course?->title ?: 'دوره حذف‌شده' }}</td>
                            <td>{{ $license->course?->academy?->name ?: '—' }}</td>
                            <td><span class="sp-mode">{{ $license->provider_mode === 'production' ? 'واقعی' : 'آزمایشی' }}</span></td>
                            <td>{{ $license->issued_at ? \App\Support\PersianUi::dateTime($license->issued_at) : '—' }}</td>
                            <td><span class="sp-status {{ $statusClasses[$license->status] ?? 'muted' }}"><i></i>{{ $statusLabels[$license->status] ?? $license->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="sp-empty">با این فیلتر، لایسنسی پیدا نشد. پس از فعال‌سازی پخش امن توسط دانش‌آموز، سوابق اینجا دیده می‌شوند.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($licenses->hasPages())<div class="sp-pagination">{{ $licenses->links() }}</div>@endif
        </section>

        <section class="sp-panel sp-panel-spaced">
            <div class="sp-panel-heading">
                <div><span class="sp-overline">کنترل خطا و تکرار</span><h2>درخواست‌های نیازمند پیگیری</h2><p>درخواست‌های ناموفق یا نامطمئن، بدون نمایش کلید یا اطلاعات محرمانه.</p></div>
                <span class="sp-count">{{ $digits($issuances->total()) }} درخواست</span>
            </div>
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead><tr><th>دانش‌آموز</th><th>دوره</th><th>وضعیت درخواست</th><th>تعداد تلاش</th><th>آخرین به‌روزرسانی</th></tr></thead>
                    <tbody>
                    @forelse($issuances as $issuance)
                        <tr>
                            <td>{{ $issuance->student?->name ?: 'حساب حذف‌شده' }}</td>
                            <td>{{ $issuance->course?->title ?: 'دوره حذف‌شده' }}</td>
                            <td><span class="sp-status {{ $statusClasses[$issuance->status] ?? 'muted' }}"><i></i>{{ $statusLabels[$issuance->status] ?? $issuance->status }}</span></td>
                            <td>{{ $digits($issuance->attempt_count) }}</td>
                            <td>{{ $issuance->completed_at ? \App\Support\PersianUi::dateTime($issuance->completed_at) : ($issuance->started_at ? \App\Support\PersianUi::dateTime($issuance->started_at) : '—') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="sp-empty">درخواست معوق یا نیازمند بررسی وجود ندارد.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($issuances->hasPages())<div class="sp-pagination">{{ $issuances->links() }}</div>@endif
            <div class="sp-callout sp-callout-warning">
                اگر وضعیت «نیازمند بررسی» است، لایسنس را دوباره صادر نکنید. ابتدا در پنل ناشر نتیجه درخواست قبلی را تطبیق دهید؛ ممکن است SpotPlayer آن را ساخته باشد ولی پاسخ به شیخان نرسیده باشد.
            </div>
        </section>

    @elseif($tab === 'courses')
        <section class="sp-panel">
            <div class="sp-panel-heading">
                <div><span class="sp-overline">شناسه‌های سمت ناشر</span><h2>دوره‌های شیخان</h2><p>هر دوره شیخان باید به شناسه دوره متناظر در پنل ناشر SpotPlayer متصل باشد.</p></div>
                <span class="sp-count">{{ $digits($courses->total()) }} دوره</span>
            </div>
            <div class="sp-course-grid">
                @forelse($courses as $course)
                    <article class="sp-course-card">
                        <div class="sp-course-card-top">
                            <span class="sp-course-symbol">▶</span>
                            <span class="sp-status {{ filled($course->spotplayer_course_id) ? 'is-ready' : 'is-pending' }}"><i></i>{{ filled($course->spotplayer_course_id) ? 'اتصال ثبت شده' : 'نیازمند اتصال' }}</span>
                        </div>
                        <h3>{{ $course->title }}</h3>
                        <p>{{ $course->academy?->name ?: 'آموزشگاه' }}</p>
                        <div class="sp-course-metrics">
                            <span><b>{{ $digits($course->active_licenses_count) }}</b> لایسنس فعال</span>
                            <span><b>{{ $digits($course->total_licenses_count) }}</b> کل سوابق</span>
                        </div>
                        <div class="sp-provider-id">
                            <small>شناسه دوره در SpotPlayer</small>
                            @if(filled($course->spotplayer_course_id))
                                <code>{{ $course->spotplayer_course_id }}</code>
                            @else
                                <span>هنوز ثبت نشده</span>
                            @endif
                        </div>
                        <a class="sp-btn sp-btn-secondary sp-btn-wide" href="{{ route($isOwner ? 'owner.courses.edit' : 'teacher.courses.edit', $course) }}">ویرایش اتصال دوره</a>
                    </article>
                @empty
                    <div class="sp-empty">دوره‌ای در محدوده دسترسی شما وجود ندارد.</div>
                @endforelse
            </div>
            @if($courses->hasPages())<div class="sp-pagination">{{ $courses->links() }}</div>@endif
            <div class="sp-callout sp-callout-info">
                شناسه دوره با API Key یا License Key فرق دارد. اگر دوره لایسنس فعال دارد، شناسه آن را تغییر ندهید؛ سیستم برای جلوگیری از قطع دسترسی، این تغییر را مسدود می‌کند.
            </div>
        </section>

    @elseif($tab === 'guide')
        <section class="sp-panel">
            <div class="sp-panel-heading">
                <div><span class="sp-overline">راهنمای نقش‌محور</span><h2>راه‌اندازی از پنل ناشر تا پخش برای دانش‌آموز</h2><p>ترتیب مراحل مهم است؛ تنظیم ناقص یک مرحله باعث می‌شود لایسنس صادر نشود یا ویدئو باز نشود.</p></div>
            </div>
            <ol class="sp-timeline">
                <li>
                    <span class="sp-step-number">۱</span>
                    <div><h3>ساخت دوره در پنل ناشر SpotPlayer</h3><p>مدیر یا مدرس با دسترسی حساب ناشر وارد پنل SpotPlayer می‌شود، یک دوره متناظر می‌سازد و ویدئوهای همان دوره را در SpotPlayer بارگذاری می‌کند. برای پخش امن، فایل MP4 را به‌جای بارگذاری در SpotPlayer در فضای فایل عمومی قرار ندهید.</p><a href="https://panel.spotplayer.ir/" target="_blank" rel="noopener noreferrer">بازکردن پنل ناشر SpotPlayer ↗</a></div>
                </li>
                <li>
                    <span class="sp-step-number">۲</span>
                    <div><h3>ثبت شناسه دوره در شیخان</h3><p>شناسه دوره را از پنل ناشر کپی کنید. در شیخان، وارد فرم همان دوره شوید و فیلد «شناسه دوره در SpotPlayer» را پر و ذخیره کنید. این شناسه با کلید API و کلید لایسنس متفاوت است.</p><a href="{{ route($isOwner ? 'owner.courses.index' : 'teacher.courses.index') }}">رفتن به فهرست دوره‌ها ←</a></div>
                </li>
                <li>
                    <span class="sp-step-number">۳</span>
                    <div><h3>بررسی انتشار و دسترسی آموزشی دانش‌آموز</h3><p>دوره باید منتشر شده باشد و دانش‌آموز در آموزشگاه عضو فعال باشد. برای دوره رایگان، ثبت‌نام فعال لازم است؛ برای دوره پولی، پرداخت کامل و تراکنش مالی تکمیل‌شده باید در شیخان ثبت شده باشد.</p></div>
                </li>
                <li>
                    <span class="sp-step-number">۴</span>
                    <div><h3>فعال‌سازی توسط خود دانش‌آموز</h3><p>دانش‌آموز وارد صفحه دوره در پنل خود می‌شود و دکمه «فعال‌سازی پخش امن ویدئو» را می‌زند. شیخان دسترسی را دوباره کنترل می‌کند و سپس برای همان دانش‌آموز و همان دوره لایسنس می‌گیرد. لایسنس صرفاً با ثبت خرید به‌صورت خودکار ایجاد نمی‌شود؛ اولین درخواست فعال‌سازی آن را ایجاد می‌کند.</p></div>
                </li>
                <li>
                    <span class="sp-step-number">۵</span>
                    <div><h3>تست پخش و بررسی واترمارک و دستگاه</h3><p>ابتدا در حالت Test با حساب آزمایشی، بازشدن پلیر، پخش و واترمارک را بررسی کنید. مدیر پس از تأیید تست زنده، حالت Production را فعال می‌کند. تعداد دستگاه‌ها مطابق تنظیمات سمت سرور اعمال می‌شود.</p></div>
                </li>
            </ol>
            @if(! $isOwner)
                <div class="sp-callout sp-callout-info">کلید API و تغییر Test/Production محرمانه و در سطح سرور مدیریت می‌شود. مدرس فقط شناسه دوره‌های تحت تدریس خود را در فرم دوره تنظیم می‌کند؛ برای تغییر اتصال سراسری با مدیر آموزشگاه هماهنگ کنید.</div>
            @endif
        </section>

    @elseif($tab === 'settings' && $isOwner)
        <section class="sp-kpis">
            <article class="sp-kpi {{ $isConfigured ? 'sp-kpi-green' : 'sp-kpi-orange' }}">
                <span>وضعیت اعتبارنامه</span><strong>{{ $isConfigured ? 'آماده' : 'ناقص' }}</strong>
                <small>{{ $environmentKeyConfigured ? 'کلید از Environment سرور' : ($storedKeyConfigured ? 'کلید رمزگذاری‌شده در پنل' : 'کلید API ثبت نشده') }}</small>
            </article>
            <article class="sp-kpi sp-kpi-blue">
                <span>حالت مؤثر</span><strong>{{ $mode === 'production' ? 'Production' : 'Test' }}</strong>
                <small>دستگاه مجاز: {{ $digits($maxActiveDevices) }} · وب: {{ $digits($webAppDevices) }}</small>
            </article>
        </section>

        <section class="sp-panel sp-panel-spaced">
            <div class="sp-panel-heading"><div><span class="sp-overline">دسترسی مدیر</span><h2>اعتبارنامه و حالت اجرا</h2><p>کلید API فقط سمت سرور نگهداری می‌شود و دوباره در صفحه نمایش داده نمی‌شود.</p></div></div>
            <div class="sp-callout sp-callout-warning">
                ذخیره‌شدن کلید به‌تنهایی به معنی تست موفق پخش نیست. قبل از Production، یک دوره و یک حساب دانش‌آموز آزمایشی را در محیط واقعی بررسی کنید.
            </div>
            <form method="POST" action="{{ route('owner.spotplayer.update') }}" class="sp-settings-form">
                @csrf
                @method('PUT')
                <label>
                    <span>کلید API ناشر SpotPlayer</span>
                    <input type="password" name="api_key" value="" autocomplete="new-password" autocapitalize="none" spellcheck="false" maxlength="2048" dir="ltr" placeholder="{{ $isConfigured ? 'برای حفظ کلید فعلی خالی بگذار' : 'کلید API را از پنل ناشر وارد کن' }}">
                    <small>کلید در دیتابیس رمزگذاری می‌شود و در HTML، گزارش‌ها یا لاگ‌ها نمایش داده نمی‌شود. کلید Environment سرور بر مقدار ذخیره‌شده در پنل اولویت دارد.</small>
                </label>
                @if($storedKeyConfigured && ! $environmentKeyConfigured)
                    <label class="sp-checkbox">
                        <input type="checkbox" name="clear_api_key" value="1">
                        <span>حذف کلید رمزگذاری‌شده ذخیره‌شده در پنل</span>
                    </label>
                @endif
                <label>
                    <span>حالت اجرا</span>
                    <select name="mode" required>
                        <option value="test" @selected($mode === 'test')>Test — لایسنس آزمایشی</option>
                        <option value="production" @selected($mode === 'production')>Production — لایسنس واقعی</option>
                    </select>
                    @if($environmentModeOverride)
                        <small class="sp-warning-text">متغیر SPOTPLAYER_MODE در سرور تنظیم شده و بر انتخاب ذخیره‌شده در پنل اولویت دارد.</small>
                    @endif
                </label>
                <label class="sp-checkbox sp-confirm-production">
                    <input type="checkbox" name="confirm_production" value="1">
                    <span>در صورت انتخاب Production، تأیید می‌کنم تست زنده دوره و حساب دانش‌آموز انجام شده و صدور لایسنس واقعی مدنظر است.</span>
                </label>
                <button class="sp-btn sp-btn-primary" type="submit">ذخیره تنظیمات اتصال</button>
            </form>
        </section>
    @endif
</div>
@endsection
