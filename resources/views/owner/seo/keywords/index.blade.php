@extends('layouts.seo-specialist')

@section('title', 'کلمات کلیدی | پنل متخصص SEO شیخان')
@section('header-title', 'کلمات کلیدی')

@section('content')
<div class="owner-seo-page seo-keywords-page space-y-6">
    @include('owner.admin.partials.public-site-workspace-tabs')
    <section class="owner-seo-hero">
        <div>
            <span class="owner-seo-kicker">برنامه‌ریزی و پیگیری عبارت‌های جست‌وجو</span>
            <h1>عبارت‌هایی که مردم جست‌وجو می‌کنند</h1>
            <p>عبارت‌های جست‌وجوی مرتبط با آموزشگاه را گروه‌بندی کن، برای هرکدام یک صفحه مشخص کن و اولویت و وضعیت رسیدگی را بنویس. رتبه را فقط وقتی ثبت کن که واقعاً بررسی شده باشد.</p>
        </div>
        <div class="seo-keyword-hero-actions">
            <a href="{{ route('owner.seo.index') }}" class="website-open-btn">ممیزی صفحات SEO</a>
            <a href="{{ route('owner.seo.keywords.create') }}" class="course-action-btn primary">افزودن کلمه +</a>
        </div>
    </section>

    <section class="seo-keyword-stats" aria-label="خلاصه عبارت‌های ثبت‌شده">
        <article class="owner-seo-stat"><span>کل کلمات ثبت‌شده</span><strong>{{ number_format($stats['total']) }}</strong><small>فهرست داده‌های واردشده</small></article>
        <article class="owner-seo-stat"><span>بدون صفحهٔ هدف</span><strong>{{ number_format($stats['withoutTarget']) }}</strong><small>نیازمند تعیین URL مقصد</small></article>
        <article class="owner-seo-stat"><span>اولویت بالا</span><strong>{{ number_format($stats['highPriority']) }}</strong><small>برای بازبینی در برنامهٔ کار</small></article>
        <article class="owner-seo-stat"><span>بدون رتبهٔ مشاهده‌شده</span><strong>{{ number_format($stats['withoutPosition']) }}</strong><small>هنوز رتبهٔ قابل استنادی ثبت نشده</small></article>
        <article class="owner-seo-stat"><span>در حال پیگیری</span><strong>{{ number_format($stats['inProgress']) }}</strong><small>برنامه‌ریزی، اجرا یا رصد</small></article>
    </section>

    <section class="dashboard-panel owner-panel seo-keyword-tools">
        <div class="owner-panel-head">
            <div><h2>افزودن یا دریافت چند عبارت با هم</h2><p>اگر فهرست بزرگی داری، می‌توانی آن را با فایل جدولی CSV وارد کنی؛ عبارت‌های تکراری دوباره ثبت نمی‌شوند.</p></div>
            <div class="seo-keyword-hero-actions">
                <a href="{{ route('owner.seo.keywords.template') }}" class="course-action-btn">دانلود قالب CSV</a>
                <a href="{{ route('owner.seo.keywords.export') }}" class="course-action-btn">خروجی CSV با فیلترهای فعلی ↓</a>
            </div>
        </div>
        <form method="POST" action="{{ route('owner.seo.keywords.import') }}" enctype="multipart/form-data" class="seo-keyword-import">
            @csrf
            <label class="seo-keyword-file-label" for="keyword-csv">فایل جدولی CSV یا TXT (حداکثر ۵ مگابایت)</label>
            <input id="keyword-csv" name="file" type="file" accept=".csv,.txt,text/csv,text/plain" required>
            <button class="course-action-btn primary" type="submit">ورود کلمات</button>
            @error('file')<small class="seo-keyword-error">{{ $message }}</small>@enderror
        </form>
        <details class="seo-keyword-csv-help">
            <summary>ساختار فایل CSV و ستون‌های قابل شناسایی</summary>
            <p>ستون اجباری: <code dir="ltr">keyword</code> یا «کلمه کلیدی». ستون‌های اختیاری: <code dir="ltr">cluster, intent, target_url, priority, status, search_volume, target_position, observed_position, observed_on, impressions, clicks, ctr, notes</code>. عنوان‌های فارسی مثل «گروه موضوعی»، «نیت»، «آدرس هدف»، «حجم جستجو» و «رتبه مشاهده شده» هم پذیرفته می‌شوند.</p>
            <p>فقط داده‌ای را برای حجم جست‌وجو یا رتبه وارد کن که از یک منبع واقعی آمده باشد. فهرست داخلی به‌تنهایی رتبه یا حجم گوگل را محاسبه نمی‌کند.</p>
            <div class="seo-keyword-template">keyword,cluster,intent,target_url,priority,status,search_volume,target_position,observed_position,observed_on,notes</div>
        </details>
    </section>

    <section class="dashboard-panel owner-panel seo-keyword-list-panel">
        <div class="owner-panel-head">
            <div><h2>فهرست عبارت‌ها</h2><p>عبارت‌ها را جست‌وجو و گروه‌بندی کن و صفحه مرتبط و وضعیت رسیدگی هرکدام را ببین.</p></div>
            <span class="owner-pill">{{ number_format($keywords->total()) }} مورد در این فهرست</span>
        </div>

        <form method="GET" action="{{ route('owner.seo.keywords.index') }}" class="seo-keyword-filters">
            <label><span>جست‌وجو</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="عبارت، گروه، نشانی صفحه یا یادداشت..."></label>
            <label><span>وضعیت کار</span><select name="status"><option value="">همهٔ وضعیت‌ها</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span>هدف جست‌وجو</span><select name="intent"><option value="">همه</option>@foreach($intentLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['intent'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span>اولویت</span><select name="priority"><option value="">همه</option>@foreach($priorityLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span>گروه موضوعی</span><select name="cluster"><option value="">همهٔ گروه موضوعی‌ها</option>@foreach($clusters as $cluster)<option value="{{ $cluster }}" @selected(($filters['cluster'] ?? '') === $cluster)>{{ $cluster }}</option>@endforeach</select></label>
            <div class="seo-keyword-filter-actions"><button type="submit" class="course-action-btn primary">اعمال فیلتر</button><a href="{{ route('owner.seo.keywords.index') }}" class="course-action-btn">پاک کردن</a></div>
        </form>

        <div class="seo-keyword-table-wrap">
            <table class="seo-keyword-table">
                <thead><tr><th>کلمهٔ کلیدی / گروه موضوعی</th><th>نیت و اولویت</th><th>صفحهٔ هدف</th><th>حجم ماهانه</th><th>رتبه</th><th>وضعیت</th><th>عملیات</th></tr></thead>
                <tbody>
                @forelse($keywords as $item)
                    <tr>
                        <td data-label="کلمهٔ کلیدی">
                            <strong class="seo-keyword-primary">{{ $item->keyword }}</strong>
                            <span class="seo-keyword-cluster">{{ $item->cluster ?: 'بدون گروه موضوعی' }}</span>
                            @if($item->notes)<small class="seo-keyword-notes">{{ \Illuminate\Support\Str::limit($item->notes, 105) }}</small>@endif
                        </td>
                        <td data-label="نیت و اولویت">
                            <span class="seo-keyword-intent">{{ $intentLabels[$item->intent] ?? $item->intent }}</span>
                            <span class="seo-keyword-priority is-{{ $item->priority }}">{{ $priorityLabels[$item->priority] ?? $item->priority }}</span>
                        </td>
                        <td data-label="صفحهٔ هدف">
                            @if($item->target_url)
                                <a href="{{ $item->target_url }}" target="_blank" rel="noopener noreferrer" class="seo-keyword-target">{{ \Illuminate\Support\Str::limit($item->target_url, 48) }} ↗</a>
                            @else
                                <span class="seo-keyword-missing">تعیین نشده</span>
                            @endif
                        </td>
                        <td data-label="حجم ماهانه">
                            @if($item->search_volume !== null)
                                <strong>{{ number_format($item->search_volume) }}</strong>
                                <small class="seo-keyword-source">{{ $item->volume_source === 'csv_import' ? 'ورود از CSV' : ($item->volume_source === 'search_console' ? 'Search Console' : 'ثبت دستی') }}</small>
                            @else
                                <span class="seo-keyword-missing">داده نداریم</span>
                            @endif
                        </td>
                        <td data-label="رتبه">
                            @if($item->last_observed_position !== null)
                                <strong class="seo-keyword-position">#{{ number_format($item->last_observed_position) }}</strong>
                                <small class="seo-keyword-source">{{ $item->last_observed_at?->format('Y-m-d') }} · {{ $item->last_observed_source === 'csv_import' ? 'CSV' : ($item->last_observed_source === 'search_console' ? 'Search Console' : 'دستی') }}</small>
                            @else
                                <span class="seo-keyword-missing">ثبت نشده</span>
                            @endif
                            @if($item->target_position)
                                <small class="seo-keyword-source">هدف: #{{ number_format($item->target_position) }}</small>
                            @endif
                        </td>
                        <td data-label="وضعیت"><span class="seo-keyword-status is-{{ $item->status }}">{{ $statusLabels[$item->status] ?? $item->status }}</span></td>
                        <td data-label="عملیات">
                            <div class="seo-keyword-actions">
                                <a href="{{ route('owner.seo.keywords.edit', $item) }}" class="course-action-btn primary">ویرایش / رتبه</a>
                                <form method="POST" action="{{ route('owner.seo.keywords.destroy', $item) }}" onsubmit="return confirm('این کلمه و تاریخچه رتبهٔ آن حذف شود؟');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="seo-keyword-delete">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="seo-keyword-empty"><strong>هنوز کلمه‌ای ثبت نشده</strong><p>اول یک عبارت جست‌وجو ثبت کن یا فهرست آماده را از فایل جدولی وارد کن. مقدار رتبه و حجم جست‌وجو تا زمان ورود داده خالی می‌ماند.</p><a href="{{ route('owner.seo.keywords.create') }}" class="course-action-btn primary">ثبت اولین کلمه</a></div>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($keywords->hasPages())<div class="mt-5">{{ $keywords->links() }}</div>@endif
    </section>
    <div class="seo-keyword-disclaimer"><strong>تفاوت مهم:</strong> این بخش، مدیریت کلمات، صفحهٔ هدف و تاریخچهٔ رتبه‌های واردشده را انجام می‌دهد. رتبهٔ زنده، حجم جست‌وجو و کلیک/نمایش خودکار فقط وقتی معتبرند که از CSV معتبر یا اتصال رسمی داده‌ای وارد شده باشند؛ این پنل فعلاً اتصال خودکار به Google Search Console یا سرویس رتبه‌گیری ندارد.</div>
</div>
@endsection
