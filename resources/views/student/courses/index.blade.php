@extends('layouts.student')

@section('title','دوره‌های من | شیخان')
@section('header-title','دوره‌های من')

@section('content')
<div class="student-workspace-page">
    <header class="student-workspace-head">
        <div class="student-workspace-head-copy">
            <span class="student-workspace-kicker">مسیر یادگیری</span>
            <h1 class="student-workspace-title">دوره‌های من</h1>
            <p class="student-workspace-description">فقط دوره‌هایی که دسترسی آموزشی این حساب برای آن‌ها معتبر است؛ پیشرفت هر مسیر را از همین‌جا دنبال کن.</p>
        </div>
        <div class="student-workspace-actions"><a class="student-workspace-btn primary" href="{{ route('student.dashboard') }}">خانه یادگیری</a></div>
    </header>

    <section class="student-workspace-card" aria-labelledby="student-courses-title">
        <div class="student-workspace-card-head">
            <div><h2 id="student-courses-title">دوره‌های فعال</h2><p>مسیرهای آموزشی فعلی شما</p></div>
            <span class="student-workspace-status primary">{{ AppSupportPersianUi::digits($courses->count()) }} دوره</span>
        </div>
        <div class="student-workspace-list">
            @forelse($courses as $course)
                @php($p=max(0,min(100,(float)($course->learning_progress ?? 0))))
                <article class="student-workspace-row">
                    <div class="student-workspace-date"><strong>{{ AppSupportPersianUi::digits(round($p)) }}٪</strong><small>پیشرفت</small></div>
                    <div class="student-workspace-row-main">
                        <a href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
                        <span>{{ $course->academy?->name ?? 'آکادمی شیخان' }} · {{ $course->level ?: 'دوره آموزشی' }}</span>
                        <div class="student-progress" style="margin-top:7px"><div class="student-progress-track"><span style="width:{{ $p }}%"></span></div></div>
                    </div>
                    <span class="student-workspace-status {{ $p >= 100 ? 'success' : 'primary' }}">{{ $p >= 100 ? 'تکمیل' : 'ادامه' }}</span>
                </article>
            @empty
                <div class="student-workspace-empty"><strong>هنوز دوره فعالی برای شما ثبت نشده است.</strong><span>پس از ثبت‌نام معتبر، مسیر آموزشی شما در این بخش نمایش داده می‌شود.</span></div>
            @endforelse
        </div>
        @if($courses->isNotEmpty())
            @foreach($courses->take(1) as $course)
                <div class="student-workspace-actions" style="margin-top:13px"><a class="student-workspace-btn primary" href="{{ route('student.courses.show', $course) }}">ادامه یادگیری ←</a></div>
            @endforeach
        @endif
        
    </section>
</div>
@endsection