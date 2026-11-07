@extends('layouts.teacher')
@section('title', 'گزارش عملکرد آموزشی | شیخان')
@section('header-title', 'گزارش عملکرد')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">مرکز تحلیل و ارزیابی</span>
            <h1 class="teacher-workspace-title">گزارش جامع عملکرد آموزشی</h1>
            <p class="teacher-workspace-description">
                برآیند جامع پیشرفت یادگیری، نرخ حضور، وضعیت تکالیف، آزمون‌ها و کلاس‌های آنلاین تحت هدایت شما · تاریخ گزارش: {{ $reportDate }}
            </p>
        </div>
        <div class="teacher-workspace-actions">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-workspace-btn secondary">داشبورد مدرس</a>
            <a href="{{ route('teacher.courses.index') }}" class="teacher-workspace-btn primary">دوره‌های من</a>
        </div>
    </header>

    {{-- KPI Stat Cards --}}
    <section class="teacher-workspace-stats">
        <article class="teacher-workspace-stat">
            <span>دانش‌آموزان فعال</span>
            <strong>{{ \App\Support\PersianUi::digits($metrics['studentCount']) }}</strong>
            <small>در تمام کلاس‌های تحت تدریس</small>
        </article>
        <article class="teacher-workspace-stat">
            <span>میانگین پیشرفت یادگیری</span>
            <strong>{{ \App\Support\PersianUi::digits($metrics['overallProgressAverage']) }}٪</strong>
            <small>مشاهده درس‌های منتشرشده</small>
        </article>
        <article class="teacher-workspace-stat">
            <span>نرخ حضور و غیاب کلی</span>
            <strong>{{ $metrics['overallAttendanceRate'] !== null ? \App\Support\PersianUi::digits($metrics['overallAttendanceRate']) . '٪' : '—' }}</strong>
            <small>بر اساس زمان حضور واقعی</small>
        </article>
        <article class="teacher-workspace-stat">
            <span>کلاس‌های آنلاین تکمیل‌شده</span>
            <strong>{{ \App\Support\PersianUi::digits($metrics['completedLiveClassesCount']) }}</strong>
            <small>از {{ \App\Support\PersianUi::digits($metrics['totalLiveClassesCount']) }} جلسه زمان‌بندی‌شده</small>
        </article>
        <article class="teacher-workspace-stat">
            <span>منتظر ارزیابی و نمره‌دهی</span>
            <strong>{{ \App\Support\PersianUi::digits($metrics['pendingReviews']) }}</strong>
            <small>{{ \App\Support\PersianUi::digits($metrics['pendingAssignmentReviews']) }} تکلیف + {{ \App\Support\PersianUi::digits($metrics['pendingExamReviews']) }} آزمون</small>
        </article>
    </section>

    {{-- Interactive Performance Trend Chart --}}
    <section class="c" data-dashboard-chart data-chart='@json($chart)' style="margin:0">
        <h3>
            روند فعالیت و پیشرفت دانش‌آموزان
            <span class="tabs" style="margin:0">
                <button type="button" class="on" data-chart-range="week" aria-pressed="true">هفته</button>
                <button type="button" data-chart-range="month" aria-pressed="false">ماه</button>
            </span>
        </h3>
        <p style="font-size:12px;color:var(--mu);margin:-6px 0 12px">
            میانگین پیشرفت ثبت‌شده برای درس‌های مشاهده‌شده توسط دانش‌آموزان شما
        </p>
        <div class="teacher-workspace-actions-row" style="margin:0 0 12px">
            <a href="{{ route('teacher.students.index') }}" class="teacher-workspace-link">مشاهده فهرست دانش‌آموزان و ورود به پرونده‌ها ←</a>
        </div>
        <div class="bars" data-chart-bars aria-label="نمودار درصد پیشرفت">
            @foreach(($chart['week']['values'] ?? []) as $index => $value)
                <div>
                    <b>{{ \App\Support\PersianUi::digits(round(max(0, min(100, (float) $value)))) }}٪</b>
                    <i style="height:{{ max(0, min(100, (float) $value)) }}%"></i>
                    <small>{{ $chart['week']['labels'][$index] ?? '' }}</small>
                </div>
            @endforeach
        </div>
        <p class="muted-note" data-chart-empty @if(collect($chart['week']['values'] ?? [])->contains(fn($val) => (float) $val > 0)) hidden @endif>
            برای این بازه هنوز داده‌ای جهت رسم نمودار ثبت نشده است.
        </p>
    </section>

    {{-- Course Performance Table --}}
    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div>
                <h2>عملکرد دوره‌های آموزشی</h2>
                <p>تعداد دانش‌آموزان، پیشرفت محتوا، تکالیف و جلسات آنلاین به تفکیک دوره</p>
            </div>
            <span class="teacher-workspace-chip">{{ \App\Support\PersianUi::digits($courseReports->count()) }} دوره</span>
        </div>
        <div class="teacher-workspace-table-wrap">
            <table class="teacher-workspace-table">
                <thead>
                    <tr>
                        <th>عنوان دوره</th>
                        <th style="text-align:center">دانش‌آموز فعال</th>
                        <th style="text-align:center">سرفصل / درس</th>
                        <th>میانگین پیشرفت</th>
                        <th style="text-align:center">تکلیف (ارسال‌شده)</th>
                        <th style="text-align:center">آزمون</th>
                        <th style="text-align:center">کلاس آنلاین</th>
                        <th style="text-align:center">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courseReports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->title }}</strong>
                                <span style="display:block;margin-top:3px;color:var(--mu);font-size:11px">
                                    سطح: {{ $report->level ?? 'عمومی' }} · {{ $report->is_published ? 'منتشرشده' : 'پیش‌نویس' }}
                                </span>
                            </td>
                            <td style="text-align:center">
                                <strong>{{ \App\Support\PersianUi::digits($report->student_count) }}</strong>
                            </td>
                            <td style="text-align:center">
                                <span>{{ \App\Support\PersianUi::digits($report->sections_count) }} فصل · {{ \App\Support\PersianUi::digits($report->lessons_count) }} درس</span>
                            </td>
                            <td style="min-width:140px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <span style="flex:1;height:6px;border-radius:99px;background:var(--pl);overflow:hidden">
                                        <span style="display:block;height:100%;width:{{ min(100, max(0, $report->progress_average)) }}%;background:var(--pri)"></span>
                                    </span>
                                    <strong style="font-size:11px;min-width:32px">{{ \App\Support\PersianUi::digits($report->progress_average) }}٪</strong>
                                </div>
                            </td>
                            <td style="text-align:center">
                                <span>{{ \App\Support\PersianUi::digits($report->assignments_count) }} ({{ \App\Support\PersianUi::digits($report->submissions_count) }})</span>
                            </td>
                            <td style="text-align:center">
                                <span>{{ \App\Support\PersianUi::digits($report->exams_count) }}</span>
                            </td>
                            <td style="text-align:center">
                                <span>{{ \App\Support\PersianUi::digits($report->live_classes_count) }}</span>
                            </td>
                            <td style="text-align:center">
                                <a href="{{ route('teacher.courses.progress', $report->id) }}" class="teacher-workspace-btn secondary" style="min-height:30px;padding:4px 10px;font-size:11px">
                                    مانیتور پیشرفت
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="teacher-workspace-empty">
                                    <strong>دوره‌ای برای نمایش وجود ندارد.</strong>
                                    پس از تخصیص دوره به حساب شما، گزارش تفصیلی دوره‌ها در این جدول نمایش داده می‌شود.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Classrooms & Attendance Section --}}
    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div>
                <h2>کلاس‌ها و آمار حضور و غیاب</h2>
                <p>شاخص حضور دانش‌آموزان بر اساس جلسات برگزارشده و دقایق واقعی حضور</p>
            </div>
            <span class="teacher-workspace-chip">{{ \App\Support\PersianUi::digits($classroomReports->count()) }} کلاس فعال</span>
        </div>
        <div class="teacher-workspace-grid">
            @forelse($classroomReports as $classroom)
                <article style="border:1px solid var(--ln);border-radius:16px;padding:14px;background:var(--sf);display:grid;gap:10px">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                        <div>
                            <strong style="font-size:14px;color:var(--tx)">{{ $classroom->title }}</strong>
                            <small style="display:block;margin-top:2px;color:var(--mu)">{{ $classroom->course_title }}</small>
                        </div>
                        <span class="teacher-workspace-chip">
                            {{ \App\Support\PersianUi::digits($classroom->student_count) }} دانش‌آموز
                        </span>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;text-align:center;margin-top:4px">
                        <div style="padding:8px;border-radius:10px;background:var(--pl)">
                            <strong style="display:block;font-size:13px;color:var(--pri)">
                                {{ $classroom->attendance_rate !== null ? \App\Support\PersianUi::digits($classroom->attendance_rate) . '٪' : '—' }}
                            </strong>
                            <small style="font-size:10px;color:var(--mu)">نرخ حضور</small>
                        </div>
                        <div style="padding:8px;border-radius:10px;background:var(--pl)">
                            <strong style="display:block;font-size:13px;color:var(--tx)">
                                {{ \App\Support\PersianUi::digits($classroom->measured_sessions) }}
                            </strong>
                            <small style="font-size:10px;color:var(--mu)">جلسه ارزیابی‌شده</small>
                        </div>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-top:2px;padding-top:8px;border-top:1px solid var(--ln)">
                        <span style="font-size:11px;color:var(--mu)">
                            {{ \App\Support\PersianUi::digits($classroom->attended_minutes) }} از {{ \App\Support\PersianUi::digits($classroom->scheduled_minutes) }} دقیقه
                        </span>
                        <div style="display:flex;flex-wrap:wrap;gap:10px">
                            <a href="{{ route('teacher.students.index', ['classroom_id' => $classroom->id]) }}" style="font-size:11px;font-weight:700;color:var(--pri)">
                                جزئیات دانش‌آموزان کلاس ←
                            </a>
                            <a href="{{ route('teacher.attendance.index', ['classroom_id' => $classroom->id]) }}" style="font-size:11px;font-weight:700;color:var(--pri)">
                                تاریخچه حضور و غیاب
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="teacher-workspace-empty" style="grid-column:1/-1">
                    <strong>هنوز کلاسی ثبت نشده است.</strong>
                    کلاس‌های فعال شما پس از ایجاد و تخصیص در این بخش قرار می‌گیرند.
                </div>
            @endforelse
        </div>
    </section>

    {{-- Top Learners & Students Needing Follow-up --}}
    <div class="g g3">
        {{-- Top Students --}}
        <section class="c">
            <h3>
                برترین دانش‌آموزان در یادگیری
                <a href="{{ route('teacher.students.index') }}">همه دانش‌آموزان ←</a>
            </h3>
            @forelse($topStudents as $student)
                <div class="row">
                    <span class="av" style="width:36px;height:36px;font-size:13px;flex-shrink:0">
                        @if(!empty($student->avatar_url))
                            <img src="{{ $student->avatar_url }}" alt="" width="36" height="36" style="border-radius:50%;object-fit:cover">
                        @else
                            {{ mb_substr($student->name, 0, 1) }}
                        @endif
                    </span>
                    <span class="t">
                        <b><a href="{{ route('teacher.students.show', $student->id) }}" style="color:inherit;text-decoration:underline;text-decoration-color:var(--ln);text-underline-offset:3px">{{ $student->name }}</a></b>
                        <small>{{ \App\Support\PersianUi::digits($student->courses_count) }} دوره فعال</small>
                    </span>
                    <div style="text-align:left;min-width:65px">
                        <span class="pill gr" style="font-weight:700">
                            {{ \App\Support\PersianUi::digits($student->progress_average) }}٪
                        </span>
                    </div>
                </div>
            @empty
                <div class="emp">
                    <i>✦</i>
                    <b>داده‌ای برای برترین‌ها نیست</b>
                    <small>با مشاهده و تکمیل درس‌ها توسط دانش‌آموزان، رتبه‌بندی محاسبه می‌شود.</small>
                </div>
            @endforelse
        </section>

        {{-- Students Needing Educational Follow-up --}}
        <section class="c">
            <h3>
                دانش‌آموزان نیازمند توجه و پیگیری
                <a href="{{ route('teacher.direct-messages.index') }}">ارسال پیام ←</a>
            </h3>
            @forelse($studentsNeedingFollowup as $student)
                <div class="row">
                    <span class="av" style="width:36px;height:36px;font-size:13px;flex-shrink:0">
                        @if(!empty($student->avatar_url))
                            <img src="{{ $student->avatar_url }}" alt="" width="36" height="36" style="border-radius:50%;object-fit:cover">
                        @else
                            {{ mb_substr($student->name, 0, 1) }}
                        @endif
                    </span>
                    <span class="t">
                        <b><a href="{{ route('teacher.students.show', $student->id) }}" style="color:inherit;text-decoration:underline;text-decoration-color:var(--ln);text-underline-offset:3px">{{ $student->name }}</a></b>
                        <small>پیشرفت یادگیری: کمتر از ۲۵٪</small>
                    </span>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span class="pill or" style="font-weight:700">
                            {{ \App\Support\PersianUi::digits($student->progress_average) }}٪
                        </span>
                        <a href="{{ route('teacher.direct-messages.index') }}" class="btn s" title="ارسال پیام به دانش‌آموز یا ولی" style="padding:4px 8px;font-size:11px">
                            پیام
                        </a>
                    </div>
                </div>
            @empty
                <div class="emp">
                    <i>✓</i>
                    <b>همه دانش‌آموزان در مسیر پیشرفت مناسب هستند</b>
                    <small>دانش‌آموزی با پیشرفت بحرانی یا پایین مشاهده نشد.</small>
                </div>
            @endforelse
        </section>
    </div>

    {{-- Assessment & Grading Overview --}}
    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div>
                <h2>خلاصه وضعیت تکالیف و ارزیابی‌ها</h2>
                <p>مرور وضعیت پاسخ‌های دریافت شده، تصحیح‌های معوق و نمرات</p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('teacher.assignments.index') }}" class="teacher-workspace-btn secondary" style="min-height:32px;padding:4px 10px;font-size:11px">مدیریت تکالیف</a>
                <a href="{{ route('teacher.exams.index') }}" class="teacher-workspace-btn secondary" style="min-height:32px;padding:4px 10px;font-size:11px">مدیریت آزمون‌ها</a>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-top:6px">
            <div style="padding:14px;border-radius:14px;border:1px solid var(--ln);background:var(--pl)">
                <span style="font-size:11px;color:var(--mu);display:block">کل تکالیف تعریف‌شده</span>
                <strong style="font-size:20px;color:var(--tx);display:block;margin-top:4px">{{ \App\Support\PersianUi::digits($metrics['totalAssignmentsCount']) }}</strong>
                <small style="color:var(--mu);font-size:11px">{{ \App\Support\PersianUi::digits($metrics['gradedAssignmentSubmissions']) }} پاسخ نمره‌دهی‌شده</small>
            </div>
            <div style="padding:14px;border-radius:14px;border:1px solid var(--ln);background:var(--pl)">
                <span style="font-size:11px;color:var(--mu);display:block">تکالیف در انتظار تصحیح</span>
                <strong style="font-size:20px;color:{{ $metrics['pendingAssignmentReviews'] > 0 ? 'var(--or)' : 'var(--tx)' }};display:block;margin-top:4px">{{ \App\Support\PersianUi::digits($metrics['pendingAssignmentReviews']) }}</strong>
                <small style="color:var(--mu);font-size:11px">نیازمند بازبینی و ثبت بازخورد</small>
            </div>
            <div style="padding:14px;border-radius:14px;border:1px solid var(--ln);background:var(--pl)">
                <span style="font-size:11px;color:var(--mu);display:block">کل آزمون‌های برگزارشده</span>
                <strong style="font-size:20px;color:var(--tx);display:block;margin-top:4px">{{ \App\Support\PersianUi::digits($metrics['totalExamsCount']) }}</strong>
                <small style="color:var(--mu);font-size:11px">{{ \App\Support\PersianUi::digits($metrics['gradedExamAttempts']) }} آزمون نهایی‌شده</small>
            </div>
            <div style="padding:14px;border-radius:14px;border:1px solid var(--ln);background:var(--pl)">
                <span style="font-size:11px;color:var(--mu);display:block">میانگین نمرات آزمون</span>
                <strong style="font-size:20px;color:var(--pri);display:block;margin-top:4px">
                    {{ $metrics['examAverage'] !== null ? \App\Support\PersianUi::digits($metrics['examAverage']) : '—' }}
                </strong>
                <small style="color:var(--mu);font-size:11px">
                    {{ \App\Support\PersianUi::digits($metrics['pendingExamReviews']) }} آزمون در انتظار بررسی تشریحی
                </small>
            </div>
        </div>
    </section>
</div>
@endsection

