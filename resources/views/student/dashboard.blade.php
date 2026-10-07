@extends('layouts.student')

@section('title', 'داشبورد دانش‌آموز | شیخان')
@section('header-title', 'داشبورد دانش‌آموز')

@section('content')
<div class="student-dashboard">
    <section class="student-welcome">
        <span class="student-kicker">فضای اختصاصی دانش‌آموز</span>
        <h2>سلام {{ $student->name }} 👋</h2>
        <p>دوره‌ها، جلسات، تکالیف و نتیجه‌های خودت را از یک مسیر امن و یکپارچه دنبال کن.</p>
    </section>

    <section class="student-stats" aria-label="خلاصه وضعیت">
        <article class="student-stat"><span>دوره‌های فعال</span><strong>{{ \App\Support\PersianUi::digits($activeCourseCount) }}</strong></article>
        <article class="student-stat"><span>میانگین پیشرفت</span><strong>{{ \App\Support\PersianUi::digits($overallProgress) }}٪</strong></article>
        <article class="student-stat"><span>تکالیف در انتظار</span><strong>{{ \App\Support\PersianUi::digits($pendingAssignments) }}</strong></article>
    </section>

    <div class="student-grid">
        <section class="student-panel">
            <div class="student-panel-head">
                <div><span class="student-kicker" style="color:var(--panel-primary)">یادگیری من</span><h3>دوره‌های فعال</h3></div>
                <span class="student-row-meta">{{ \App\Support\PersianUi::digits($courses->count()) }} دوره</span>
            </div>
            <div class="student-course-list">
                @forelse($courses as $course)
                    <article class="student-course">
                        <div>
                            <div class="student-course-title">{{ $course->title }}</div>
                            <div class="student-course-meta">{{ $course->level ?: 'دوره آموزشی' }}</div>
                        </div>
                        <div class="student-progress">
                            <strong>{{ \App\Support\PersianUi::digits(round($course->learning_progress)) }}٪</strong>
                            <div class="student-progress-track"><span style="width:{{ min(100,max(0,$course->learning_progress)) }}%"></span></div>
                        </div>
                    </article>
                @empty
                    <div class="student-empty">هنوز دوره فعالی برای حساب شما ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="student-panel">
            <div class="student-panel-head">
                <div><span class="student-kicker" style="color:var(--panel-primary)">چراغ جلسات</span><h3>جلسات کلاس</h3></div>
                <span class="student-row-meta">خصوصی</span>
            </div>
            <div class="student-list">
                @forelse($sessions as $session)
                    <article class="student-session {{ $session['available'] ? 'is-open' : 'is-locked' }}">
                        <div class="student-session-head">
                            <span class="student-lamp {{ $session['available'] ? 'on' : 'off' }}"></span>
                            <strong>{{ $session['lamp'] === 'روشن' ? 'چراغ روشن' : 'چراغ خاموش' }}</strong>
                            <span>{{ $session['date'] }}</span>
                        </div>
                        <div class="student-row-title">{{ $session['title'] }}</div>
                        <div class="student-row-meta">{{ $session['course'] }} · {{ $session['time'] }}</div>
                        @if($session['available'])
                            <a class="student-session-action" href="{{ $session['href'] }}">مشاهده جلسه ←</a>
                        @elseif($session['is_future'])
                            <span class="student-session-action disabled">جلسه هنوز برگزار نشده</span>
                        @else
                            <span class="student-session-action disabled">ویدئو هنوز منتشر نشده</span>
                        @endif
                    </article>
                @empty
                    <div class="student-empty">هنوز جلسه‌ای برای دوره‌های شما ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="student-panel">
        <div class="student-panel-head">
            <div><span class="student-kicker" style="color:var(--panel-primary)">پیگیری</span><h3>آخرین نتیجه‌ها</h3></div>
        </div>
        <div class="student-list">
            @forelse($recentResults as $result)
                <div class="student-list-row">
                    <div class="student-date"><strong>{{ \App\Support\PersianUi::digits($result->score ?? 0) }}</strong><small>نمره</small></div>
                    <div><div class="student-row-title">{{ $result->title }}</div><span class="student-row-meta">{{ \App\Support\PersianUi::date($result->graded_at) }}</span></div>
                </div>
            @empty
                <div class="student-empty">هنوز نتیجه‌ای ثبت نشده است.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
