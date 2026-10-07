@extends('layouts.student')

@section('title','حضور و غیاب | شیخان')
@section('header-title','حضور و غیاب')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="attendance-title">
    <div class="student-panel-head"><div><span class="student-kicker" style="color:var(--panel-primary)">پیگیری</span><h2 id="attendance-title">حضور و غیاب من</h2><p>سوابق فقط برای کلاس‌هایی که عضویت فعال شما در آن‌ها ثبت شده است.</p></div></div>
    <div class="student-list">
        @forelse($attendance as $item)
            @php($label=['present'=>'حاضر','absent'=>'غایب','late'=>'با تأخیر','excused'=>'موجه'][$item->status] ?? $item->status)
            <article class="student-list-row">
                <div class="student-date"><strong>{{ \App\Support\PersianUi::date($item->attendance_date) }}</strong><small>تاریخ</small></div>
                <div class="student-row-content"><div class="student-row-title">{{ $item->classroom?->title }}</div><span class="student-row-meta">{{ $item->note ?: 'بدون توضیح' }}</span></div>
                <span class="student-status {{ $item->status==='present' ? 'success' : 'warning' }}">{{ $label }}</span>
            </article>
        @empty
            <div class="student-empty"><strong>سابقه‌ای ثبت نشده است.</strong></div>
        @endforelse
    </div>
    @if($attendance->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی حضور و غیاب">{{ $attendance->links() }}</nav>@endif
</section>
@endsection
