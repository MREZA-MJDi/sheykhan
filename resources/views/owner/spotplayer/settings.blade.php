@extends('layouts.owner')

@section('title', 'مرکز مدیریت SpotPlayer | شیخان')
@section('header-title', 'مرکز مدیریت SpotPlayer')

@push('styles')

@endpush

@section('content')
@php
    $persianNumber = fn ($value) => \App\Support\PersianUi::number((int) $value);
    $reviewRequiredCount = (int) $issuanceCounts->get('review_required', 0);
    $issuingCount = (int) $issuanceCounts->get('issuing', 0);
    $failedCount = (int) $issuanceCounts->get('failed', 0);
    $courseStatusLabel = fn ($status) => match ($status) {
        'published' => 'منتشرشده',
        'archived' => 'آرشیو',
        default => 'پیش‌نویس',
    };
    $courseStatusTone = fn ($status) => match ($status) {
        'published' => 'ok',
        'archived' => 'bad',
        default => 'warn',
    };
    $licenseStatusLabel = fn ($status) => match ($status) {
        'active' => 'فعال',
        'revoked' => 'باطل‌شده',
        default => $status ?: 'نامشخص',
    };
    $licenseStatusTone = fn ($status) => $status === 'active' ? 'ok' : 'warn';
    $issuanceStatusLabel = fn ($status) => match ($status) {
        'issuing' => 'در حال صدور',
        'failed' => 'ردشده توسط ناشر',
        'review_required' => 'نیازمند تطبیق دستی',
        'active' => 'تکمیل‌شده',
        default => $status ?: 'نامشخص',
    };
    $issuanceStatusTone = fn ($status) => match ($status) {
        'failed' => 'bad',
        'review_required' => 'warn',
        'issuing' => 'warn',
        default => 'ok',
    };
@endphp

