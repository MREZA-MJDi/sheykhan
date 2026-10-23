@extends('layouts.owner')

@section('title', $keyword->exists ? 'ویرایش کلمهٔ کلیدی | شیخان' : 'افزودن کلمهٔ کلیدی | شیخان')
@section('header-title', 'مدیریت کلمات کلیدی')

@section('content')
@php($isEdit = $keyword->exists)
<div class="owner-form-shell seo-keyword-form-page">
    <div class="owner-form-head">
        <div>
            <span>برنامه‌ریزی هدفمند جست‌وجو</span>
            <h1>{{ $isEdit ? $keyword->keyword : 'ثبت کلمهٔ کلیدی جدید' }}</h1>
            <p>عبارت را به یک صفحهٔ مشخص وصل کن و وضعیت اجرا و داده‌های واقعی آن را جداگانه نگه دار. رتبهٔ خالی یعنی مشاهده‌ای ثبت نشده است.</p>
        </div>
        <a href="{{ route('owner.seo.keywords.index') }}">بازگشت به فهرست</a>
    </div>

    <form method="POST" action="{{ $isEdit ? route('owner.seo.keywords.update', $keyword) : route('owner.seo.keywords.store') }}" class="owner-form-grid">
        @csrf
        @if($isEdit) @method('PATCH') @endif

        <section class="owner-form-section">
            <h2>مشخصات عبارت و مقصد</h2>
            <label>
                <span>کلمه یا عبارت کلیدی *</span>
                <input name="keyword" value="{{ old('keyword', $keyword->keyword) }}" maxlength="255" required placeholder="مثلاً: کلاس آنلاین ریاضی هفتم">
                @error('keyword')<small class="seo-keyword-error">{{ $message }}</small>@enderror
                <small>عبارت‌های تکراری پس از یکسان‌سازی فاصله‌ها و شکل رایج حروف فارسی دوباره ساخته نمی‌شوند.</small>
            </label>
            <label><span>خوشه / گروه موضوعی</span><input name="cluster" value="{{ old('cluster', $keyword->cluster) }}" maxlength="120" placeholder="مثلاً: ریاضی هفتم"></label>
            <div class="owner-form-two">
                <label><span>نیت جست‌وجو *</span><select name="intent" required>@foreach($intentLabels as $value => $label)<option value="{{ $value }}" @selected(old('intent', $keyword->intent ?: 'informational') === $value)>{{ $label }}</option>@endforeach</select></label>
                <label><span>اولویت *</span><select name="priority" required>@foreach($priorityLabels as $value => $label)<option value="{{ $value }}" @selected(old('priority', $keyword->priority ?: 'medium') === $value)>{{ $label }}</option>@endforeach</select></label>
            </div>
            <label>
                <span>URL صفحهٔ هدف</span>
                <input type="url" name="target_url" value="{{ old('target_url', $keyword->target_url) }}" maxlength="2048" placeholder="https://example.com/courses/math">
                @error('target_url')<small class="seo-keyword-error">{{ $message }}</small>@enderror
                <small>یک URL عمومی واقعی انتخاب کن تا چند صفحه برای یک عبارت با هم رقابت نکنند.</small>
            </label>
            <label><span>وضعیت کار *</span><select name="status" required>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $keyword->status ?: 'idea') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span>یادداشت برنامه‌ریزی</span><textarea name="notes" rows="4" maxlength="5000" placeholder="هدف صفحه، رقیب، ایدهٔ محتوا یا اقدام بعدی">{{ old('notes', $keyword->notes) }}</textarea></label>
        </section>

        <aside class="owner-form-side">
            <section class="owner-form-section">
                <h2>شاخص‌های قابل استناد</h2>
                <label>
                    <span>حجم جست‌وجوی ماهانه</span>
                    <input type="number" min="0" max="2147483647" name="search_volume" value="{{ old('search_volume', $keyword->search_volume) }}" placeholder="خالی بگذار اگر منبع نداری">
                    <small>عدد را از منبع دادهٔ خودت وارد کن؛ تخمین خودکار ساخته نمی‌شود.</small>
                </label>
                <label><span>رتبهٔ هدف</span><input type="number" min="1" max="1000" name="target_position" value="{{ old('target_position', $keyword->target_position) }}" placeholder="مثلاً ۱۰"></label>
            </section>
            <section class="owner-form-section">
                <h2>ثبت آخرین مشاهدهٔ واقعی</h2>
                <label>
                    <span>رتبهٔ مشاهده‌شده (اختیاری)</span>
                    <input type="number" min="1" max="1000" name="observed_position" value="{{ old('observed_position') }}" placeholder="اگر هنوز بررسی نشده، خالی بماند">
                    <small>این مقدار در تاریخچه با منبع «ثبت دستی» ذخیره می‌شود؛ جای دادهٔ زندهٔ گوگل نیست.</small>
                </label>
                <label><span>تاریخ مشاهده</span><input type="date" name="observed_on" value="{{ old('observed_on', $keyword->last_observed_at?->toDateString() ?: now()->toDateString()) }}"></label>
                <label><span>یادداشت مشاهده</span><textarea name="observation_notes" rows="3" maxlength="500" placeholder="کشور، دستگاه، موتور جست‌وجو یا توضیح کوتاه">{{ old('observation_notes') }}</textarea></label>
            </section>
            <button type="submit" class="owner-primary-btn w-full">{{ $isEdit ? 'ذخیره تغییرات' : 'ثبت کلمه' }}</button>
        </aside>
    </form>

    @if($isEdit)
        <section class="dashboard-panel owner-panel seo-keyword-history">
            <div class="owner-panel-head"><div><h2>ثبت یک مشاهدهٔ جدید</h2><p>برای حفظ تاریخچه، رتبهٔ تازه را ثبت کن؛ رکوردهای قبلی پاک نمی‌شوند.</p></div></div>
            <form method="POST" action="{{ route('owner.seo.keywords.rankings.store', $keyword) }}" class="seo-keyword-observation-form">
                @csrf
                <label><span>رتبه</span><input type="number" name="position" min="1" max="1000" required></label>
                <label><span>تاریخ مشاهده</span><input type="date" name="observed_on" value="{{ now()->toDateString() }}" required></label>
                <label><span>حجم جست‌وجو (در صورت داشتن منبع)</span><input type="number" name="search_volume" min="0" max="2147483647"></label>
                <label><span>Impressions (اختیاری)</span><input type="number" name="impressions" min="0"></label>
                <label><span>Clicks (اختیاری)</span><input type="number" name="clicks" min="0"></label>
                <label><span>CTR درصدی</span><input type="number" name="ctr" min="0" max="100" step="0.0001"></label>
                <label class="seo-keyword-observation-note"><span>منبع/یادداشت</span><input type="text" name="notes" maxlength="500" placeholder="مثلاً جست‌وجوی دستی، موبایل، ایران"></label>
                <button type="submit" class="course-action-btn primary">ثبت مشاهده</button>
            </form>

            <div class="seo-keyword-history-table">
                <h3>تاریخچهٔ ثبت‌شده</h3>
                <table class="seo-keyword-table">
                    <thead><tr><th>تاریخ</th><th>رتبه</th><th>حجم</th><th>نمایش / کلیک</th><th>منبع و یادداشت</th></tr></thead>
                    <tbody>
                    @forelse(($rankings ?? collect()) as $ranking)
                        <tr>
                            <td>{{ $ranking->observed_on?->format('Y-m-d') }}</td>
                            <td>{{ $ranking->position !== null ? '#'.number_format($ranking->position) : '—' }}</td>
                            <td>{{ $ranking->search_volume !== null ? number_format($ranking->search_volume) : '—' }}</td>
                            <td>{{ $ranking->impressions !== null ? number_format($ranking->impressions) : '—' }} / {{ $ranking->clicks !== null ? number_format($ranking->clicks) : '—' }}</td>
                            <td><span class="seo-keyword-source">{{ $ranking->source === 'csv_import' ? 'CSV' : ($ranking->source === 'search_console' ? 'Search Console' : 'دستی') }}</span><small class="seo-keyword-notes">{{ $ranking->notes }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">هنوز تاریخچه‌ای ثبت نشده؛ می‌توانی یک مشاهدهٔ واقعی ثبت کنی یا داده را از CSV وارد کنی.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <form method="POST" action="{{ route('owner.seo.keywords.destroy', $keyword) }}" class="seo-keyword-danger-zone" onsubmit="return confirm('این کلمه و تمام تاریخچهٔ آن حذف شود؟');">
            @csrf @method('DELETE')
            <span>حذف دائمی کلمه و تاریخچه</span>
            <button type="submit" class="seo-keyword-delete">حذف کلمه</button>
        </form>
    @endif
</div>
@endsection
