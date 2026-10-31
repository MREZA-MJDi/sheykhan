@extends('layouts.teacher')
@section('title', 'داشبورد مدرس')
@section('heading', 'خوش آمدی، ' . ($teacher->name ?? 'مدرس'))
@section('content')
    @include('teacher.partials.hero')

    <div class="tp-welcome">
        <div><h2>نمای کلی فضای آموزشی تو</h2><p>اطلاعات زیر از کلاس‌ها، دوره‌ها، تکالیف و جلسات واقعی حسابت خوانده شده‌اند.</p></div>
        <span class="tp-status-inline">{{ $dashboardDate ?? '' }}</span>
    </div>

    <div class="tp-metric-grid">
        @foreach ([
            ['کلاس فعال', $metrics['activeClasses'] ?? 0, '▤'],
            ['دانش‌آموز فعال', $metrics['studentCount'] ?? 0, '♙'],
            ['جلسه این هفته', $metrics['weeklySessions'] ?? 0, '◷'],
            ['در انتظار بررسی', $metrics['pendingReviews'] ?? 0, '✓'],
        ] as [$label, $value, $icon])
            <article class="tp-metric"><span class="tp-ico">{{ $icon }}</span><div><b class="tp-number">{{ \App\Support\PersianUi::digits($value) }}</b><span>{{ $label }}</span></div></article>
        @endforeach
    </div>

    @php
        $tpQuick = [
            ['✓', 'ثبت حضور', route('teacher.attendance.index'), '#22b573', 'attendance.view'],
            ['✎', 'تکلیف جدید', route('teacher.assignments.create'), '#ff9f43', 'assignments.manage'],
            ['◈', 'ساخت آزمون', route('teacher.exams.create'), '#8b5cf6', 'exams.manage'],
            ['✦', 'پیام مستقیم', route('teacher.direct-messages.index'), '#14b8c4', 'parent_messages.manage'],
            ['◉', 'برنامه هفتگی', route('teacher.schedule.index'), '#4a5fe0', 'live_classes.view'],
            ['♙', 'دانش‌آموزان', route('teacher.students.index'), '#f0566b', 'students.view'],
        ];
        $tpQuick = array_values(array_filter($tpQuick, fn ($item) => $tpUser->hasPermission($item[4])));
    @endphp
    <x-teacher.section-head title="اقدام سریع" subtitle="میانبرهای متصل به قابلیت‌های فعال حساب تو" />
    <div class="tp-quick">
        @foreach ($tpQuick as [$icon, $label, $url, $color])
            <a href="{{ $url }}" style="--k:{{ $color }}"><i>{{ $icon }}</i><b>{{ $label }}</b></a>
        @endforeach
    </div>

    <div class="tp-grid tp-grid--main tp-grid--start">
        <x-teacher.card title="برنامه امروز" :href="$tpUser->hasPermission('live_classes.view') ? route('teacher.schedule.index') : null" link-text="برنامه هفتگی">
            @forelse ($todaySessions as $session)
                <div class="tp-schedule-item">
                    <span class="tp-schedule-time">{{ $session['time'] }}</span>
                    <div class="tp-schedule-copy"><strong>{{ $session['title'] }}</strong><small>{{ $session['meta'] }} · {{ $session['status'] }}</small></div>
                    @if (!empty($session['can_join']) && !empty($session['meeting_url']))
                        <a class="tp-btn tp-btn--sm" href="{{ $session['meeting_url'] }}" target="_blank" rel="noopener noreferrer">ورود</a>
                    @endif
                </div>
            @empty
                <div class="tp-empty"><strong>برای امروز جلسه‌ای ثبت نشده است.</strong><span>برنامه کلاس‌ها در این قسمت نمایش داده می‌شود.</span></div>
            @endforelse
        </x-teacher.card>

        <x-teacher.card title="کلاس‌های پیش‌رو" :href="$tpUser->hasPermission('live_classes.view') ? route('teacher.live-classes.index') : null" link-text="جلسات آنلاین">
            @forelse ($upcomingClasses as $item)
                <div class="tp-schedule-item">
                    <span class="tp-schedule-time">{{ \App\Support\PersianUi::time($item->scheduled_at) }}</span>
                    <div class="tp-schedule-copy"><strong>{{ $item->title }}</strong><small>{{ $item->course?->title }} · {{ $item->classroom?->title ?? 'بدون کلاس مشخص' }}</small></div>
                    @if ($item->status === 'live') <span class="tp-status-inline warn">زنده</span> @endif
                    @if ($item->can_join && $item->meeting_url)<a class="tp-link" href="{{ $item->meeting_url }}" target="_blank" rel="noopener noreferrer">ورود</a>@endif
                </div>
            @empty
                <div class="tp-empty"><strong>جلسه آنلاین آینده‌ای ثبت نشده است.</strong></div>
            @endforelse
        </x-teacher.card>
    </div>

    <div class="tp-grid tp-grid--main tp-grid--start">
        <x-teacher.card title="تکالیف و تحویل‌ها" :href="$tpUser->hasPermission('assignments.view') ? route('teacher.assignments.index') : null" link-text="همه تکالیف">
            @forelse ($activities as $assignment)
                <div class="tp-schedule-item">
                    <span class="tp-ico">✎</span>
                    <div class="tp-schedule-copy"><strong>{{ $assignment->title }}</strong><small>{{ $assignment->course?->title }} · {{ $assignment->due_at ? \App\Support\PersianUi::date($assignment->due_at) : 'بدون موعد' }}</small></div>
                    <span class="tp-status-inline {{ $assignment->pending_review_count ? 'warn' : 'ok' }}">{{ \App\Support\PersianUi::digits($assignment->pending_review_count) }} منتظر</span>
                    @if($tpUser->hasPermission('assignments.view'))<a class="tp-link" href="{{ route('teacher.assignments.submissions', $assignment) }}">بررسی</a>@endif
                </div>
            @empty
                <div class="tp-empty"><strong>تکلیفی برای نمایش وجود ندارد.</strong></div>
            @endforelse
        </x-teacher.card>

        <x-teacher.card title="پیشرفت دوره‌ها" :href="$tpUser->hasPermission('courses.view') ? route('teacher.courses.index') : null" link-text="دوره‌ها">
            @forelse ($courseProgress as $course)
                <div class="tp-progress-row"><strong>{{ $course->title }}</strong><x-teacher.progress :value="$course->progress_average" /><b>{{ \App\Support\PersianUi::digits($course->progress_average) }}٪</b></div>
            @empty
                <div class="tp-empty"><strong>دوره فعالی در دسترس نیست.</strong><span>پس از تخصیص دوره به حساب مدرس، شاخص آن اینجا نمایش داده می‌شود.</span></div>
            @endforelse
        </x-teacher.card>
    </div>

    <div class="tp-grid tp-grid--main tp-grid--start">
        <x-teacher.card title="روند مشاهده درس‌ها">
            @php($week = $chart['week'] ?? ['labels' => [], 'values' => []])
            @if (count($week['labels'] ?? []))
                <div class="tp-chart">
                    @foreach ($week['labels'] as $index => $label)
                        @php($value = max(0, min(100, (float) ($week['values'][$index] ?? 0))))
                        <div class="tp-chart-col"><b>{{ \App\Support\PersianUi::digits((int) round($value)) }}٪</b><i style="height:{{ max(3, $value) }}%"></i><span>{{ $label }}</span></div>
                    @endforeach
                </div>
                <p class="tp-muted">میانگین درصد پیشرفت ثبت‌شده برای درس‌های منتشرشده در هفته جاری؛ نبود رکورد به معنی داده صفر است، نه حدس درباره زمان مطالعه.</p>
            @else
                <div class="tp-empty"><strong>هنوز داده‌ای برای نمودار این هفته ثبت نشده است.</strong></div>
            @endif
        </x-teacher.card>

        <x-teacher.card title="دانش‌آموزان با پیشرفت بالاتر" :href="$tpUser->hasPermission('students.view') ? route('teacher.students.index') : null" link-text="فهرست دانش‌آموزان">
            @forelse ($topStudents as $student)
                <div class="tp-schedule-item">
                    <x-teacher.avatar :name="$student->name" :photo="$student->avatar_url" />
                    <div class="tp-schedule-copy"><strong>{{ $student->name }}</strong><small>میانگین پیشرفت درس‌ها</small></div>
                    <b class="tp-number">{{ \App\Support\PersianUi::digits($student->progress_average) }}٪</b>
                </div>
            @empty
                <div class="tp-empty"><strong>داده کافی برای رتبه‌بندی وجود ندارد.</strong></div>
            @endforelse
        </x-teacher.card>
    </div>

    <x-teacher.card title="پیش‌نمایش کلاس‌ها" :href="$tpUser->hasPermission('classrooms.view') ? route('teacher.classrooms.index') : null" link-text="مدیریت کلاس‌ها">
        <div class="tp-grid tp-grid--cards">
            @forelse ($classroomRosterPreviews as $classroom)
                <article class="tp-class-card"><div class="tp-class-card__cover"></div><strong>{{ $classroom['title'] }}</strong><small class="tp-muted">{{ $classroom['course_title'] }}</small>
                    <div class="tp-roster-row" style="margin-top:10px">
                        @foreach ($classroom['students'] as $student)<x-teacher.avatar :name="$student->name" :photo="$student->avatar_url" :size="32" />@endforeach
                        <small class="tp-muted">{{ \App\Support\PersianUi::digits($classroom['student_count']) }} دانش‌آموز فعال</small>
                    </div>
                </article>
            @empty
                <div class="tp-empty"><strong>کلاس فعالی برای نمایش وجود ندارد.</strong></div>
            @endforelse
        </div>
    </x-teacher.card>
@endsection