<div class="sp-workspace" dir="rtl">
    <header class="sp-hero">
        <div>
            <span class="sp-eyebrow">مرکز مدیریت پخش ویدئو</span>
            <h1>SpotPlayer در شیخان</h1>
            <p>اتصال ناشر، نگاشت دوره‌ها و وضعیت واقعی لایسنس‌های دانش‌آموزان را از یک صفحه ببین. این صفحه از داده‌های ثبت‌شده در شیخان استفاده می‌کند؛ کلیدها و اطلاعات محرمانه هرگز در فهرست‌ها نمایش داده نمی‌شوند.</p>
        </div>
        <div class="sp-hero-actions">
            <span class="sp-pill {{ $isConfigured ? 'ok' : 'warn' }}"><i class="sp-dot"></i>{{ $isConfigured ? 'تنظیمات سرور آماده است' : 'اتصال نیازمند تنظیم است' }}</span>
            <span class="sp-pill">{{ $mode === 'production' ? 'Production' : 'Test' }}</span>
            <a class="sp-button" href="https://panel.spotplayer.ir/" target="_blank" rel="noopener noreferrer">پنل ناشر SpotPlayer ↗</a>
        </div>
    </header>

    @if(session('success'))
        <div role="status" class="sp-flash success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="sp-flash error">
            <strong>تنظیمات ذخیره نشد.</strong>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <nav class="sp-nav" aria-label="بخش‌های مدیریت SpotPlayer">
        <a href="#sp-overview">نمای کلی</a>
        <a href="#sp-courses">اتصال دوره‌ها</a>
        <a href="#sp-licenses">لایسنس‌های صادرشده</a>
        <a href="#sp-activity">موارد نیازمند بررسی</a>
        <a href="#sp-setup">تنظیمات اتصال</a>
        <a href="#sp-guide">راهنمای راه‌اندازی</a>
    </nav>

    <section id="sp-overview" aria-label="وضعیت اتصال و آمار">
        <div class="sp-kpis">
            <article class="sp-kpi cyan">
                <div class="sp-kpi-label">دوره‌های آموزشگاه</div>
                <div class="sp-kpi-value">{{ $persianNumber($courseStats['total']) }}</div>
                <div class="sp-kpi-hint">دوره‌های قابل مدیریت در آموزشگاه‌های مجاز</div>
            </article>
            <article class="sp-kpi green">
                <div class="sp-kpi-label">دوره‌های متصل</div>
                <div class="sp-kpi-value">{{ $persianNumber($courseStats['mapped']) }}</div>
                <div class="sp-kpi-hint">{{ $persianNumber($courseStats['unmapped']) }} دوره هنوز شناسهٔ SpotPlayer ندارد</div>
            </article>
            <article class="sp-kpi cyan">
                <div class="sp-kpi-label">لایسنس‌های ثبت‌شده</div>
                <div class="sp-kpi-value">{{ $persianNumber($licenseStats['total']) }}</div>
                <div class="sp-kpi-hint">{{ $persianNumber($licenseStats['active']) }} لایسنس با وضعیت فعال در شیخان</div>
            </article>
            <article class="sp-kpi {{ $reviewRequiredCount ? 'pink' : 'orange' }}">
                <div class="sp-kpi-label">نیازمند تطبیق با ناشر</div>
                <div class="sp-kpi-value">{{ $persianNumber($reviewRequiredCount + $issuingCount) }}</div>
                <div class="sp-kpi-hint">{{ $persianNumber($failedCount) }} درخواست با رد قطعی ناشر</div>
            </article>
        </div>

        <div class="sp-grid">
            <article class="sp-card">
                <div class="sp-card-head">
                    <div><h2>وضعیت پیکربندی سرور</h2><p>آماده‌بودن تنظیمات با تأیید پخش واقعی متفاوت است.</p></div>
                    <span class="sp-status {{ $isConfigured ? 'ok' : 'warn' }}">{{ $isConfigured ? 'آماده' : 'ناقص' }}</span>
                </div>
                <div class="sp-card-body">
                    <div class="sp-mini-grid">
                        <div class="sp-mini"><span>منبع API Key</span><strong>{{ $environmentKeyConfigured ? 'Environment سرور' : ($storedKeyConfigured ? 'ذخیرهٔ رمزگذاری‌شده' : 'ثبت نشده') }}</strong></div>
                        <div class="sp-mini"><span>حالت مؤثر</span><strong>{{ $mode === 'production' ? 'Production · لایسنس واقعی' : 'Test · لایسنس آزمایشی' }}</strong></div>
                        <div class="sp-mini"><span>حداکثر دستگاه مجاز</span><strong>{{ $persianNumber($maxActiveDevices) }} دستگاه</strong></div>
                        <div class="sp-mini"><span>دستگاه وب مجاز</span><strong>{{ $persianNumber($webAppDevices) }} دستگاه</strong></div>
                    </div>
                    @if($environmentModeOverride)
                        <div class="sp-callout mt-3"><div><strong>حالت از Environment کنترل می‌شود</strong><p>متغیر <code>SPOTPLAYER_MODE</code> در سرور بر انتخاب ذخیره‌شده در فرم اولویت دارد.</p></div></div>
                    @endif
                    <div class="sp-callout info mt-3">
                        <div><strong>پخش زنده هنوز از این صفحه قابل تأیید نیست</strong><p>تا زمانی که ویدئوی آزمایشی با حساب واقعی ناشر و حساب دانش‌آموز پخش نشود، این وضعیت فقط آمادگی تنظیمات را نشان می‌دهد.</p></div>
                    </div>
                </div>
            </article>

            <article class="sp-card">
                <div class="sp-card-head">
                    <div><h2>تفکیک لایسنس‌ها بر اساس محیط</h2><p>بر اساس فیلد <code>provider_mode</code> در پایگاه دادهٔ شیخان.</p></div>
                </div>
                <div class="sp-card-body">
                    <div class="sp-mini-grid">
                        <div class="sp-mini"><span>لایسنس‌های Test</span><strong>{{ $persianNumber($licenseStats['test']) }}</strong></div>
                        <div class="sp-mini"><span>لایسنس‌های Production</span><strong>{{ $persianNumber($licenseStats['production']) }}</strong></div>
                        <div class="sp-mini"><span>درخواست در حال صدور</span><strong>{{ $persianNumber($issuingCount) }}</strong></div>
                        <div class="sp-mini"><span>درخواست‌های ناموفق قطعی</span><strong>{{ $persianNumber($failedCount) }}</strong></div>
                    </div>
                    <p class="sp-help mt-3">این آمار از رکوردهای داخلی شیخان خوانده می‌شود؛ شمارندهٔ آنلاین دستگاه‌ها یا فهرست زندهٔ پنل ناشر نیست.</p>
                </div>
            </article>
        </div>

        @if($reviewRequiredCount > 0)
            <div class="sp-callout sp-danger-note mt-4">
                <div><strong>{{ $persianNumber($reviewRequiredCount) }} وضعیت لایسنس نیازمند تطبیق دستی است</strong><p>در این وضعیت‌ها ممکن است ناشر لایسنس را ساخته باشد ولی پاسخ شبکه یا ثبت داخلی قطعی نباشد. قبل از هر تلاش مجدد، شناسه و وضعیت را در پنل ناشر بررسی کن؛ صدور دوبارهٔ خودکار می‌تواند لایسنس تکراری بسازد.</p></div>
            </div>
        @endif
    </section>

    <section id="sp-courses" class="sp-card sp-section">
        <div class="sp-card-head">
            <div><h2>دوره‌ها و نگاشت به SpotPlayer</h2><p>برای هر دورهٔ شیخان، شناسهٔ متناظر ناشر باید ثبت شده باشد. ویدئوها از پنل ناشر بارگذاری می‌شوند.</p></div>
            <a class="sp-button soft" href="{{ route('owner.courses.index') }}">مدیریت همهٔ دوره‌ها</a>
        </div>
        @if($spotPlayerCourses->count())
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead><tr><th>دوره</th><th>آموزشگاه</th><th>وضعیت</th><th>شناسهٔ ناشر</th><th>لایسنس‌های ثبت‌شده</th><th>عملیات</th></tr></thead>
                    <tbody>
                    @foreach($spotPlayerCourses as $course)
                        <tr>
                            <td><span class="sp-primary-text">{{ $course->title }}</span><span class="sp-secondary-text">به‌روزرسانی {{ \App\Support\PersianUi::date($course->updated_at) }}</span></td>
                            <td>{{ $course->academy?->name ?: '—' }}</td>
                            <td><span class="sp-status {{ $courseStatusTone($course->status) }}">{{ $courseStatusLabel($course->status) }}</span></td>
                            <td>
                                @if(filled($course->spotplayer_course_id))
                                    <code class="sp-code">{{ $course->spotplayer_course_id }}</code>
                                @else
                                    <span class="sp-status warn">شناسه ثبت نشده</span>
                                @endif
                            </td>
                            <td>{{ $persianNumber($course->spotplayer_licenses_count) }}</td>
                            <td><a class="sp-button sm" href="{{ route('owner.courses.edit', $course) }}">تنظیم شناسهٔ دوره</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($spotPlayerCourses->hasPages())
                <div class="sp-pagination">{{ $spotPlayerCourses->onEachSide(1)->links('components.navigation.pagination') }}</div>
            @endif
        @else
            <div class="sp-empty">هنوز دوره‌ای برای این آموزشگاه ثبت نشده است. ابتدا یک دوره بساز و سپس شناسهٔ SpotPlayer آن را تنظیم کن.</div>
        @endif
    </section>

    <section id="sp-licenses" class="sp-card sp-section">
        <div class="sp-card-head">
            <div><h2>لایسنس‌های صادرشده در شیخان</h2><p>فقط داده‌های داخلی ذخیره‌شده نمایش داده می‌شود؛ کلید لایسنس در این صفحه عمداً مخفی است.</p></div>
            <span class="sp-pill">{{ $persianNumber($licenseStats['total']) }} رکورد</span>
        </div>
        @if($licenseRecords->isNotEmpty())
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead><tr><th>دانش‌آموز</th><th>دوره</th><th>شناسهٔ ناشر</th><th>محیط صدور</th><th>تاریخ صدور</th><th>وضعیت</th></tr></thead>
                    <tbody>
                    @foreach($licenseRecords as $license)
                        <tr>
                            <td><span class="sp-primary-text">{{ $license->student?->name ?: 'حساب حذف‌شده' }}</span><span class="sp-secondary-text">شناسه داخلی {{ $persianNumber($license->student_id) }}</span></td>
                            <td>{{ $license->course?->title ?: 'دوره حذف‌شده' }}</td>
                            <td><code class="sp-code">{{ $license->provider_course_id }}</code></td>
                            <td><span class="sp-status {{ $license->provider_mode === 'production' ? 'ok' : 'warn' }}">{{ $license->provider_mode === 'production' ? 'Production' : 'Test' }}</span></td>
                            <td>{{ $license->issued_at ? \App\Support\PersianUi::dateTime($license->issued_at) : '—' }}</td>
                            <td><span class="sp-status {{ $licenseStatusTone($license->status) }}">{{ $licenseStatusLabel($license->status) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sp-empty">هنوز لایسنسی در شیخان ثبت نشده است. پس از ثبت خرید/دسترسی و فعال‌سازی پخش امن توسط دانش‌آموز، رکورد واقعی اینجا نمایش داده می‌شود.</div>
        @endif
    </section>

    <section id="sp-activity" class="sp-card sp-section">
        <div class="sp-card-head">
            <div><h2>درخواست‌های نیازمند بررسی</h2><p>وضعیت‌هایی که از روی پاسخ ناشر یا قطعیت ذخیره‌سازی باید پیگیری شوند.</p></div>
            <span class="sp-pill {{ $reviewRequiredCount ? 'warn' : 'ok' }}">{{ $persianNumber($reviewRequiredCount) }} نیازمند تطبیق</span>
        </div>
        @if($issuanceRecords->isNotEmpty())
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead><tr><th>دانش‌آموز</th><th>دوره</th><th>وضعیت</th><th>تلاش</th><th>آخرین بررسی</th><th>توضیح</th></tr></thead>
                    <tbody>
                    @foreach($issuanceRecords as $issuance)
                        <tr>
                            <td>{{ $issuance->student?->name ?: 'حساب حذف‌شده' }}</td>
                            <td>{{ $issuance->course?->title ?: 'دوره حذف‌شده' }}</td>
                            <td><span class="sp-status {{ $issuanceStatusTone($issuance->status) }}">{{ $issuanceStatusLabel($issuance->status) }}</span></td>
                            <td>{{ $persianNumber($issuance->attempt_count) }}</td>
                            <td>{{ $issuance->updated_at ? \App\Support\PersianUi::dateTime($issuance->updated_at) : '—' }}</td>
                            <td><span class="sp-secondary-text">{{ $issuance->last_error ?: 'برای ادامه، وضعیت پنل ناشر را بررسی کن.' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sp-empty">در حال حاضر درخواست معلق یا نیازمند تطبیق ثبت نشده است.</div>
        @endif
        <div class="sp-card-body">
            <div class="sp-callout">
                <div><strong>چرا این ردیف‌ها دکمهٔ «صدور مجدد» ندارند؟</strong><p>در سرویس فعلی، API و گردش‌کار مطمئن برای استعلام، ابطال یا صدور مجدد از سمت مدیر پیاده نشده است. بنابراین این صفحه وضعیت را نشان می‌دهد و از ارسال درخواست تکراریِ پرریسک جلوگیری می‌کند. تطبیق و ابطال باید از پنل ناشر انجام شود.</p></div>
            </div>
        </div>
    </section>

    <section id="sp-setup" class="sp-grid sp-section">
        <article class="sp-card">
            <div class="sp-card-head">
                <div><h2>اعتبارنامه و حالت اجرا</h2><p>API Key فقط سمت سرور مصرف و در پایگاه داده رمزگذاری می‌شود.</p></div>
            </div>
            <div class="sp-card-body">
                <form method="POST" action="{{ route('owner.spotplayer.update') }}" class="sp-form">
                    @csrf
                    @method('PUT')
                    <div class="sp-field">
                        <label for="sp-api-key">کلید API ناشر SpotPlayer</label>
                        <input id="sp-api-key" type="password" name="api_key" value="" autocomplete="new-password" autocapitalize="none" spellcheck="false" maxlength="2048" dir="ltr" class="sp-input" placeholder="{{ $isConfigured ? 'برای نگه‌داشتن کلید فعلی خالی بگذار' : 'کلید API را از پنل ناشر وارد کن' }}">
                        <p class="sp-help">مقدار قبلی هیچ‌وقت دوباره در فرم قرار نمی‌گیرد. کلید را داخل سورس، توضیحات دوره یا پیام به مدرس ننویس.</p>
                    </div>

                    @if($storedKeyConfigured && ! $environmentKeyConfigured)
                        <label class="sp-check"><input type="checkbox" name="clear_api_key" value="1"><span>کلید ذخیره‌شدهٔ رمزگذاری‌شده از پنل حذف شود.</span></label>
                    @endif

                    <div class="sp-field">
                        <label for="sp-mode">حالت اجرا</label>
                        <select id="sp-mode" name="mode" required class="sp-select">
                            <option value="test" @selected($mode === 'test')>Test · صدور لایسنس آزمایشی</option>
                            <option value="production" @selected($mode === 'production')>Production · صدور لایسنس واقعی</option>
                        </select>
                        @if($environmentModeOverride)
                            <p class="sp-help">حالت مؤثر از متغیر سرور می‌آید؛ تغییر این فیلد تا زمان حذف override اثر اجرایی ندارد.</p>
                        @endif
                    </div>

                    <label class="sp-check"><input type="checkbox" name="confirm_production" value="1"><span>در صورت انتخاب Production تأیید می‌کنم حساب ناشر و پخش ویدئوی آزمایشی با حساب دانش‌آموز بررسی شده است.</span></label>
                    <button type="submit" class="sp-button primary">ذخیرهٔ تنظیمات اتصال</button>
                </form>
            </div>
        </article>

        <article class="sp-card">
            <div class="sp-card-head"><div><h2>حدود واقعی اتصال فعلی</h2><p>مقادیر خوانده‌شده از تنظیمات سرور؛ بدون تغییر نمایشی در مرورگر.</p></div></div>
            <div class="sp-card-body">
                <div class="sp-mini-grid">
                    <div class="sp-mini"><span>محدودیت کل دستگاه‌ها</span><strong>{{ $persianNumber($maxActiveDevices) }}</strong></div>
                    <div class="sp-mini"><span>دستگاه وب</span><strong>{{ $persianNumber($webAppDevices) }}</strong></div>
                    <div class="sp-mini"><span>کلید API</span><strong>{{ $environmentKeyConfigured ? 'از Environment' : ($storedKeyConfigured ? 'رمزگذاری‌شده' : 'وجود ندارد') }}</strong></div>
                    <div class="sp-mini"><span>حالت مؤثر</span><strong>{{ $mode === 'production' ? 'Production' : 'Test' }}</strong></div>
                </div>
                <div class="sp-callout info mt-3"><div><strong>تست واقعی را با دانش‌آموز آزمایشی انجام بده</strong><p>پس از تکمیل اتصال، از خود حساب دانش‌آموز وارد دوره شو و گزینهٔ فعال‌سازی پخش امن را اجرا کن. بازشدن پلیر و پخش واقعی را جداگانه بررسی کن.</p></div></div>
            </div>
        </article>
    </section>

    <section id="sp-guide" class="sp-card sp-section">
        <div class="sp-card-head"><div><h2>راهنمای راه‌اندازی و تحویل</h2><p>ترتیب کار روشن است؛ هر شناسه یا کلید فقط در جای خودش استفاده می‌شود.</p></div></div>
        <div class="sp-card-body">
            <ol class="sp-guide">
                <li><div><strong>کلید API را از پنل ناشر دریافت کن</strong><p>با حساب ناشر وارد پنل شو، کلید API را بردار و فقط در فرم امن بالا ثبت کن. این کلید با شناسهٔ دوره و License Key فرق دارد.</p><a class="sp-button mt-2" href="https://panel.spotplayer.ir/" target="_blank" rel="noopener noreferrer">بازکردن پنل ناشر ↗</a></div></li>
                <li><div><strong>ویدئوها را در دورهٔ ناشر بارگذاری کن</strong><p>فایل‌های MP4/WebM را در پنل SpotPlayer بارگذاری کن؛ فرم فایل‌های همراه شیخان برای PDF، تصویر و ZIP است.</p></div></li>
                <li><div><strong>شناسهٔ خارجی را به دورهٔ متناظر شیخان وصل کن</strong><p>از فهرست دوره‌ها وارد ویرایش دوره شو و شناسهٔ واقعی SpotPlayer را در فیلد اتصال قرار بده. شناسهٔ داخلی شیخان را اینجا وارد نکن.</p></div></li>
                <li><div><strong>با حساب دانش‌آموز آزمایشی لایسنس بگیر</strong><p>دانش‌آموز واجد دسترسی، از صفحهٔ دوره دکمهٔ «فعال‌سازی پخش امن ویدئو» را می‌زند. ثبت رکورد لایسنس در شیخان به معنی تست کامل پخش نیست.</p></div></li>
                <li><div><strong>پخش واقعی و محدودیت‌ها را بررسی کن؛ سپس Production</strong><p>پلیر وب، واترمارک، دسترسی و تعداد دستگاه را با حساب ناشر آزمایش کن. فقط بعد از تأیید واقعی و فعال‌بودن مجوز، حالت Production را انتخاب کن.</p></div></li>
            </ol>
            <div class="sp-callout mt-4"><div><strong>حفاظت محتوا</strong><p>SpotPlayer برای ویدئوی ضبط‌شده است؛ جلسهٔ زنده از بخش کلاس آنلاین مدیریت می‌شود. واترمارک و لایسنس ریسک اشتراک‌گذاری را کاهش می‌دهند، اما ضبط صفحه با دستگاه دیگر را صددرصد ناممکن نمی‌کنند.</p></div></div>
        </div>
    </section>
</div>
@endsection
