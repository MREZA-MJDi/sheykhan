@extends('layouts.teacher')
@section('title','کلاس‌های آنلاین | شیخان')
@section('header-title','کلاس‌های آنلاین')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">جلسه آنلاین</span>
            <h1 class="teacher-workspace-title">کلاس‌های آنلاین</h1>
            <p class="teacher-workspace-description">جلسه را به دوره و در صورت نیاز به یک کلاس مشخص متصل کن و لینک ورود دانش‌آموز را امن و شفاف نگه دار.</p>
        </div>
        <a href="{{ route('teacher.live-classes.create') }}" class="teacher-workspace-btn primary">+ جلسه آنلاین جدید</a>
    </header>

    @if(session('success'))<div class="teacher-workspace-alert success">{{ session('success') }}</div>@endif

    <section class="teacher-workspace-stats">
        <div class="teacher-workspace-stat"><small>کل جلسات</small><strong>{{ App\Support\PersianUi::digits($liveClasses->total()) }}</strong><span>جلسه ثبت‌شده</span></div>
        <div class="teacher-workspace-stat"><small>برنامه‌ریزی‌شده</small><strong>{{ App\Support\PersianUi::digits($liveClasses->getCollection()->where('status','scheduled')->count()) }}</strong><span>در این صفحه</span></div>
        <div class="teacher-workspace-stat"><small>در حال برگزاری</small><strong>{{ App\Support\PersianUi::digits($liveClasses->getCollection()->where('status','live')->count()) }}</strong><span>وضعیت فعلی</span></div>
        <div class="teacher-workspace-stat"><small>لغوشده</small><strong>{{ App\Support\PersianUi::digits($liveClasses->getCollection()->where('status','cancelled')->count()) }}</strong><span>در این صفحه</span></div>
    </section>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>جلسه‌های آنلاین</h2><p>جلسه‌های نزدیک به امروز را سریع پیدا کن و لینک ورود را باز کن.</p></div></div>
        <div class="teacher-workspace-list">
            @forelse($liveClasses as $liveClass)
                @php
                    $status = match($liveClass->status) {
                        'scheduled' => ['برنامه‌ریزی‌شده',''],
                        'live' => ['در حال برگزاری','success'],
                        'ended' => ['پایان‌یافته','muted'],
                        'cancelled' => ['لغوشده','danger'],
                        default => [$liveClass->status,'muted'],
                    };
                @endphp
                <article class="teacher-workspace-item">
                    <div class="teacher-workspace-item-main">
                        <strong class="teacher-workspace-item-title">{{ $liveClass->title }}</strong>
                        <div class="teacher-workspace-item-meta">
                            <span>{{ $liveClass->course?->title ?: 'دوره' }}</span>
                            <span>{{ $liveClass->classroom?->title ?: 'همه دانش‌آموزان دوره' }}</span>
                            <span>{{ App\Support\PersianUi::date($liveClass->scheduled_at).' · '.App\Support\PersianUi::time($liveClass->scheduled_at) }}</span>
                            <span>{{ $liveClass->provider }}</span>
                            <span>{{ App\Support\PersianUi::digits($liveClass->duration_minutes) }} دقیقه</span>
                        </div>
                    </div>
                    <div class="teacher-workspace-item-actions">
                        <span class="teacher-workspace-chip {{ $status[1] }}">{{ $status[0] }}</span>
                        @if($liveClass->meeting_url && $liveClass->status !== 'cancelled')
                            <a href="{{ $liveClass->meeting_url }}" target="_blank" rel="noopener noreferrer" class="teacher-workspace-link primary">ورود به جلسه</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="teacher-workspace-empty"><strong>جلسه‌ای ثبت نشده است.</strong>اولین جلسه آنلاین را برای یک دوره یا کلاس برنامه‌ریزی کن.</div>
            @endforelse
        </div>
        @if($liveClasses->hasPages())<div class="teacher-workspace-pagination">{{ $liveClasses->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
