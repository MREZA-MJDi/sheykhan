@extends('layouts.teacher')
@section('title', 'گزارش عملکرد')
@section('heading', 'گزارش عملکرد')
@section('content')
    <x-teacher.section-head title="عملکرد آموزشی" subtitle="همه اعداد از داده‌های ثبت‌شده backend محاسبه شده‌اند؛ داده‌ی ناموجود به‌صورت نمونه ساخته نمی‌شود." />
    <div class="tp-metric-grid">
        @foreach ([
            ['کلاس‌های فعال', $metrics['activeClasses'] ?? 0],
            ['دانش‌آموزان فعال', $metrics['studentCount'] ?? 0],
            ['جلسه‌های این هفته', $metrics['weeklySessions'] ?? 0],
            ['جلسه‌های تکمیل‌شده', $completedSessions ?? 0],
            ['تحویل‌های منتظر تصحیح', $metrics['pendingAssignmentReviews'] ?? 0],
            ['آزمون‌های منتظر بررسی', $metrics['pendingExamReviews'] ?? 0],
        ] as [$label, $value])
            <article class="tp-metric"><div><b class="tp-number">{{ \App\Support\PersianUi::digits($value) }}</b><span>{{ $label }}</span></div></article>
        @endforeach
    </div>
    <div class="tp-grid tp-grid--main tp-grid--start">
        <x-teacher.card title="روند هفتگی">
            @php($period = $chart['week'] ?? ['labels' => [], 'values' => []])
            @if (count($period['labels'] ?? []))
                <div class="tp-chart">@foreach ($period['labels'] as $i => $label)@php($value = max(0, min(100, (float) ($period['values'][$i] ?? 0))))<div class="tp-chart-col"><b>{{ \App\Support\PersianUi::digits((int) round($value)) }}٪</b><i style="height:{{ max(3, $value) }}%"></i><span>{{ $label }}</span></div>@endforeach</div>
            @else
                <div class="tp-empty"><strong>داده‌ای برای گزارش هفتگی ثبت نشده است.</strong></div>
            @endif
            <p class="tp-muted">شاخص نمودار، میانگین درصد پیشرفت ثبت‌شده درس‌های منتشرشده است؛ معادل ساعت مطالعه یا نمره آزمون نیست.</p>
        </x-teacher.card>
        <x-teacher.card title="پیشرفت به تفکیک دوره">
            @forelse ($courseProgress as $course)
                <div class="tp-progress-row"><strong>{{ $course->title }}</strong><x-teacher.progress :value="$course->progress_average" /><b>{{ \App\Support\PersianUi::digits($course->progress_average) }}٪</b></div>
            @empty
                <div class="tp-empty"><strong>دوره‌ای برای گزارش وجود ندارد.</strong></div>
            @endforelse
        </x-teacher.card>
    </div>
    <x-teacher.card title="کارهای نیازمند پیگیری">
        <div class="tp-schedule-item"><span class="tp-ico">✎</span><div class="tp-schedule-copy"><strong>پاسخ‌های تکالیف</strong><small>تحویل‌های ثبت‌شده‌ای که هنوز نمره ندارند.</small></div><b>{{ \App\Support\PersianUi::digits($metrics['pendingAssignmentReviews'] ?? 0) }}</b><a class="tp-link" href="{{ route('teacher.assignments.index') }}">مدیریت</a></div>
        <div class="tp-schedule-item"><span class="tp-ico">✓</span><div class="tp-schedule-copy"><strong>پاسخ‌های آزمون</strong><small>تلاش‌های ارسال‌شده که نیازمند بررسی دستی یا نهایی هستند.</small></div><b>{{ \App\Support\PersianUi::digits($metrics['pendingExamReviews'] ?? 0) }}</b><a class="tp-link" href="{{ route('teacher.exams.index') }}">مدیریت</a></div>
    </x-teacher.card>
@endsection