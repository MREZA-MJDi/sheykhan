@extends('layouts.student')

@section('title','تکالیف | شیخان')
@section('header-title','تکالیف')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="assignments-title">
    <div class="student-panel-head">
        <div>
            <span class="student-kicker" style="color:var(--panel-primary)">پیگیری</span>
            <h2 id="assignments-title">تکالیف من</h2>
            <p>فقط تکالیف دوره‌ها و کلاس‌هایی که این حساب مجاز به مشاهده آن‌هاست.</p>
        </div>
    </div>

    <div class="student-list">
        @forelse($assignments as $assignment)
            @php($submission=$assignment->submissions->first())
            <article class="student-list-row">
                <div class="student-date">
                    <strong>{{ $assignment->due_at ? \App\Support\PersianUi::date($assignment->due_at) : '—' }}</strong>
                    <small>موعد</small>
                </div>
                <div class="student-row-content">
                    <a class="student-row-title" href="{{ route('student.assignments.show',$assignment) }}">{{ $assignment->title }}</a>
                    <span class="student-row-meta">
                        {{ $assignment->course?->title }}
                        @if($assignment->classroom) · {{ $assignment->classroom->title }} @endif
                    </span>
                </div>
                @if($submission?->graded_at)
                    <span class="student-status success">ارزیابی‌شده</span>
                @elseif($submission?->submitted_at)
                    <span class="student-status primary">ارسال‌شده</span>
                @else
                    <span class="student-status warning">نیازمند اقدام</span>
                @endif
            </article>
        @empty
            <div class="student-empty"><strong>تکلیف فعالی ندارید.</strong><span>با انتشار تکلیف مرتبط، اینجا نمایش داده می‌شود.</span></div>
        @endforelse
    </div>

    @if($assignments->hasPages())
        <nav class="student-pagination" aria-label="صفحه‌بندی تکالیف">{{ $assignments->links() }}</nav>
    @endif
</section>
@endsection
