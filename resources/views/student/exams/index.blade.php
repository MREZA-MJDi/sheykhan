@extends('layouts.student')

@section('title', 'آزمون‌ها')
@section('header-title', 'آزمون‌ها')

@section('content')
<section class="page" id="exams">
    <div class="card">
        <div class="chead"><h2>آزمون‌های دوره‌های من</h2><span class="pill">{{ \App\Support\PersianUi::digits($exams->total()) }} آزمون</span></div>
        <div class="tw">
            <table class="tbl">
                <thead><tr><th>آزمون</th><th>دوره / کلاس</th><th>مدت</th><th>زمان برگزاری</th><th>وضعیت</th><th></th></tr></thead>
                <tbody>
                @forelse($exams as $exam)
                    @php
                        $open = $exam->status === 'published' && (!$exam->starts_at || $exam->starts_at->lte(now())) && (!$exam->ends_at || $exam->ends_at->gte(now()));
                    @endphp
                    <tr>
                        <td><strong>{{ $exam->title }}</strong></td>
                        <td>{{ $exam->course?->title ?? '—' }} @if($exam->classroom) · {{ $exam->classroom->title }} @endif</td>
                        <td>{{ $exam->duration_minutes ? \App\Support\PersianUi::digits($exam->duration_minutes).' دقیقه' : 'بدون محدودیت زمانی' }}</td>
                        <td>
                            @if($exam->starts_at || $exam->ends_at)
                                {{ $exam->starts_at ? \App\Support\PersianUi::dateTime($exam->starts_at) : '—' }}
                                @if($exam->ends_at) تا {{ \App\Support\PersianUi::dateTime($exam->ends_at) }} @endif
                            @else
                                زمان مشخص نشده
                            @endif
                        </td>
                        <td><span class="tag {{ $open ? 'ok' : 'w' }}">{{ $open ? 'قابل شروع' : ($exam->status === 'published' ? 'خارج از بازهٔ آزمون' : 'غیرفعال') }}</span></td>
                        <td><a class="btn s" href="{{ route('student.exams.show', $exam->id) }}">جزئیات</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">آزمون منتشرشده‌ای برای دوره‌های فعال تو وجود ندارد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())<div class="frm">{{ $exams->links() }}</div>@endif
    </div>
</section>
@endsection
