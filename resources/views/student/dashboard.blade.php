@extends('layouts.student')

@section('title', 'خانه یادگیری من')
@section('header-title', 'خانه یادگیری من')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'صبح بخیر' : ($hour < 17 ? 'روز بخیر' : 'عصر بخیر');
    $classes = collect($upcomingLiveClasses ?? []);
    $pending = collect($assignments ?? [])->filter(fn ($item) => empty($item->submitted_at));
    $courseProgress = (int) ($overallProgress ?? 0);
@endphp
<section class="page" id="dashboard">
    <div class="hero">
        <div class="av" aria-hidden="true">
            @if($studentAvatarUrl ?? null)
                <img data-avatar-image src="{{ $studentAvatarUrl }}" alt="تصویر پروفایل {{ $student->name }}" width="160" height="160" loading="eager" decoding="async" fetchpriority="high">
                <svg data-avatar-fallback hidden viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
            @else
                <svg data-avatar-fallback viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
            @endif
        </div>
        <div class="txt">
            <h2>{{ $greeting }}، {{ $student->name }}!</h2>
            <p>مسیر یادگیری‌ات را ادامه بده؛ پیشرفت دوره‌ها، کلاس‌های پیش‌رو و تکالیفت اینجا جمع شده‌اند.</p>
            @if($nextLesson)
                <a class="btn" href="{{ route('student.lessons.show', $nextLesson->id) }}">ادامه یادگیری: {{ $nextLesson->title }}</a>
            @else
                <a class="btn" href="{{ route('student.courses.index') }}">مشاهده دوره‌های من</a>
            @endif
        </div>
    </div>

    <div class="stats">
        <a class="st s1" href="{{ route('student.courses.index') }}">
            <div class="ic"><svg class="i" aria-hidden="true"><use href="#i-book"/></svg></div>
            <small>دوره‌های فعال</small>
            <b>{{ \App\Support\PersianUi::digits($activeCourseCount) }}</b>
            <span>دوره‌های قابل دسترسی تو</span>
        </a>
        <a class="st s2" href="{{ route('student.assignments.index') }}">
            <div class="ic"><svg class="i" aria-hidden="true"><use href="#i-task"/></svg></div>
            <small>تکالیف ارسال‌نشده</small>
            <b>{{ \App\Support\PersianUi::digits($pendingAssignments) }}</b>
            <span>تکالیف قابل ارسال</span>
        </a>
        <a class="st s3" href="{{ route('student.live-classes.index') }}">
            <div class="ic"><svg class="i" aria-hidden="true"><use href="#i-video"/></svg></div>
            <small>کلاس‌های پیش‌رو</small>
            <b>{{ \App\Support\PersianUi::digits($classes->count()) }}</b>
            <span>در بازهٔ برنامه‌ریزی‌شده</span>
        </a>
        <a class="st s4" href="{{ route('student.achievements.index') }}">
            <div class="ic"><svg class="i" aria-hidden="true"><use href="#i-trophy"/></svg></div>
            <small>دستاوردهای ثبت‌شده</small>
            <b>{{ \App\Support\PersianUi::digits($achievementCount) }}</b>
            <span>دستاوردهای منتشرشده</span>
        </a>
    </div>

    <div class="row">
        <div class="card lk">
            <div class="chead">
                <h2><a class="stretch" href="{{ route('student.live-classes.index') }}">کلاس‌های پیش‌رو</a></h2>
                <a class="pill" href="{{ route('student.live-classes.index') }}">همه</a>
            </div>
            @forelse($classes->take(4) as $class)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-video"/></svg></div>
                    <div>
                        <b>{{ $class->title }}</b>
                        <span>{{ $class->course_title ?? 'کلاس آنلاین' }} · {{ \App\Support\PersianUi::dateTime($class->scheduled_at) }}</span>
                    </div>
                    @if(!empty($class->can_join))
                        <a class="pl student-dashboard-join" href="{{ route('student.live-classes.join', $class->id) }}" aria-label="ورود به جلسه آنلاین {{ $class->title }}">
                            <svg class="i" aria-hidden="true"><use href="#i-play"/></svg><span>ورود</span>
                        </a>
                    @else
                        <a class="pl" href="{{ route('student.live-classes.index') }}" aria-label="دیدن برنامه کلاس‌ها"><svg class="i" aria-hidden="true"><use href="#i-cal"/></svg></a>
                    @endif
                </div>
            @empty
                <div class="empty">در حال حاضر کلاس پیش‌رویی در برنامه‌ات ثبت نشده است.</div>
            @endforelse
        </div>
        <div class="card lk">
            <div class="chead">
                <h2><a class="stretch" href="{{ route('student.assignments.index') }}">تکالیف نزدیک</a></h2>
                <a class="pill" href="{{ route('student.assignments.index') }}">همه</a>
            </div>
            @forelse(collect($assignments)->take(4) as $assignment)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-task"/></svg></div>
                    <div>
                        <b>{{ $assignment->title }}</b>
                        <span>
                            @if($assignment->due_at)
                                مهلت: {{ \App\Support\PersianUi::date($assignment->due_at) }}
                            @else
                                بدون مهلت مشخص
                            @endif
                            ·
                            {{ $assignment->submitted_at ? 'ارسال‌شده' : 'در انتظار ارسال' }}
                        </span>
                    </div>
                    <a class="pl" href="{{ route('student.assignments.show', $assignment->id) }}" aria-label="مشاهده تکلیف"><svg class="i" aria-hidden="true"><use href="#i-play"/></svg></a>
                </div>
            @empty
                <div class="empty">تکلیف قابل نمایشی برای دوره‌هایت وجود ندارد.</div>
            @endforelse
        </div>
    </div>

    <div class="row">
        <div class="card">
            <div class="chead">
                <h2>پیشرفت یادگیری</h2>
                <a class="pill" href="{{ route('student.courses.index') }}">دوره‌ها</a>
            </div>
            <div class="goalbox">
                <div class="goal" style="--goal-progress:{{ max(0, min(100, $courseProgress)) }}%" role="img" aria-label="پیشرفت {{ $courseProgress }} درصد">
                    <b>{{ \App\Support\PersianUi::digits($courseProgress) }}٪</b>
                </div>
                <div>
                    <p>تا اینجا {{ \App\Support\PersianUi::digits($completedLessonsCount) }} درس از {{ \App\Support\PersianUi::digits($totalLessonsCount) }} درس تکمیل شده است.</p>
                    <p>{{ \App\Support\PersianUi::digits($studyMinutesLast7Days) }} دقیقه مطالعه در ۷ روز اخیر ثبت شده.</p>
                    <p>پیوستگی مطالعه: {{ \App\Support\PersianUi::digits($studyStreak) }} روز</p>
                </div>
            </div>
            <div class="chead" style="margin-top:18px"><h2>دوره‌های من</h2><a class="pill" href="{{ route('student.courses.index') }}">مشاهده همه</a></div>
            @forelse(collect($courses)->take(3) as $course)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-book"/></svg></div>
                    <div>
                        <b>{{ $course->title }}</b>
                        <span>{{ \App\Support\PersianUi::digits($course->completed_lessons ?? 0) }} از {{ \App\Support\PersianUi::digits($course->total_lessons ?? 0) }} درس</span>
                        <div class="prog" aria-label="پیشرفت دوره">
                            <i style="width:{{ max(0, min(100, (float) ($course->learning_progress ?? 0))) }}%"></i>
                        </div>
                    </div>
                    <a class="pl" href="{{ route('student.courses.show', $course->id) }}" aria-label="ادامه دوره {{ $course->title }}"><svg class="i" aria-hidden="true"><use href="#i-play"/></svg></a>
                </div>
            @empty
                <div class="empty">هنوز دورهٔ فعالی به حساب تو متصل نشده است.</div>
            @endforelse
        </div>

        <div class="card">
            <div class="chead">
                <h2>آخرین نتیجه‌ها</h2>
                <a class="pill" href="{{ route('student.results.index') }}">همه نتیجه‌ها</a>
            </div>
            @forelse(collect($recentResults)->take(5) as $result)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="{{ $result->type === 'exam' ? '#i-exam' : '#i-task' }}"/></svg></div>
                    <div>
                        <b>{{ $result->title }}</b>
                        <span>{{ $result->status_label }} · {{ $result->occurred_at ? \App\Support\PersianUi::date($result->occurred_at) : '—' }}</span>
                    </div>
                    @if($result->score !== null)
                        <span class="tag">{{ \App\Support\PersianUi::number($result->score, 1) }}</span>
                    @endif
                </div>
            @empty
                <div class="empty">نتیجهٔ تصحیح‌شده‌ای برای نمایش وجود ندارد.</div>
            @endforelse

            <div class="chead" style="margin-top:18px">
                <h2>منابع آموزشی</h2>
                <a class="pill" href="{{ route('student.resources.index') }}">همه</a>
            </div>
            @forelse(collect($resources)->take(4) as $resource)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-file"/></svg></div>
                    <div><b>{{ $resource->title }}</b><span>{{ $resource->course?->title ?? $resource->academy?->name ?? 'منبع آموزشی' }}</span></div>
                    <a class="pl" href="{{ route('student.resources.view', $resource->id) }}" aria-label="مشاهده منبع {{ $resource->title }}"><svg class="i" aria-hidden="true"><use href="#i-play"/></svg></a>
                </div>
            @empty
                <div class="empty">هنوز منبع آموزشی‌ای برای نمایش در دسترس نیست.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
