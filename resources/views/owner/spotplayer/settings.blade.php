@extends('layouts.owner')

@section('title', 'ویدئوهای آموزشی | شیخان')
@section('header-title', 'ویدئوهای آموزشی')

@push('styles')
<style>
.sp-workspace{--sp-bg:#090d28;--sp-bg2:#0e1336;--sp-card:#131a47;--sp-card2:#1b2460;--sp-line:rgba(255,255,255,.09);--sp-text:#e9ecff;--sp-muted:#98a1d6;--sp-cyan:#22d3ee;--sp-pink:#ff3d81;--sp-orange:#ffa62b;--sp-green:#2ee6a6;color:var(--sp-text);background:radial-gradient(880px 420px at 94% -12%,rgba(34,211,238,.17),transparent 70%),radial-gradient(760px 360px at 0 108%,rgba(120,80,255,.16),transparent 68%),var(--sp-bg);border:1px solid rgba(114,139,255,.12);border-radius:24px;padding:clamp(16px,2.5vw,30px);box-shadow:0 24px 70px rgba(8,14,44,.15)}
.sp-workspace *{box-sizing:border-box}
.sp-workspace a{color:inherit;text-decoration:none}
.sp-workspace button,.sp-workspace input,.sp-workspace select{font:inherit}
.sp-hero{display:flex;justify-content:space-between;align-items:flex-start;gap:22px;flex-wrap:wrap;padding:5px 2px 22px}
.sp-hero h1{margin:5px 0 8px;color:#fff;font-size:clamp(24px,3vw,34px);font-weight:950;line-height:1.5;letter-spacing:-.035em}
.sp-hero p{max-width:760px;margin:0;color:var(--sp-muted);font-size:13px;line-height:2.1}
.sp-eyebrow{color:var(--sp-cyan);font-size:10px;font-weight:900;letter-spacing:.06em}
.sp-hero-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.sp-pill{display:inline-flex;align-items:center;gap:8px;padding:7px 12px;border:1px solid var(--sp-line);border-radius:99px;background:rgba(255,255,255,.055);color:var(--sp-muted);font-size:11px;font-weight:800;white-space:nowrap}
.sp-pill.ok{border-color:rgba(46,230,166,.22);background:rgba(46,230,166,.10);color:var(--sp-green)}
.sp-pill.warn{border-color:rgba(255,166,43,.25);background:rgba(255,166,43,.11);color:var(--sp-orange)}
.sp-pill.bad{border-color:rgba(255,61,129,.25);background:rgba(255,61,129,.11);color:#ff80a9}
.sp-dot{width:7px;height:7px;display:inline-block;border-radius:50%;background:currentColor;box-shadow:0 0 9px currentColor}
.sp-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:39px;padding:8px 13px;border:1px solid var(--sp-line);border-radius:11px;background:var(--sp-bg2);color:var(--sp-text)!important;font-size:11px;font-weight:900;transition:.18s ease}
.sp-button:hover{border-color:rgba(34,211,238,.55);transform:translateY(-1px)}
.sp-button.primary{border-color:transparent;background:linear-gradient(110deg,#22d3ee,#68a5ff);color:#07132f!important}
.sp-button.soft{background:rgba(34,211,238,.10);border-color:rgba(34,211,238,.2);color:var(--sp-cyan)!important}
.sp-button:focus-visible,.sp-workspace input:focus-visible,.sp-workspace select:focus-visible{outline:3px solid var(--sp-cyan);outline-offset:2px}
.sp-flash{margin-bottom:16px;padding:12px 15px;border:1px solid var(--sp-line);border-radius:13px;background:var(--sp-bg2);font-size:12px;line-height:2}
.sp-flash.success{border-color:rgba(46,230,166,.22);color:var(--sp-green)}
.sp-flash.error{border-color:rgba(255,61,129,.3);color:#ff9bb8}
.sp-nav{display:flex;gap:5px;overflow:auto;border-bottom:1px solid var(--sp-line);margin:0 0 18px;padding:0 0 10px;scrollbar-width:thin}
.sp-nav a{display:inline-flex;align-items:center;min-height:38px;padding:7px 13px;border-radius:10px;color:var(--sp-muted);font-size:11px;font-weight:850;white-space:nowrap}
.sp-nav a:hover{background:rgba(255,255,255,.055);color:#fff}
.sp-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.sp-kpi{position:relative;overflow:hidden;min-width:0;padding:16px;border:1px solid var(--sp-line);border-radius:15px;background:linear-gradient(145deg,rgba(27,36,96,.9),rgba(19,26,71,.94))}
.sp-kpi:after{content:"";position:absolute;inset-inline:16px;bottom:0;height:3px;border-radius:3px 3px 0 0;background:var(--sp-accent,var(--sp-cyan));opacity:.9}
.sp-kpi-label{color:var(--sp-muted);font-size:11px;font-weight:700}
.sp-kpi-value{margin-top:8px;color:#fff;font-size:clamp(23px,2.6vw,31px);font-weight:950;line-height:1.35;font-variant-numeric:tabular-nums}
.sp-kpi-hint{margin-top:4px;color:var(--sp-muted);font-size:10px;line-height:1.8}
.sp-kpi.cyan{--sp-accent:var(--sp-cyan)}.sp-kpi.green{--sp-accent:var(--sp-green)}.sp-kpi.orange{--sp-accent:var(--sp-orange)}.sp-kpi.pink{--sp-accent:var(--sp-pink)}
.sp-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:14px}
.sp-card{min-width:0;border:1px solid var(--sp-line);border-radius:16px;background:rgba(19,26,71,.92);overflow:hidden}
.sp-card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:16px 17px;border-bottom:1px solid var(--sp-line)}
.sp-card-head h2{margin:0;color:#fff;font-size:14px;font-weight:900;line-height:1.7}
.sp-card-head p{margin:4px 0 0;color:var(--sp-muted);font-size:10px;line-height:1.9}
.sp-card-body{padding:16px 17px}
.sp-section{margin-top:16px;scroll-margin-top:24px}
.sp-table-wrap{overflow:auto}
.sp-table{width:100%;border-collapse:collapse;min-width:620px}
.sp-table th,.sp-table td{padding:12px 14px;border-bottom:1px solid var(--sp-line);text-align:right;vertical-align:middle;white-space:nowrap}
.sp-table th{color:var(--sp-muted);font-size:10px;font-weight:800}
.sp-table td{color:#e5eaff;font-size:11px}
.sp-table tr:last-child td{border-bottom:0}
.sp-primary-text{color:#fff;font-weight:850}
.sp-secondary-text{display:block;margin-top:3px;color:var(--sp-muted);font-size:10px;white-space:normal;line-height:1.7}
.sp-code{display:inline-block;max-width:220px;padding:4px 8px;border:1px solid var(--sp-line);border-radius:7px;background:rgba(5,9,35,.55);color:#bfe9ff;font:10px ui-monospace,Consolas,monospace;direction:ltr;unicode-bidi:embed;overflow-wrap:anywhere}
.sp-status{display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border:1px solid var(--sp-line);border-radius:99px;background:rgba(255,255,255,.045);color:var(--sp-muted);font-size:10px;font-weight:850}
.sp-status.ok{border-color:rgba(46,230,166,.22);background:rgba(46,230,166,.09);color:var(--sp-green)}
.sp-status.warn{border-color:rgba(255,166,43,.24);background:rgba(255,166,43,.09);color:var(--sp-orange)}
.sp-status.bad{border-color:rgba(255,61,129,.24);background:rgba(255,61,129,.09);color:#ff80a9}
.sp-callout{display:flex;align-items:flex-start;gap:11px;padding:13px 14px;border:1px solid rgba(255,166,43,.22);border-radius:13px;background:rgba(255,166,43,.075);color:#f9d7a0;font-size:11px;line-height:2}
.sp-callout.info{border-color:rgba(34,211,238,.2);background:rgba(34,211,238,.065);color:#bfeefa}
.sp-callout strong{display:block;margin-bottom:2px;color:#fff;font-weight:900}
.sp-callout p{margin:0}
.sp-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.sp-mini{padding:12px;border:1px solid var(--sp-line);border-radius:12px;background:rgba(7,12,42,.42)}
.sp-mini span{display:block;color:var(--sp-muted);font-size:10px}
.sp-mini strong{display:block;margin-top:5px;color:#fff;font-size:12px;font-weight:900;overflow-wrap:anywhere}
.sp-form{display:grid;gap:14px}
.sp-field{display:grid;gap:7px}
.sp-field label,.sp-field>span{color:#f0f2ff;font-size:11px;font-weight:850}
.sp-input,.sp-select{width:100%;min-height:42px;padding:9px 12px;border:1px solid var(--sp-line);border-radius:10px;background:#0a1032;color:#fff;outline:0}
.sp-input::placeholder{color:#6875ad}
.sp-help{color:var(--sp-muted);font-size:10px;line-height:1.9}
.sp-check{display:flex;align-items:flex-start;gap:9px;padding:11px 12px;border:1px solid var(--sp-line);border-radius:11px;background:rgba(255,255,255,.035);color:var(--sp-muted);font-size:10px;line-height:1.9}
.sp-check input{margin-top:4px;accent-color:var(--sp-cyan)}
.sp-guide{display:grid;gap:11px;counter-reset:sp-step}
.sp-guide li{position:relative;display:grid;grid-template-columns:29px 1fr;gap:11px;list-style:none}
.sp-guide li:before{counter-increment:sp-step;content:counter(sp-step);display:grid;place-items:center;width:28px;height:28px;border:1px solid rgba(34,211,238,.25);border-radius:9px;background:rgba(34,211,238,.1);color:var(--sp-cyan);font-size:11px;font-weight:900}
.sp-guide strong{display:block;color:#fff;font-size:11px}
.sp-guide p{margin:3px 0 0;color:var(--sp-muted);font-size:10px;line-height:2}
.sp-empty{padding:28px 18px;text-align:center;color:var(--sp-muted);font-size:11px;line-height:2}
.sp-pagination{padding:12px 16px;border-top:1px solid var(--sp-line);color:var(--sp-muted);font-size:10px}
.sp-pagination nav{display:flex;justify-content:space-between;gap:12px}
.sp-pagination a,.sp-pagination span{color:var(--sp-cyan)}
.sp-danger-note{border-color:rgba(255,61,129,.23);background:rgba(255,61,129,.06);color:#ffb4c9}
@media(max-width:1050px){.sp-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){.sp-grid{grid-template-columns:1fr}.sp-hero{padding-bottom:16px}.sp-card-head{padding:14px}.sp-card-body{padding:14px}}
@media(max-width:480px){.sp-workspace{border-radius:17px;padding:12px}.sp-kpis{gap:8px}.sp-kpi{padding:12px}.sp-kpi-value{font-size:22px}.sp-mini-grid{grid-template-columns:1fr}.sp-hero h1{font-size:24px}}
</style>
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
        'failed' => 'ردشده توسط شرکت ارائه‌دهنده سرویس',
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
            <h1>پخش ویدئوهای آموزشی</h1>
            <p>از این بخش می‌توانی سرویس پخش ویدئو را به دوره‌ها وصل کنی و دسترسی هنرجویان را بررسی کنی. اطلاعات محرمانه فقط برای اتصال استفاده می‌شود و در فهرست‌ها نمایش داده نمی‌شود.</p>
        </div>
        <div class="sp-hero-actions">
            <span class="sp-pill {{ $isConfigured ? 'ok' : 'warn' }}"><i class="sp-dot"></i>{{ $isConfigured ? 'تنظیمات سرور آماده است' : 'اتصال نیازمند تنظیم است' }}</span>
            <span class="sp-pill">{{ $mode === 'production' ? 'واقعی' : 'آزمایشی' }}</span>
            <a class="sp-button" href="https://panel.spotplayer.ir/" target="_blank" rel="noopener noreferrer">پنل شرکت ارائه‌دهنده سرویس SpotPlayer ↗</a>
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
        <a href="#sp-licenses">مجوزهای پخش صادرشده</a>
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
                <div class="sp-kpi-label">مجوزهای پخش ثبت‌شده</div>
                <div class="sp-kpi-value">{{ $persianNumber($licenseStats['total']) }}</div>
                <div class="sp-kpi-hint">{{ $persianNumber($licenseStats['active']) }} مجوز پخش فعال در شیخان</div>
            </article>
            <article class="sp-kpi {{ $reviewRequiredCount ? 'pink' : 'orange' }}">
                <div class="sp-kpi-label">نیازمند تطبیق با شرکت ارائه‌دهنده سرویس</div>
                <div class="sp-kpi-value">{{ $persianNumber($reviewRequiredCount + $issuingCount) }}</div>
                <div class="sp-kpi-hint">{{ $persianNumber($failedCount) }} درخواست با رد قطعی شرکت ارائه‌دهنده سرویس</div>
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
                        <div class="sp-mini"><span>محل نگهداری کلید اتصال</span><strong>{{ $environmentKeyConfigured ? 'تنظیمات سرور' : ($storedKeyConfigured ? 'ذخیره امن در سامانه' : 'ثبت نشده') }}</strong></div>
                        <div class="sp-mini"><span>حالت مؤثر</span><strong>{{ $mode === 'production' ? 'اتصال واقعی' : 'اتصال آزمایشی' }}</strong></div>
                        <div class="sp-mini"><span>حداکثر دستگاه مجاز</span><strong>{{ $persianNumber($maxActiveDevices) }} دستگاه</strong></div>
                        <div class="sp-mini"><span>دستگاه وب مجاز</span><strong>{{ $persianNumber($webAppDevices) }} دستگاه</strong></div>
                    </div>
                    @if($environmentModeOverride)
                        <div class="sp-callout mt-3"><div><strong>حالت اتصال از تنظیمات سرور خوانده می‌شود</strong><p>اگر حالت اتصال در سرور تنظیم شده باشد، همان تنظیم بر گزینه‌ی انتخابی این فرم اولویت دارد.</p></div></div>
                    @endif
                    <div class="sp-callout info mt-3">
                        <div><strong>پخش زنده هنوز از این صفحه قابل تأیید نیست</strong><p>تا زمانی که ویدئوی آزمایشی با حساب واقعی شرکت ارائه‌دهنده سرویس و حساب دانش‌آموز پخش نشود، این وضعیت فقط آمادگی تنظیمات را نشان می‌دهد.</p></div>
                    </div>
                </div>
            </article>

            <article class="sp-card">
                <div class="sp-card-head">
                    <div><h2>تعداد مجوزهای پخش در هر حالت</h2><p>این آمار بر اساس نوع اتصال ثبت‌شده در شیخان نمایش داده می‌شود.</p></div>
                </div>
                <div class="sp-card-body">
                    <div class="sp-mini-grid">
                        <div class="sp-mini"><span>مجوزهای پخش آزمایشی</span><strong>{{ $persianNumber($licenseStats['test']) }}</strong></div>
                        <div class="sp-mini"><span>مجوزهای پخش واقعی</span><strong>{{ $persianNumber($licenseStats['production']) }}</strong></div>
                        <div class="sp-mini"><span>درخواست در حال صدور</span><strong>{{ $persianNumber($issuingCount) }}</strong></div>
                        <div class="sp-mini"><span>درخواست‌های ناموفق قطعی</span><strong>{{ $persianNumber($failedCount) }}</strong></div>
                    </div>
                    <p class="sp-help mt-3">این آمار از اطلاعات ثبت‌شده در شیخان نمایش داده می‌شود؛ تعداد دستگاه‌های آنلاین یا فهرست زنده پنل شرکت ارائه‌دهنده نیست.</p>
                </div>
            </article>
        </div>

        @if($reviewRequiredCount > 0)
            <div class="sp-callout sp-danger-note mt-4">
                <div><strong>{{ $persianNumber($reviewRequiredCount) }} وضعیت مجوز پخش نیاز به بررسی دارد</strong><p>در این وضعیت‌ها ممکن است شرکت ارائه‌دهنده سرویس مجوز پخش را ساخته باشد ولی پاسخ سرویس یا ثبت اطلاعات هنوز قطعی نباشد. قبل از هر تلاش مجدد، شناسه و وضعیت را در پنل شرکت ارائه‌دهنده سرویس بررسی کن؛ صدور دوبارهٔ خودکار می‌تواند مجوز پخش تکراری ایجاد کند.</p></div>
            </div>
        @endif
    </section>

    <section id="sp-courses" class="sp-card sp-section">
        <div class="sp-card-head">
            <div><h2>دوره‌ها و نگاشت به SpotPlayer</h2><p>برای هر دورهٔ شیخان، شناسهٔ متناظر شرکت ارائه‌دهنده سرویس باید ثبت شده باشد. ویدئوها از پنل شرکت ارائه‌دهنده سرویس بارگذاری می‌شوند.</p></div>
            <a class="sp-button soft" href="{{ route('owner.courses.index') }}">مدیریت همهٔ دوره‌ها</a>
        </div>
        @if($spotPlayerCourses->count())
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead><tr><th>دوره</th><th>آموزشگاه</th><th>وضعیت</th><th>شناسهٔ شرکت ارائه‌دهنده سرویس</th><th>مجوزهای پخش ثبت‌شده</th><th>عملیات</th></tr></thead>
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
            <div><h2>مجوزهای پخش صادرشده در شیخان</h2><p>فقط داده‌های داخلی ذخیره‌شده نمایش داده می‌شود؛ کلید دسترسی در این صفحه نمایش داده نمی‌شود.</p></div>
            <span class="sp-pill">{{ $persianNumber($licenseStats['total']) }} مجوز ثبت‌شده</span>
        </div>
        @if($licenseRecords->isNotEmpty())
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead><tr><th>دانش‌آموز</th><th>دوره</th><th>شناسهٔ شرکت ارائه‌دهنده سرویس</th><th>محیط صدور</th><th>تاریخ صدور</th><th>وضعیت</th></tr></thead>
                    <tbody>
                    @foreach($licenseRecords as $license)
                        <tr>
                            <td><span class="sp-primary-text">{{ $license->student?->name ?: 'حساب حذف‌شده' }}</span><span class="sp-secondary-text">شناسه داخلی {{ $persianNumber($license->student_id) }}</span></td>
                            <td>{{ $license->course?->title ?: 'دوره حذف‌شده' }}</td>
                            <td><code class="sp-code">{{ $license->provider_course_id }}</code></td>
                            <td><span class="sp-status {{ $license->provider_mode === 'production' ? 'ok' : 'warn' }}">{{ $license->provider_mode === 'production' ? 'واقعی' : 'آزمایشی' }}</span></td>
                            <td>{{ $license->issued_at ? \App\Support\PersianUi::dateTime($license->issued_at) : '—' }}</td>
                            <td><span class="sp-status {{ $licenseStatusTone($license->status) }}">{{ $licenseStatusLabel($license->status) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sp-empty">هنوز مجوز پخشی در شیخان ثبت نشده است. پس از ثبت خرید/دسترسی و فعال‌سازی پخش امن توسط دانش‌آموز، مورد ثبت‌شده اینجا نمایش داده می‌شود.</div>
        @endif
    </section>

    <section id="sp-activity" class="sp-card sp-section">
        <div class="sp-card-head">
            <div><h2>درخواست‌های نیازمند بررسی</h2><p>وضعیت‌هایی که از روی پاسخ شرکت ارائه‌دهنده سرویس یا قطعیت ذخیره‌سازی باید پیگیری شوند.</p></div>
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
                            <td><span class="sp-secondary-text">{{ $issuance->last_error ?: 'برای ادامه، وضعیت پنل شرکت ارائه‌دهنده سرویس را بررسی کن.' }}</span></td>
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
                <div><strong>چرا این ردیف‌ها دکمهٔ «صدور مجدد» ندارند؟</strong><p>در سرویس فعلی، API و گردش‌کار مطمئن برای استعلام، ابطال یا صدور مجدد از سمت مدیر پیاده نشده است. بنابراین این صفحه وضعیت را نشان می‌دهد و از ارسال درخواست تکراریِ پرریسک جلوگیری می‌کند. تطبیق و ابطال باید از پنل شرکت ارائه‌دهنده سرویس انجام شود.</p></div>
            </div>
        </div>
    </section>

    <section id="sp-setup" class="sp-grid sp-section">
        <article class="sp-card">
            <div class="sp-card-head">
                <div><h2>اطلاعات اتصال و نوع استفاده</h2><p>کلید اتصال فقط در سرور استفاده و به‌صورت امن نگهداری می‌شود.</p></div>
            </div>
            <div class="sp-card-body">
                <form method="POST" action="{{ route('owner.spotplayer.update') }}" class="sp-form">
                    @csrf
                    @method('PUT')
                    <div class="sp-field">
                        <label for="sp-api-key">کلید اتصال شرکت ارائه‌دهنده سرویس SpotPlayer</label>
                        <input id="sp-api-key" type="password" name="api_key" value="" autocomplete="new-password" autocapitalize="none" spellcheck="false" maxlength="2048" dir="ltr" class="sp-input" placeholder="{{ $isConfigured ? 'برای نگه‌داشتن کلید فعلی خالی بگذار' : 'کلید اتصال را از پنل شرکت ارائه‌دهنده سرویس وارد کن' }}">
                        <p class="sp-help">مقدار قبلی هیچ‌وقت دوباره در فرم قرار نمی‌گیرد. کلید را داخل سورس، توضیحات دوره یا پیام به مدرس ننویس.</p>
                    </div>

                    @if($storedKeyConfigured && ! $environmentKeyConfigured)
                        <label class="sp-check"><input type="checkbox" name="clear_api_key" value="1"><span>کلید ذخیره‌شدهٔ رمزگذاری‌شده از پنل حذف شود.</span></label>
                    @endif

                    <div class="sp-field">
                        <label for="sp-mode">حالت اجرا</label>
                        <select id="sp-mode" name="mode" required class="sp-select">
                            <option value="test" @selected($mode === 'test')>آزمایش اتصال</option>
                            <option value="production" @selected($mode === 'production')>اتصال واقعی</option>
                        </select>
                        @if($environmentModeOverride)
                            <p class="sp-help">حالت مؤثر از متغیر سرور می‌آید؛ تغییر این فیلد تا زمان حذف override اثر اجرایی ندارد.</p>
                        @endif
                    </div>

                    <label class="sp-check"><input type="checkbox" name="confirm_production" value="1"><span>اگر اتصال واقعی را انتخاب می‌کنم، تأیید می‌کنم پخش ویدئو با حساب شرکت ارائه‌دهنده سرویس و هنرجو آزمایش شده است.</span></label>
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
                    <div class="sp-mini"><span>کلید اتصال</span><strong>{{ $environmentKeyConfigured ? 'از تنظیمات سرور' : ($storedKeyConfigured ? 'ذخیره امن' : 'وجود ندارد') }}</strong></div>
                    <div class="sp-mini"><span>حالت مؤثر</span><strong>{{ $mode === 'production' ? 'واقعی' : 'آزمایشی' }}</strong></div>
                </div>
                <div class="sp-callout info mt-3"><div><strong>تست واقعی را با دانش‌آموز آزمایشی انجام بده</strong><p>پس از تکمیل اتصال، از خود حساب دانش‌آموز وارد دوره شو و گزینهٔ فعال‌سازی پخش امن را اجرا کن. بازشدن پلیر و پخش واقعی را جداگانه بررسی کن.</p></div></div>
            </div>
        </article>
    </section>

    <section id="sp-guide" class="sp-card sp-section">
        <div class="sp-card-head"><div><h2>راهنمای راه‌اندازی و تحویل</h2><p>ترتیب کار روشن است؛ هر شناسه یا کلید فقط در جای خودش استفاده می‌شود.</p></div></div>
        <div class="sp-card-body">
            <ol class="sp-guide">
                <li><div><strong>کلید اتصال را از پنل سرویس دریافت کن</strong><p>با حساب شرکت ارائه‌دهنده سرویس وارد پنل شو، کلید اتصال را بردار و فقط در فرم امن بالا ثبت کن. این کلید با شناسه دوره و کلید دسترسی هنرجو متفاوت است.</p><a class="sp-button mt-2" href="https://panel.spotplayer.ir/" target="_blank" rel="noopener noreferrer">بازکردن پنل شرکت ارائه‌دهنده سرویس ↗</a></div></li>
                <li><div><strong>ویدئوها را در دورهٔ شرکت ارائه‌دهنده سرویس بارگذاری کن</strong><p>فایل‌های MP4/WebM را در پنل SpotPlayer بارگذاری کن؛ فرم فایل‌های همراه شیخان برای PDF، تصویر و ZIP است.</p></div></li>
                <li><div><strong>شناسه سرویس پخش را به دورهٔ متناظر شیخان وصل کن</strong><p>از فهرست دوره‌ها وارد ویرایش دوره شو و شناسهٔ واقعی SpotPlayer را در فیلد اتصال قرار بده. شناسهٔ داخلی شیخان را اینجا وارد نکن.</p></div></li>
                <li><div><strong>با حساب آزمایشی هنرجو دسترسی ایجاد کن</strong><p>دانش‌آموز واجد دسترسی، از صفحهٔ دوره دکمهٔ «فعال‌سازی پخش امن ویدئو» را می‌زند. ثبت مجوز در شیخان به معنی آزمایش موفق پخش ویدئو نیست.</p></div></li>
                <li><div><strong>پخش و محدودیت دستگاه‌ها را بررسی کن؛ سپس اتصال واقعی را فعال کن</strong><p>پخش‌کننده ویدئو در سایت، نشان اختصاصی آموزشگاه، دسترسی و تعداد دستگاه را با حساب شرکت ارائه‌دهنده سرویس آزمایش کن. فقط پس از اطمینان از پخش موفق و فعال‌بودن دسترسی، اتصال واقعی را انتخاب کن.</p></div></li>
            </ol>
            <div class="sp-callout mt-4"><div><strong>حفاظت محتوا</strong><p>این سرویس برای ویدئوهای ضبط‌شده است؛ جلسه‌های زنده از بخش کلاس‌های آنلاین مدیریت می‌شوند. نشان اختصاصی آموزشگاه و مجوز پخش ریسک اشتراک‌گذاری را کاهش می‌دهند، اما ضبط صفحه با دستگاه دیگر را صددرصد ناممکن نمی‌کنند.</p></div></div>
        </div>
    </section>
</div>
@endsection
