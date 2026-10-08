@extends('layouts.teacher')
@section('title','دانش‌آموزان | شیخان')
@section('header-title','دانش‌آموزان')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">پیگیری</span>
            <h1 class="teacher-workspace-title">دانش‌آموزان من</h1>
            <p class="teacher-workspace-description">فقط دانش‌آموزانی که در کلاس‌های تحت مدیریت شما عضو فعال هستند نمایش داده می‌شوند.</p>
        </div>
        <div class="teacher-workspace-actions">
            @foreach(['name'=>'نام','progress'=>'درس‌های دیده‌شده','assignments'=>'تکالیف','exams'=>'آزمون‌ها'] as $key=>$label)
                <a href="{{ request()->fullUrlWithQuery(['sort'=>$key,'direction'=>$studentSort === $key && $studentDirection === 'asc' ? 'desc' : 'asc','page'=>null]) }}" class="teacher-workspace-btn {{ $studentSort === $key ? 'primary' : 'secondary' }}">{{ $label }}{{ $studentSort === $key ? ($studentDirection === 'asc' ? ' ↑' : ' ↓') : '' }}</a>
            @endforeach
        </div>
    </header>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>فهرست دانش‌آموزان</h2><p>مرتب‌سازی با شمارنده‌های واقعی فعالیت آموزشی انجام می‌شود.</p></div><span class="teacher-workspace-chip">{{ AppSupportPersianUi::digits($students->total()) }} نفر</span></div>
        <div class="teacher-workspace-table-wrap">
            <table class="teacher-workspace-table">
                <thead><tr><th>دانش‌آموز</th><th>پایه</th><th>درس‌های دیده‌شده</th><th>تکالیف</th><th>آزمون‌ها</th></tr></thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td><strong>{{ $student->name }}</strong><div style="margin-top:3px;color:#98a0ae;font-size:7px">{{ $student->email }}</div></td>
                        <td>{{ $student->studentProfile?->grade ?? '—' }}</td>
                        <td>{{ AppSupportPersianUi::digits($student->progress_items_count) }}</td>
                        <td>{{ AppSupportPersianUi::digits($student->assignment_submissions_count) }}</td>
                        <td>{{ AppSupportPersianUi::digits($student->exam_attempts_count) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="teacher-workspace-empty"><strong>دانش‌آموزی پیدا نشد.</strong>پس از عضویت دانش‌آموز در کلاس‌های شما، این فهرست به‌صورت خودکار تکمیل می‌شود.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())<div class="teacher-workspace-pagination">{{ $students->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
