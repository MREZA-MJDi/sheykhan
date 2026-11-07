@extends('layouts.teacher')
@section('title', 'خانه مدرس | شیخان')
@section('page_title', 'خانه')
@section('page_subtitle', $dashboardDate ?? \App\Support\PersianUi::date(now()))
@section('content')
@php
    $user = $teacher ?? auth()->user();
    $metrics = $metrics ?? [];
    $classes = max(0, (int) ($metrics['activeClasses'] ?? 0));
    $students = max(0, (int) ($metrics['studentCount'] ?? 0));
    $pendingAssignments = max(0, (int) ($metrics['pendingAssignmentReviews'] ?? 0));
    $pendingExams = max(0, (int) ($metrics['pendingExamReviews'] ?? 0));
    $weeklySessions = max(0, (int) ($metrics['weeklySessions'] ?? 0));
    $classPreviews = collect($classroomRosterPreviews ?? []);
    $today = collect($todaySessions ?? []);
    $upcoming = collect($upcomingClasses ?? []);
    $recent = collect($activities ?? []);
    $quick = [
        ['label' => 'ثبت حضور و غیاب', 'description' => 'ثبت وضعیت حضور کلاس', 'icon' => '☷', 'route' => 'teacher.attendance.index', 'permission' => 'attendance.view', 'tone' => 'teal'],
        ['label' => 'ساخت تکلیف', 'description' => 'تعریف تمرین برای دانش‌آموزان', 'icon' => '✎', 'route' => 'teacher.assignments.create', 'permission' => 'assignments.manage', 'tone' => 'blue'],
        ['label' => 'ساخت آزمون', 'description' => 'طراحی و انتشار آزمون', 'icon' => '✓', 'route' => 'teacher.exams.create', 'permission' => 'exams.manage', 'tone' => 'violet'],
        ['label' => 'ارسال پیام', 'description' => 'ارتباط با دانش‌آموز یا خانواده', 'icon' => '✉', 'route' => 'teacher.direct-messages.index', 'permission' => 'parent_messages.manage', 'tone' => 'amber'],
    ];
    $quick = array_values(array_filter($quick, fn ($item) => $user?->hasPermission($item['permission'])));
@endphp

