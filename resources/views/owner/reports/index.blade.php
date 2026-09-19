@extends('layouts.owner')
@section('title','گزارش آموزشگاه | شیخان')
@section('header-title','گزارش آموزشگاه')
@section('content')
<div class="grid gap-5">
    <div class="owner-stats">
        <article class="owner-stat-card"><span>دانش‌آموز</span><strong>{{ $metrics['students'] }}</strong><small>فعال</small></article>
        <article class="owner-stat-card"><span>مدرس</span><strong>{{ $metrics['teachers'] }}</strong><small>فعال</small></article>
        <article class="owner-stat-card"><span>ثبت‌نام</span><strong>{{ $metrics['courses'] }}</strong><small>دوره</small></article>
        <article class="owner-stat-card"><span>فروش</span><strong>{{ number_format($metrics['sales'],0,'.',',') }}</strong><small>تومان</small></article>
    </div>
    <section class="dashboard-panel owner-panel"><div class="owner-panel-head"><div><h3>گزارش مدرس‌ها</h3><p>خروجی‌ای که مدیریت آموزشگاه برای ارزیابی عملکرد مدرس نیاز دارد.</p></div></div><div class="overflow-x-auto mt-4"><table class="w-full min-w-[760px] text-right"><thead class="bg-slate-50 text-[10px] text-slate-500"><tr><th class="px-4 py-3">مدرس</th><th class="px-4 py-3">دوره</th><th class="px-4 py-3">دانش‌آموز</th><th class="px-4 py-3">پیشرفت</th><th class="px-4 py-3">بررسی</th><th class="px-4 py-3">فروش</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($teacherReports as $report)<tr><td class="px-4 py-3 text-xs font-black">{{ $report->name }}</td><td class="px-4 py-3 text-xs">{{ $report->course_count }}</td><td class="px-4 py-3 text-xs">{{ $report->student_count }}</td><td class="px-4 py-3 text-xs">{{ $report->progress_average }}٪</td><td class="px-4 py-3 text-xs">{{ $report->pending_reviews }}</td><td class="px-4 py-3 text-xs">{{ number_format((float)$report->sales,0,'.',',') }} تومان</td></tr>@empty<tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">گزارشی برای مدرس‌ها موجود نیست.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection