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
                        <strong class="teacher-workspace-item-title">{{ $assignment->title }}</strong>
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
