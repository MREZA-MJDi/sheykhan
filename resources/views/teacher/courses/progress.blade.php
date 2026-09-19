@extends('layouts.teacher')

@section('title','گزارش یادگیری | شیخان')
@section('header-title','گزارش یادگیری')

@section('content')
<div class="teacher-progress-page">
    <section class="teacher-list-hero">
        <div>
            <span class="teacher-kicker">LEARNING ANALYTICS</span>
            <h2>{{ $course->title }}</h2>
            <p>مشاهده کن هر دانش‌آموز چه مقدار از درس‌ها را دیده، آخرین فعالیتش چه زمانی بوده و کدام محتوا هنوز شروع نشده است.</p>
        </div>
        <a href="{{ route('teacher.courses.content', $course) }}" class="teacher-builder-btn primary">ویرایش محتوا</a>
    </section>

    <section class="teacher-progress-matrix">
        <div class="teacher-progress-header">
            <div>
                <span class="teacher-kicker">STUDENTS × LESSONS</span>
                <h3>نقشه‌ی مشاهده</h3>
            </div>
            <div class="teacher-progress-legend">
                <span><i class="seen"></i> شروع شده</span>
                <span><i class="done"></i> کامل</span>
                <span><i class="none"></i> دیده نشده</span>
            </div>
        </div>

        <div class="teacher-progress-table-wrap">
            <table class="teacher-progress-table">
                <thead>
                    <tr>
                        <th class="student-col">دانش‌آموز</th>
                        @foreach($lessons as $lesson)
                            <th title="{{ $lesson->title }}">درس {{ $loop->iteration }}</th>
                        @endforeach
                        <th>میانگین</th>
                        <th>آخرین فعالیت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php($summary = $studentSummary->firstWhere('student.id', $student->id))
                        <tr>
                            <td class="student-col">
                                <a href="{{ route('teacher.students.show', $student) }}">
                                    <span class="teacher-student-card-avatar">{{ mb_substr($student->name, 0, 1) }}</span>
                                    <span><strong>{{ $student->name }}</strong><small>{{ $student->email }}</small></span>
                                </a>
                            </td>

                            @foreach($lessons as $lesson)
                                @php
                                    $row = $progress[$student->id . ':' . $lesson->id] ?? null;
                                    $value = min(100, max(0, (float)($row?->progress_percent ?? 0)));
                                @endphp
                                <td>
                                    <div class="teacher-progress-cell">
                                        <span class="teacher-progress-percent">{{ round($value) }}٪</span>
                                        <div class="teacher-progress-track"><i style="width:{{ $value }}%"></i></div>
                                        <small class="{{ $value >= 100 ? 'done' : ($value > 0 ? 'seen' : 'none') }}">
                                            {{ $value >= 100 ? 'کامل' : ($value > 0 ? 'درحال مشاهده' : 'ندیده') }}
                                        </small>
                                    </div>
                                </td>
                            @endforeach

                            <td>
                                <strong class="teacher-matrix-average">{{ $summary['progress'] ?? 0 }}٪</strong>
                                <small class="teacher-matrix-sub">{{ $summary['watched_lessons'] ?? 0 }} از {{ $lessons->count() }} شروع</small>
                            </td>

                            <td>
                                <span class="teacher-matrix-last">
                                    {{ !empty($summary['last_activity_at']) ? IlluminateSupportCarbon::parse($summary['last_activity_at'])->format('Y/m/d H:i') : 'هنوز فعالیتی نیست' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $lessons->count() + 3 }}" class="teacher-detail-empty large">
                                هنوز دانش‌آموز فعالی برای این دوره ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="teacher-progress-insights">
        <article>
            <span class="teacher-kicker">LESSONS</span>
            <strong>{{ $lessons->count() }}</strong>
            <small>درس در دوره</small>
        </article>
        <article>
            <span class="teacher-kicker">STUDENTS</span>
            <strong>{{ $students->count() }}</strong>
            <small>دانش‌آموز فعال</small>
        </article>
        <article>
            <span class="teacher-kicker">COMPLETIONS</span>
            <strong>{{ $studentSummary->sum('completed_lessons') }}</strong>
            <small>تکمیل ثبت‌شده</small>
        </article>
        <article>
            <span class="teacher-kicker">WATCHED</span>
            <strong>{{ $studentSummary->sum('watched_lessons') }}</strong>
            <small>درس با حداقل یک مشاهده</small>
        </article>
    </section>
</div>
@endsection
