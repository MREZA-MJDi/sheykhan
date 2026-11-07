@extends('layouts.teacher')
@section('title','آزمون‌ها | شیخان')
@section('header-title','آزمون‌ها')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">ارزیابی</span>
            <h1 class="teacher-workspace-title">آزمون‌ها</h1>
            <p class="teacher-workspace-description">آزمون را به دوره و کلاس درست وصل کن، سؤال‌ها را بساز و تلاش‌های دانش‌آموزان را تصحیح کن.</p>
        </div>
        <a href="{{ route('teacher.exams.create') }}" class="teacher-workspace-btn primary">+ آزمون جدید</a>
    </header>

    @if(session('success'))<div class="teacher-workspace-alert success">{{ session('success') }}</div>@endif

    <form method="GET" action="{{ route('teacher.exams.index') }}" class="teacher-workspace-filters" role="search" aria-label="جست‌وجو و فیلتر آزمون‌ها">
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
        <div class="teacher-workspace-filter-actions">
            <button type="submit" class="teacher-workspace-btn primary">اعمال فیلتر</button>
            @if(request()->query())
                <a href="{{ route('teacher.exams.index') }}" class="teacher-workspace-btn secondary">پاک‌کردن</a>
            @endif
        </div>
    </form>

    <section class="teacher-workspace-stats">
        <div class="teacher-workspace-stat"><small>کل آزمون‌ها</small><strong>{{ App\Support\PersianUi::digits($exams->total()) }}</strong><span>آزمون تحت مدیریت</span></div>
        <div class="teacher-workspace-stat"><small>ارسال‌شده</small><strong>{{ App\Support\PersianUi::digits($exams->getCollection()->sum('submitted_attempts_count')) }}</strong><span>تلاش در این صفحه</span></div>
        <div class="teacher-workspace-stat"><small>نیازمند بررسی</small><strong>{{ App\Support\PersianUi::digits($exams->getCollection()->sum(fn($exam) => max(0,$exam->submitted_attempts_count-$exam->graded_attempts_count))) }}</strong><span>تلاش‌های غیرنهایی</span></div>
        <div class="teacher-workspace-stat"><small>سؤال‌ها</small><strong>{{ App\Support\PersianUi::digits($exams->getCollection()->sum('questions_count')) }}</strong><span>در این صفحه</span></div>
    </section>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>فهرست آزمون‌ها</h2><p>وضعیت، زمان و نتیجه‌های هر آزمون را یکجا ببین.</p></div></div>
        <div class="teacher-workspace-list">
            @forelse($exams as $exam)
                <article class="teacher-workspace-item">
                    <div class="teacher-workspace-item-main">
                        <a class="teacher-workspace-item-title teacher-workspace-title-link" href="{{ route('teacher.exams.attempts',$exam) }}" aria-label="باز کردن پاسخ‌های آزمون {{ $exam->title }}">{{ $exam->title }}</a>
                        <div class="teacher-workspace-item-meta">
                            <span>{{ $exam->course?->title ?: 'دوره' }}</span>
                            <span>{{ $exam->classroom?->title ?: 'همه دانش‌آموزان دوره' }}</span>
                            <span>{{ App\Support\PersianUi::digits($exam->duration_minutes) }} دقیقه</span>
                            <span>{{ App\Support\PersianUi::digits($exam->questions_count) }} سؤال</span>
                            @if($exam->starts_at)<span>شروع {{ App\Support\PersianUi::date($exam->starts_at).' · '.App\Support\PersianUi::time($exam->starts_at) }}</span>@endif
                        </div>
                    </div>
                    <div class="teacher-workspace-item-actions">
                        <span class="teacher-workspace-chip {{ $exam->status === 'published' ? 'success' : ($exam->status === 'closed' ? 'danger' : 'muted') }}">{{ $exam->status === 'published' ? 'منتشرشده' : ($exam->status === 'closed' ? 'بسته' : 'پیش‌نویس') }}</span>
                        <span class="teacher-workspace-chip">{{ App\Support\PersianUi::digits($exam->submitted_attempts_count) }} ارسال</span>
                        <span class="teacher-workspace-chip {{ $exam->submitted_attempts_count > $exam->graded_attempts_count ? 'warning' : 'success' }}">{{ App\Support\PersianUi::digits($exam->graded_attempts_count) }} تصحیح‌شده</span>
                        @if((int) $exam->attempts_count === 0)
                            <a href="{{ route('teacher.exams.edit', $exam) }}" class="teacher-workspace-link secondary">ویرایش برگه</a>
                        @else
                            <span class="teacher-workspace-chip muted" title="برای حفظ اعتبار پاسخ‌ها و نمره‌ها، برگه پس از شروع اولین تلاش قفل می‌شود.">برگه قفل است</span>
                        @endif
                        <a href="{{ route('teacher.exams.attempts',$exam) }}" class="teacher-workspace-link primary">مشاهده پاسخ‌ها</a>
                    </div>
                </article>
            @empty
                <div class="teacher-workspace-empty"><strong>آزمونی ثبت نشده است.</strong>یک آزمون بساز و سؤال‌هایش را از همان workflow تعریف کن.</div>
            @endforelse
        </div>
        @if($exams->hasPages())<div class="teacher-workspace-pagination">{{ $exams->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
