@extends('layouts.teacher')
@section('title','حضور و غیاب | شیخان')
@section('header-title','حضور و غیاب')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">پیگیری و سوابق</span>
            <h1 class="teacher-workspace-title">حضور و غیاب</h1>
            <p class="teacher-workspace-description">ثبت روزانه، جست‌وجوی سوابق و گزارش‌گیری از حضور دانش‌آموزان کلاس‌های تحت مدیریت.</p>
        </div>
        <a href="{{ route('teacher.classrooms.index') }}" class="teacher-workspace-btn secondary">کلاس‌ها</a>
    </header>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div><h2>ثبت حضور امروز</h2><p>برای ثبت یا اصلاح وضعیت روزانه، کلاس موردنظر را انتخاب کن.</p></div>
        </div>
        <div class="teacher-workspace-grid" style="padding:14px">
            @forelse($classrooms as $classroom)
                <article class="teacher-workspace-card">
                    <div class="teacher-workspace-card-body">
                        <div class="teacher-workspace-card-title">{{ $classroom->title }}</div>
                        <div class="teacher-workspace-card-subtitle">{{ $classroom->course?->title ?: 'بدون دوره' }}</div>
                        <div class="teacher-workspace-mini-stats">
                            <div class="teacher-workspace-mini-stat"><strong>{{ App\Support\PersianUi::digits($classroom->active_students_count) }}</strong><span>دانش‌آموز</span></div>
                            <div class="teacher-workspace-mini-stat"><strong>{{ $classroom->capacity ? App\Support\PersianUi::digits($classroom->capacity) : '—' }}</strong><span>ظرفیت</span></div>
                            <div class="teacher-workspace-mini-stat"><strong>{{ App\Support\PersianUi::digits($classroom->attendance_today_count ?? 0) }}</strong><span>ثبت امروز</span></div>
                        </div>
                        <div class="teacher-workspace-actions-row">
                            <a href="{{ route('teacher.classrooms.attendance.edit',$classroom) }}" class="teacher-workspace-link primary">ثبت یا اصلاح حضور امروز</a>
                            <a href="{{ route('teacher.attendance.index', ['classroom_id' => $classroom->id]) }}" class="teacher-workspace-link">تاریخچه این کلاس</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="teacher-workspace-empty" style="grid-column:1/-1"><strong>کلاسی برای حضور و غیاب وجود ندارد.</strong>ابتدا یک کلاس تحت مدیریت خودت بساز.</div>
            @endforelse
        </div>
        @if($classrooms->hasPages())<div class="teacher-workspace-pagination">{{ $classrooms->links('components.navigation.pagination') }}</div>@endif
    </section>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div><h2>تاریخچه حضور و غیاب</h2><p>سوابق به‌صورت صفحه‌بندی‌شده نمایش داده می‌شوند؛ فیلترها را می‌توانی ترکیب کنی.</p></div>
            <span class="teacher-workspace-chip">{{ App\Support\PersianUi::digits($historySummary->records_count ?? 0) }} رکورد</span>
        </div>

        <form method="GET" action="{{ route('teacher.attendance.index') }}" class="teacher-workspace-filters">
            <label>کلاس
                <select name="classroom_id">
                    <option value="">همه کلاس‌ها</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected((string)($filters['classroom_id'] ?? '') === (string)$classroom->id)>{{ $classroom->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>از تاریخ
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            </label>
            <label>تا تاریخ
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            </label>
            <label>وضعیت
                <select name="status">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach(['present' => 'حاضر', 'late' => 'با تأخیر', 'absent' => 'غایب', 'excused' => 'موجه'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="teacher-workspace-filter-actions">
                <button type="submit" class="teacher-workspace-btn primary">اعمال فیلتر</button>
                <a href="{{ route('teacher.attendance.index') }}" class="teacher-workspace-btn secondary">پاک‌کردن</a>
            </div>
        </form>

        <div class="teacher-workspace-stats" style="padding:0 14px 14px">
            <article class="teacher-workspace-stat"><span>حاضر</span><strong>{{ App\Support\PersianUi::digits($historySummary->present_count ?? 0) }}</strong></article>
            <article class="teacher-workspace-stat"><span>با تأخیر</span><strong>{{ App\Support\PersianUi::digits($historySummary->late_count ?? 0) }}</strong></article>
            <article class="teacher-workspace-stat"><span>غایب</span><strong>{{ App\Support\PersianUi::digits($historySummary->absent_count ?? 0) }}</strong></article>
            <article class="teacher-workspace-stat"><span>موجه</span><strong>{{ App\Support\PersianUi::digits($historySummary->excused_count ?? 0) }}</strong></article>
        </div>

        <div class="teacher-workspace-table-wrap">
            <table class="teacher-workspace-table">
                <thead><tr><th>تاریخ</th><th>دانش‌آموز</th><th>کلاس</th><th>وضعیت</th><th>دقیقه حضور</th><th>عملیات</th></tr></thead>
                <tbody>
                @forelse($attendanceHistory as $record)
                    <tr>
                        <td>{{ App\Support\PersianUi::date($record->attendance_date) }}</td>
                        <td><a href="{{ route('teacher.students.show', $record->student_id) }}">{{ $record->student?->name ?? 'دانش‌آموز حذف‌شده' }}</a></td>
                        <td>{{ $record->classroom?->title ?? '—' }}</td>
                        <td>
                            @php($statusLabels = ['present' => 'حاضر', 'late' => 'با تأخیر', 'absent' => 'غایب', 'excused' => 'موجه'])
                            <span class="teacher-workspace-chip">{{ $statusLabels[$record->status] ?? $record->status }}</span>
                        </td>
                        <td>{{ $record->attended_minutes !== null ? App\Support\PersianUi::digits($record->attended_minutes) : '—' }} / {{ $record->scheduled_minutes !== null ? App\Support\PersianUi::digits($record->scheduled_minutes) : '—' }}</td>
                        <td><a class="teacher-workspace-link" href="{{ route('teacher.classrooms.attendance.edit', ['classroom' => $record->classroom_id, 'attendance_date' => $record->attendance_date->format('Y-m-d')]) }}">مشاهده / اصلاح</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="teacher-workspace-empty"><strong>رکوردی برای این فیلتر پیدا نشد.</strong>بازه زمانی یا وضعیت دیگری را انتخاب کن.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($attendanceHistory->hasPages())<div class="teacher-workspace-pagination">{{ $attendanceHistory->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
