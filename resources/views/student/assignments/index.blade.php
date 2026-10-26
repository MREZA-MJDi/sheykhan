@extends('layouts.student')

@section('title', 'تکالیف')
@section('header-title', 'تکالیف')

@section('content')
<section class="page" id="assignments">
    <div class="card">
        <div class="chead"><h2>تکالیف دوره‌های من</h2><div class="flex flex-wrap items-center gap-2"><span class="pill">{{ \App\Support\PersianUi::digits($assignments->total()) }} تکلیف</span><a class="pill" href="{{ route('student.assignments.export', ['format' => 'csv']) }}">خروجی Excel / CSV</a><a class="pill" href="{{ route('student.assignments.export', ['format' => 'word']) }}">گزارش Word</a></div></div>
        <div class="tw">
            <table class="tbl">
                <thead><tr><th>تکلیف</th><th>دوره / کلاس</th><th>مهلت</th><th>وضعیت</th><th></th></tr></thead>
                <tbody>
                @forelse($assignments as $assignment)
                    @php
                        $submission = $assignment->submissions->sortByDesc('created_at')->first();
                        $isLate = $assignment->due_at && $assignment->due_at->isPast() && !$submission;
                    @endphp
                    <tr>
                        <td><strong>{{ $assignment->title }}</strong></td>
                        <td>{{ $assignment->course?->title ?? '—' }} @if($assignment->classroom) <span>{{ $assignment->classroom->title }}</span> @endif</td>
                        <td>{{ $assignment->due_at ? \App\Support\PersianUi::dateTime($assignment->due_at) : 'بدون مهلت' }}</td>
                        <td>
                            @if($submission?->graded_at)
                                <span class="tag ok">تصحیح‌شده</span>
                            @elseif($submission)
                                <span class="tag d">ارسال‌شده</span>
                            @elseif($isLate)
                                <span class="tag no">مهلت گذشته</span>
                            @else
                                <span class="tag w">در انتظار ارسال</span>
                            @endif
                        </td>
                        <td><a class="btn s" href="{{ route('student.assignments.show', $assignment->id) }}">مشاهده</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">تکلیفی برای دوره‌های فعال تو وجود ندارد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())<div class="frm">{{ $assignments->links() }}</div>@endif
    </div>
</section>
@endsection
