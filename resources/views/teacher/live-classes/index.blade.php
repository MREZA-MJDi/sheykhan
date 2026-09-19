@extends('layouts.teacher')
@section('title','کلاس‌های آنلاین | شیخان')
@section('header-title','کلاس‌های آنلاین')
@section('content')
<div class="teacher-detail-page">
    @if(session('success'))<div class="teacher-builder-alert success">{{ session('success') }}</div>@endif

    <section class="teacher-list-hero">
        <div><span class="teacher-kicker">LIVE CLASS</span><h2>جلسه‌های آنلاین</h2><p>جلسه زمان‌بندی کن، لینک را مدیریت کن و بعد از لغو یا پایان وضعیت آن را ثبت کن.</p></div>
        <a href="{{ route('teacher.live-classes.create') }}" class="teacher-builder-btn primary" style="background:#5b5ce8;color:#fff">+ جلسه جدید</a>
    </section>

    <section class="teacher-class-grid">
        @forelse($liveClasses as $liveClass)
            <article class="teacher-class-card">
                <div class="teacher-class-card-head">
                    <div><span class="teacher-kicker">{{ $liveClass->course?->title }}</span><h3>{{ $liveClass->title }}</h3><p>{{ $liveClass->classroom?->title ?? 'عمومی دوره' }}</p></div>
                    <span class="teacher-content-status {{ $liveClass->status === 'scheduled' ? 'published' : 'draft' }}">{{ ['scheduled'=>'زمان‌بندی‌شده','live'=>'در حال برگزاری','ended'=>'تمام‌شده','cancelled'=>'لغوشده'][$liveClass->status] ?? $liveClass->status }}</span>
                </div>
                <div class="teacher-class-metrics">
                    <div><span>زمان</span><strong>{{ $liveClass->scheduled_at->format('m/d H:i') }}</strong></div>
                    <div><span>مدت</span><strong>{{ $liveClass->duration_minutes }}د</strong></div>
                    <div><span>Provider</span><strong>{{ $liveClass->provider ?: '—' }}</strong></div>
                </div>
                <div class="teacher-class-actions">
                    @if($liveClass->meeting_url && $liveClass->status !== 'cancelled')
                        <a href="{{ $liveClass->meeting_url }}" target="_blank" rel="noreferrer" class="primary">ورود به جلسه</a>
                    @endif
                    <a href="{{ route('teacher.live-classes.edit',$liveClass) }}">ویرایش</a>
                    @if(in_array($liveClass->status,['scheduled','live'],true))
                        <form method="POST" action="{{ route('teacher.live-classes.cancel',$liveClass) }}" data-confirm="این جلسه لغو شود؟"><button type="submit" class="teacher-builder-delete min-h-8">لغو</button>@csrf @method('PATCH')</form>
                    @endif
                </div>
            </article>
        @empty
            <div class="teacher-detail-panel teacher-detail-empty large">جلسه‌ای ثبت نشده است.</div>
        @endforelse
    </section>
</div>
@endsection
