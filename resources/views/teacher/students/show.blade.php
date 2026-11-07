@extends('layouts.teacher')
@section('title', 'پرونده دانش‌آموز | شیخان')
@section('header-title', 'پرونده دانش‌آموز')

@section('content')
@php
    $grade = $student->studentProfile?->gradeRelation?->title
        ?: $student->studentProfile?->grade
        ?: 'ثبت نشده';
    $attendanceCount = $attendance->count();
@endphp

<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">پرونده آموزشی</span>
            <h1 class="teacher-workspace-title">{{ $student->name }}</h1>
            <p class="teacher-workspace-description">
                پایه {{ $grade }} · شماره دانش‌آموزی {{ $student->studentProfile?->student_number ?: 'ثبت نشده' }}
            </p>
        </div>
        <div class="teacher-workspace-actions">
            <a href="{{ route('teacher.students.index') }}" class="teacher-workspace-btn secondary">بازگشت به دانش‌آموزان</a>
        </div>
    </header>

    <section class="teacher-workspace-stats" aria-label="خلاصه عملکرد دانش‌آموز">
        <article class="teacher-workspace-stat"><small>کلاس‌های فعال مشترک</small><strong>{{ App\Support\PersianUi::digits($classrooms->count()) }}</strong><span>کلاس‌های تحت تدریس شما</span></article>
        <article class="teacher-workspace-stat"><small>فعالیت درس</small><strong>{{ App\Support\PersianUi::digits($progress_count) }}</strong><span>رکوردهای ثبت‌شده مشاهده درس</span></article>
        <article class="teacher-workspace-stat"><small>ارسال تکلیف</small><strong>{{ App\Support\PersianUi::digits($submission_count) }}</strong><span>در دوره‌های تحت تدریس شما</span></article>
        <article class="teacher-workspace-stat"><small>تلاش آزمون</small><strong>{{ App\Support\PersianUi::digits($attempt_count) }}</strong><span>در آزمون‌های شما</span></article>
    </section>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>کلاس‌های این دانش‌آموز</h2><p>فقط کلاس‌هایی نمایش داده می‌شوند که هم عضویت دانش‌آموز در آن فعال است و هم به شما تخصیص دارند.</p></div></div>
        <div class="teacher-workspace-grid" style="padding:14px">
            @foreach($classrooms as $classroom)
                <article class="teacher-workspace-stat">
                    <strong>{{ $classroom->title }}</strong>
                    <span>{{ $classroom->course?->title ?: 'بدون دوره' }}</span>
                    <span>کد کلاس: {{ $classroom->code }}</span>
                </article>
            @endforeach
        </div>
    </section>

    <section class="teacher-workspace-grid">
        <article class="teacher-workspace-card">
            <div class="teacher-workspace-card-head"><div><h2>آخرین فعالیت‌های درس</h2><p>۱۰ رکورد اخیر از درس‌های دوره‌های تحت تدریس شما</p></div></div>
            <div class="teacher-workspace-list">
                @forelse($progress as $item)
                    <div class="teacher-workspace-item">
                        <div class="teacher-workspace-item-main">
                            <strong>{{ $item->lesson?->title ?: 'درس حذف‌شده' }}</strong>
                            <small>{{ $item->last_watched_at ? App\Support\PersianUi::date($item->last_watched_at) . ' · ' . App\Support\PersianUi::time($item->last_watched_at) : 'زمان مشاهده ثبت نشده' }}</small>
                        </div>
                        <span class="teacher-workspace-chip">{{ App\Support\PersianUi::digits((int) $item->progress_percent) }}٪</span>
                    </div>
                @empty
                    <div class="teacher-workspace-empty">هنوز فعالیت درسی ثبت نشده است.</div>
                @endforelse
            </div>
        </article>

        <article class="teacher-workspace-card">
            <div class="teacher-workspace-card-head"><div><h2>آخرین حضور و غیاب</h2><p>۱۰ ثبت اخیر در کلاس‌های مشترک</p></div></div>
            <div class="teacher-workspace-list">
                @forelse($attendance as $record)
                    @php
                        $attendanceLabel = match($record->status) {
                            'present' => 'حاضر',
                            'absent' => 'غایب',
                            'late' => 'با تأخیر',
                            'excused' => 'موجه',
                            default => 'ثبت‌شده',
                        };
                    @endphp
                    <div class="teacher-workspace-item">
                        <div class="teacher-workspace-item-main">
                            <strong>{{ $record->classroom?->title ?: 'کلاس' }}</strong>
                            <small>{{ App\Support\PersianUi::date($record->attendance_date) }}</small>
                        </div>
                        <span class="teacher-workspace-chip">{{ $attendanceLabel }}</span>
                    </div>
                @empty
                    <div class="teacher-workspace-empty">سابقه حضور و غیاب ثبت نشده است.</div>
                @endforelse
            </div>
        </article>

        <article class="teacher-workspace-card">
            <div class="teacher-workspace-card-head"><div><h2>آخرین تکالیف</h2><p>ارسال‌های ثبت‌شده برای تکالیف شما</p></div></div>
            <div class="teacher-workspace-list">
                @forelse($submissions as $submission)
                    <div class="teacher-workspace-item">
                        <div class="teacher-workspace-item-main">
                            <strong>{{ $submission->assignment?->title ?: 'تکلیف حذف‌شده' }}</strong>
                            <small>{{ $submission->submitted_at ? App\Support\PersianUi::date($submission->submitted_at) : 'ارسال نشده' }}</small>
                        </div>
                        <span class="teacher-workspace-chip {{ $submission->graded_at ? 'success' : 'warning' }}">
                            {{ $submission->graded_at ? 'تصحیح‌شده · ' . App\Support\PersianUi::digits($submission->score ?? 0) : 'نیازمند تصحیح' }}
                        </span>
                    </div>
                @empty
                    <div class="teacher-workspace-empty">ارسالی برای تکالیف شما ثبت نشده است.</div>
                @endforelse
            </div>
        </article>

        <article class="teacher-workspace-card">
            <div class="teacher-workspace-card-head"><div><h2>آخرین آزمون‌ها</h2><p>تلاش‌های ثبت‌شده در آزمون‌های شما</p></div></div>
            <div class="teacher-workspace-list">
                @forelse($attempts as $attempt)
                    <div class="teacher-workspace-item">
                        <div class="teacher-workspace-item-main">
                            <strong>{{ $attempt->exam?->title ?: 'آزمون حذف‌شده' }}</strong>
                            <small>{{ $attempt->submitted_at ? App\Support\PersianUi::date($attempt->submitted_at) : 'در حال انجام' }}</small>
                        </div>
                        <span class="teacher-workspace-chip {{ $attempt->status === 'graded' ? 'success' : 'warning' }}">
                            {{ $attempt->status === 'graded' ? 'نمره ' . App\Support\PersianUi::digits($attempt->score ?? 0) : 'در انتظار بررسی' }}
                        </span>
                    </div>
                @empty
                    <div class="teacher-workspace-empty">تلاشی در آزمون‌های شما ثبت نشده است.</div>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
