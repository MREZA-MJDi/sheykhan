@extends('layouts.teacher')
@section('title','تکالیف | شیخان')
@section('header-title','تکالیف')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">ارزیابی</span>
            <h1 class="teacher-workspace-title">تکالیف</h1>
            <p class="teacher-workspace-description">تکلیف بساز، موعد تحویل را مدیریت کن و پاسخ‌های ارسال‌شده را از همین‌جا تصحیح کن.</p>
        </div>
        <a href="{{ route('teacher.assignments.create') }}" class="teacher-workspace-btn primary">+ تکلیف جدید</a>
    </header>

    @if(session('success'))<div class="teacher-workspace-alert success">{{ session('success') }}</div>@endif

    <form method="GET" action="{{ route('teacher.assignments.index') }}" class="teacher-workspace-filters" role="search" aria-label="جست‌وجو و فیلتر تکالیف">
        <label class="teacher-workspace-filter-search">
            <span>جست‌وجو</span>
            <input type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="نام، دوره یا کلاس…" autocomplete="off">
        </label>
        <label>
            <span>وضعیت</span>
            <select name="status">
                <option value="" @selected(request('status') === '')>همه وضعیت‌ها</option>
                <option value="draft" @selected(request('status') === 'draft')>پیش‌نویس</option>
                <option value="published" @selected(request('status') === 'published')>منتشرشده</option>
                <option value="closed" @selected(request('status') === 'closed')>بسته</option>
            </select>
        </label>
        <label>
            <span>اولویت رسیدگی</span>
            <select name="queue">
                <option value="" @selected(request('queue') !== 'needs_review')>همه موارد</option>
                <option value="needs_review" @selected(request('queue') === 'needs_review')>فقط نیازمند تصحیح</option>
            </select>
        </label>
        <div class="teacher-workspace-filter-actions">
            <button type="submit" class="teacher-workspace-btn primary">اعمال فیلتر</button>
            @if(request()->query())
                <a href="{{ route('teacher.assignments.index') }}" class="teacher-workspace-btn secondary">پاک‌کردن</a>
            @endif
        </div>
    </form>

    <section class="teacher-workspace-stats">
        <div class="teacher-workspace-stat"><small>کل تکالیف</small><strong>{{ App\Support\PersianUi::digits($assignments->total()) }}</strong><span>تحت مدیریت شما</span></div>
        <div class="teacher-workspace-stat"><small>ارسال‌شده</small><strong>{{ App\Support\PersianUi::digits($assignments->getCollection()->sum('submitted_count')) }}</strong><span>در این صفحه</span></div>
        <div class="teacher-workspace-stat"><small>منتظر تصحیح</small><strong>{{ App\Support\PersianUi::digits($assignments->getCollection()->sum('pending_review_count')) }}</strong><span>نیازمند اقدام</span></div>
        <div class="teacher-workspace-stat"><small>نمایش</small><strong>{{ App\Support\PersianUi::digits($assignments->count()) }}</strong><span>مورد در این صفحه</span></div>
    </section>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>فهرست تکالیف</h2><p>موارد نزدیک به موعد و آخرین موارد ایجادشده را سریع بررسی کن.</p></div></div>
        <div class="teacher-workspace-list">
            @forelse($assignments as $assignment)
                <article class="teacher-workspace-item">
                    <div class="teacher-workspace-item-main">
                        <a class="teacher-workspace-item-title teacher-workspace-title-link" href="{{ route('teacher.assignments.submissions',$assignment) }}" aria-label="باز کردن پاسخ‌های تکلیف {{ $assignment->title }}">{{ $assignment->title }}</a>
                        <div class="teacher-workspace-item-meta">
                            <span>{{ $assignment->course?->title ?: 'دوره' }}</span>
                            <span>{{ $assignment->classroom?->title ?: 'همه دانش‌آموزان دوره' }}</span>
                            <span>{{ $assignment->due_at ? 'موعد '.App\Support\PersianUi::date($assignment->due_at).' · '.App\Support\PersianUi::time($assignment->due_at) : 'بدون موعد' }}</span>
                            <span>نمره کل {{ App\Support\PersianUi::digits($assignment->max_score ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="teacher-workspace-item-actions">
                        <span class="teacher-workspace-chip">{{ $assignment->status === 'published' ? 'منتشرشده' : ($assignment->status === 'closed' ? 'بسته' : 'پیش‌نویس') }}</span>
                        <span class="teacher-workspace-chip">{{ App\Support\PersianUi::digits($assignment->submitted_count) }} ارسال</span>
                        <span class="teacher-workspace-chip {{ $assignment->pending_review_count ? 'warning' : 'success' }}">{{ App\Support\PersianUi::digits($assignment->pending_review_count) }} نیازمند بررسی</span>
                        @if($assignment->status === 'published')
                            <form method="POST" action="{{ route('teacher.assignments.status', $assignment) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="teacher-workspace-link danger" data-confirm="تکلیف بسته شود؟ پاسخ‌های ثبت‌شده حفظ می‌شوند.">بستن تکلیف</button>
                            </form>
                        @elseif((int) $assignment->total_submissions_count === 0)
                            <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="teacher-workspace-link danger" data-confirm="این تکلیف و فایل‌های بلااستفاده آن حذف شوند؟ این کار قابل بازگشت نیست.">حذف تکلیف</button>
                            </form>
                        @endif
                        <a href="{{ route('teacher.assignments.submissions',$assignment) }}" class="teacher-workspace-link primary">بررسی پاسخ‌ها</a>
                    </div>
                </article>
            @empty
                <div class="teacher-workspace-empty"><strong>تکلیفی ثبت نشده است.</strong>اولین تکلیف را بساز و آن را به یک دوره یا کلاس متصل کن.</div>
            @endforelse
        </div>
        @if($assignments->hasPages())<div class="teacher-workspace-pagination">{{ $assignments->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