<div class="teacher-home">
    <section class="teacher-home__welcome">
        <div>
            <span class="teacher-home__eyebrow">فضای کاری مدرس</span>
            <h2>سلام {{ $user?->name }}، خوش آمدی</h2>
            <p>کارهای مهم امروزت را یک‌جا ببین و بدون جابه‌جایی بین صفحه‌ها ادامه بده.</p>
            <a href="{{ route('account.guide', ['role' => 'teacher']) }}" style="display:inline-flex;align-items:center;gap:8px;margin-top:14px;padding:9px 14px;border:1px solid rgba(255,255,255,.28);border-radius:12px;color:inherit;font-weight:800;text-decoration:none">راهنمای کار با پنل و آموزش مرحله‌به‌مرحله ←</a>
        </div>
        <div class="teacher-home__welcome-mark" aria-hidden="true">✦</div>
    </section>

    <section class="teacher-home__metrics" aria-label="خلاصه وضعیت آموزشی">
        @if($user?->hasPermission('classrooms.view'))
            <a class="teacher-home__metric metric-blue" href="{{ route('teacher.classrooms.index') }}">
                <span class="metric-icon">▤</span><span class="metric-value">{{ \App\Support\PersianUi::digits($classes) }}</span><span class="metric-label">کلاس فعال من</span>
            </a>
        @endif
        @if($user?->hasPermission('students.view'))
            <a class="teacher-home__metric metric-teal" href="{{ route('teacher.students.index') }}">
                <span class="metric-icon">♙</span><span class="metric-value">{{ \App\Support\PersianUi::digits($students) }}</span><span class="metric-label">دانش‌آموز تحت آموزش</span>
            </a>
        @endif
        @if($user?->hasPermission('assignments.view'))
            <a class="teacher-home__metric metric-amber" href="{{ route('teacher.assignments.index') }}">
                <span class="metric-icon">✎</span><span class="metric-value">{{ \App\Support\PersianUi::digits($pendingAssignments) }}</span><span class="metric-label">تکلیف در انتظار بررسی</span>
            </a>
        @endif
        @if($user?->hasPermission('exams.view'))
            <a class="teacher-home__metric metric-violet" href="{{ route('teacher.exams.index') }}">
                <span class="metric-icon">✓</span><span class="metric-value">{{ \App\Support\PersianUi::digits($pendingExams) }}</span><span class="metric-label">آزمون در انتظار بررسی</span>
            </a>
        @endif
        @if($user?->hasPermission('live_classes.view'))
            <a class="teacher-home__metric metric-slate" href="{{ route('teacher.schedule.index') }}">
                <span class="metric-icon">◷</span><span class="metric-value">{{ \App\Support\PersianUi::digits($weeklySessions) }}</span><span class="metric-label">جلسه در این هفته</span>
            </a>
        @endif
    </section>

    @if(count($quick))
        <section class="teacher-home__section">
            <div class="teacher-home__section-heading"><div><h3>میانبر کارهای روزانه</h3><p>برای کارهای پرتکرار، مستقیم وارد بخش مربوط شو.</p></div></div>
            <div class="teacher-home__quick-grid">
                @foreach($quick as $item)
                    <a class="teacher-home__quick teacher-home__quick--{{ $item['tone'] }}" href="{{ route($item['route']) }}">
                        <span class="quick-icon">{{ $item['icon'] }}</span><span><b>{{ $item['label'] }}</b><small>{{ $item['description'] }}</small></span><span class="quick-arrow" aria-hidden="true">←</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="teacher-home__model" aria-labelledby="teacher-model-title">
        <div class="teacher-home__model-heading">
            <div>
                <h3 id="teacher-model-title">چه چیزی را کجا مدیریت می‌کنی؟</h3>
                <p>هر بخش یک کار مشخص دارد؛ این تفکیک جلوی ثبت اطلاعات در جای اشتباه را می‌گیرد.</p>
            </div>
            <a href="{{ route('account.guide') }}">راهنمای کامل پنل</a>
        </div>
        <div class="teacher-home__model-grid">
            @if($user?->hasPermission('courses.view'))
                <article><strong>دوره</strong><p>محتوای آموزشی، سرفصل، درس، قیمت و ویدئوهای ضبط‌شده.</p><a href="{{ route('teacher.courses.index') }}">مدیریت دوره‌ها</a></article>
                <article><strong>SpotPlayer</strong><p>پخش امن ویدئوهای از پیش ضبط‌شده داخل صفحهٔ دوره؛ نه جلسهٔ زنده.</p><a href="{{ route('teacher.courses.index') }}">اتصال در تنظیمات دوره</a></article>
            @endif
            @if($user?->hasPermission('classrooms.view'))
                <article><strong>کلاس / گروه</strong><p>گروه دانش‌آموزان یک دوره؛ اعضا، ظرفیت، برنامه و حضور و غیاب.</p><a href="{{ route('teacher.classrooms.index') }}">مدیریت گروه‌ها</a></article>
            @endif
            @if($user?->hasPermission('live_classes.view'))
                <article><strong>جلسه آنلاین</strong><p>تدریس زنده در ساعت مشخص و با لینک ورود دانش‌آموزان.</p><a href="{{ route('teacher.live-classes.index') }}">زمان‌بندی جلسه</a></article>
            @endif
        </div>
    </section>

    <div class="teacher-home__columns">
        @if($user?->hasPermission('live_classes.view'))
            <section class="teacher-home__panel">
                <div class="teacher-home__section-heading"><div><h3>برنامه امروز</h3><p>جلسه‌هایی که برای امروز ثبت شده‌اند.</p></div><a href="{{ route('teacher.schedule.index') }}">برنامه کامل</a></div>
                @forelse($today as $session)
                    <div class="teacher-home__list-row">
                        <span class="list-time">{{ $session['time'] ?? '—' }}</span>
                        <span class="list-copy"><b>{{ $session['title'] ?? 'جلسه آموزشی' }}</b><small>{{ $session['meta'] ?? 'جزئیات جلسه' }}</small></span>
                        @if(!empty($session['can_join']) && filled($session['meeting_url'] ?? null))
                            <a class="teacher-home__join" href="{{ $session['meeting_url'] }}" target="_blank" rel="noopener noreferrer">ورود به جلسه</a>
                        @endif
                    </div>
                @empty
                    <div class="teacher-home__empty"><span>◷</span><b>امروز جلسه‌ای نداری</b><small>اگر جلسه‌ای زمان‌بندی شود، اینجا نمایش داده می‌شود.</small></div>
                @endforelse
            </section>
        @endif

        @if($user?->hasPermission('classrooms.view'))
            <section class="teacher-home__panel">
                <div class="teacher-home__section-heading"><div><h3>کلاس‌های من</h3><p>دسترسی سریع به کلاس‌های فعال.</p></div><a href="{{ route('teacher.classrooms.index') }}">همه کلاس‌ها</a></div>
                @forelse($classPreviews->take(5) as $class)
                    <a class="teacher-home__list-row teacher-home__class-row" href="{{ $class['url'] ?? route('teacher.classrooms.index') }}">
                        <span class="class-icon">▤</span><span class="list-copy"><b>{{ $class['title'] }}</b><small>{{ $class['course_title'] ?? 'دوره آموزشی' }}</small></span>
                        <span class="list-count">{{ \App\Support\PersianUi::digits($class['student_count'] ?? 0) }} نفر</span>
                    </a>
                @empty
                    <div class="teacher-home__empty"><span>▤</span><b>هنوز کلاسی به شما اختصاص داده نشده</b><small>کلاس‌های تخصیص‌یافته در این بخش دیده می‌شوند.</small></div>
                @endforelse
            </section>
        @endif
    </div>

    <div class="teacher-home__columns teacher-home__columns--bottom">
        @if($user?->hasPermission('live_classes.view'))
            <section class="teacher-home__panel">
                <div class="teacher-home__section-heading"><div><h3>کلاس‌های آنلاین پیش‌رو</h3><p>جلسات آینده و زمان شروع آن‌ها.</p></div><a href="{{ route('teacher.live-classes.index') }}">مدیریت کلاس آنلاین</a></div>
                @forelse($upcoming->take(4) as $class)
                    <div class="teacher-home__list-row">
                        <span class="class-icon class-icon--online">◉</span>
                        <span class="list-copy"><b>{{ $class->course?->title ?? 'کلاس آنلاین' }}</b><small>{{ $class->classroom?->title ?? 'جلسه آموزشی' }} · {{ \App\Support\PersianUi::date($class->scheduled_at) }}، {{ \App\Support\PersianUi::time($class->scheduled_at) }}</small></span>
                        @if(($class->can_join ?? false) && filled($class->meeting_url))
                            <a class="teacher-home__join" href="{{ $class->meeting_url }}" target="_blank" rel="noopener noreferrer">ورود</a>
                        @else
                            <span class="list-status">{{ $class->status }}</span>
                        @endif
                    </div>
                @empty
                    <div class="teacher-home__empty"><span>◉</span><b>کلاس آنلاین پیش‌رویی ثبت نشده</b><small>جلسات زمان‌بندی‌شده اینجا نمایش داده می‌شوند.</small></div>
                @endforelse
            </section>
        @endif

        @if($user?->hasPermission('assignments.view'))
            <section class="teacher-home__panel">
                <div class="teacher-home__section-heading"><div><h3>نیازمند پیگیری</h3><p>آخرین تکالیف و تعداد ارسال‌های منتظر بررسی.</p></div><a href="{{ route('teacher.assignments.index') }}">همه تکالیف</a></div>
                @forelse($recent->take(4) as $activity)
                    <a class="teacher-home__list-row" href="{{ route('teacher.assignments.index') }}">
                        <span class="class-icon class-icon--task">✎</span><span class="list-copy"><b>{{ $activity->title }}</b><small>{{ $activity->classroom?->title ?? 'دوره' }} · {{ $activity->due_at ? \App\Support\PersianUi::date($activity->due_at) : 'بدون مهلت' }}</small></span>
                        <span class="list-count">{{ \App\Support\PersianUi::digits($activity->pending_review_count ?? 0) }} در انتظار</span>
                    </a>
                @empty
                    <div class="teacher-home__empty"><span>✓</span><b>کار عقب‌افتاده‌ای ثبت نشده</b><small>تکالیف نیازمند بررسی در این بخش نشان داده می‌شوند.</small></div>
                @endforelse
            </section>
        @endif
    </div>
</div>
@endsection
