@extends('layouts.student')

@section('title', 'نمرات و عملکرد')
@section('header-title', 'نمرات و عملکرد')

@section('content')
@php
    $pageRows = collect($results->items());
    $scoredRows = $pageRows->filter(fn ($row) => $row->score !== null);
    $average = $scoredRows->isNotEmpty() ? $scoredRows->avg('score') : null;
@endphp
<section class="page" id="results">
    <div class="stats">
        <div class="st s1"><div class="ic"><svg class="i" aria-hidden="true"><use href="#i-chart"/></svg></div><small>نتیجه‌های این صفحه</small><b>{{ \App\Support\PersianUi::digits($pageRows->count()) }}</b><span>در این بخش از فهرست</span></div>
        <div class="st s2"><div class="ic"><svg class="i" aria-hidden="true"><use href="#i-trophy"/></svg></div><small>میانگین نمره‌های قابل‌نمایش</small><b>{{ $average === null ? '—' : \App\Support\PersianUi::number($average, 1) }}</b><span>فقط نتیجه‌های نمره‌دار این صفحه</span></div>
        <div class="st s3"><div class="ic"><svg class="i" aria-hidden="true"><use href="#i-exam"/></svg></div><small>نتیجهٔ آزمون</small><b>{{ \App\Support\PersianUi::digits($pageRows->where('result_type', 'exam')->count()) }}</b><span>در این صفحه</span></div>
        <div class="st s4"><div class="ic"><svg class="i" aria-hidden="true"><use href="#i-task"/></svg></div><small>نتیجهٔ تکلیف</small><b>{{ \App\Support\PersianUi::digits($pageRows->where('result_type', 'assignment')->count()) }}</b><span>در این صفحه</span></div>
    </div>

    <div class="card">
        <div class="chead"><h2>کارنامه</h2><div class="flex flex-wrap items-center gap-2"><span class="pill">{{ \App\Support\PersianUi::digits($results->total()) }} نتیجه</span><a class="pill" href="{{ route('student.results.export', ['format' => 'csv']) }}">خروجی Excel / CSV</a><a class="pill" href="{{ route('student.results.export', ['format' => 'word']) }}">گزارش Word</a></div></div>
        <div class="tw">
            <table class="tbl">
                <thead><tr><th>عنوان</th><th>نوع</th><th>تاریخ ثبت</th><th>وضعیت</th><th>نمره</th><th></th></tr></thead>
                <tbody>
                @forelse($results as $result)
                    <tr>
                        <td><strong>{{ $result->title }}</strong></td>
                        <td>{{ $result->result_type === 'exam' ? 'آزمون' : 'تکلیف' }}</td>
                        <td>{{ $result->occurred_at ? \App\Support\PersianUi::dateTime($result->occurred_at) : '—' }}</td>
                        <td><span class="tag {{ $result->status_label === 'تصحیح‌شده' ? 'ok' : 'w' }}">{{ $result->status_label }}</span></td>
                        <td>{{ $result->score === null ? '—' : \App\Support\PersianUi::number($result->score, 1) }}</td>
                        <td>
                            @if($result->result_type === 'exam')
                                <a class="pill" href="{{ route('student.exams.result', $result->target_id) }}">جزئیات آزمون</a>
                            @else
                                <a class="pill" href="{{ route('student.assignments.show', $result->target_id) }}">جزئیات تکلیف</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">هنوز نتیجهٔ ثبت‌شده‌ای برای نمایش وجود ندارد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($results->hasPages())<div class="frm">{{ $results->links() }}</div>@endif
    </div>
</section>
@endsection
