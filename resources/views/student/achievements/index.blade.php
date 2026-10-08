@extends('layouts.student')

@section('title','دستاوردهای من | شیخان')
@section('header-title','دستاوردهای من')

@section('content')
<div class="student-workspace-page"><header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">افتخار</span><h1 class="student-workspace-title">دستاوردهای من</h1><p class="student-workspace-description">دستاوردهای منتشرشده‌ای که به این حساب تعلق دارند.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.dashboard') }}">خانه یادگیری</a></header><section class="student-workspace-card" aria-labelledby="achievements-title">
    <div class="student-workspace-card-head"><div><h2 id="achievements-title">دستاوردهای ثبت‌شده</h2><p>سوابق موفقیت و افتخار شما</p></div></div>
    <div class="student-workspace-list">
        @forelse($achievements as $achievement)
            <article class="student-workspace-row">
                <div class="student-workspace-date"><strong>★</strong><small>دستاورد</small></div>
                <div class="student-workspace-row-main"><div class="student-row-title">{{ $achievement->title ?: $achievement->display_name }}</div><span class="student-row-meta">{{ $achievement->school_name ?: $achievement->achievement_type }}</span></div>
                @if($achievement->media)
                    <a class="student-workspace-status primary" href="{{ route('student.achievements.show', $achievement) }}">مشاهده</a>
                @else
                    <span class="student-workspace-status success">ثبت‌شده</span>
                @endif
            </article>
        @empty
            <div class="student-workspace-empty"><strong>هنوز دستاوردی منتشر نشده است.</strong><span>موفقیت‌های ثبت‌شده پس از انتشار اینجا نمایش داده می‌شوند.</span></div>
        @endforelse
    </div>
    @if($achievements->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی دستاوردها">{{ $achievements->links() }}</nav>@endif
</section></div>
@endsection
