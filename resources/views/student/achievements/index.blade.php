@extends('layouts.student')

@section('title','دستاوردهای من | شیخان')
@section('header-title','دستاوردهای من')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="achievements-title">
    <div class="student-panel-head"><div><span class="student-kicker" style="color:var(--panel-primary)">افتخار</span><h2 id="achievements-title">دستاوردهای من</h2><p>دستاوردهای منتشرشده‌ای که به این حساب تعلق دارند.</p></div></div>
    <div class="student-list">
        @forelse($achievements as $achievement)
            <article class="student-list-row">
                <div class="student-date"><strong>★</strong><small>دستاورد</small></div>
                <div class="student-row-content"><div class="student-row-title">{{ $achievement->title ?: $achievement->display_name }}</div><span class="student-row-meta">{{ $achievement->school_name ?: $achievement->achievement_type }}</span></div>
                @if($achievement->media)
                    <a class="student-action" target="_blank" rel="noopener" href="{{ route('media.view',$achievement->media) }}">مشاهده</a>
                @else
                    <span class="student-status success">ثبت‌شده</span>
                @endif
            </article>
        @empty
            <div class="student-empty"><strong>هنوز دستاوردی منتشر نشده است.</strong><span>موفقیت‌های ثبت‌شده پس از انتشار اینجا نمایش داده می‌شوند.</span></div>
        @endforelse
    </div>
    @if($achievements->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی دستاوردها">{{ $achievements->links() }}</nav>@endif
</section>
@endsection
