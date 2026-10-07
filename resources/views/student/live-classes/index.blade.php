@extends('layouts.student')

@section('title','کلاس‌های من | شیخان')
@section('header-title','کلاس‌های من')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="classes-title">
    <div class="student-panel-head">
        <div><span class="student-kicker" style="color:var(--panel-primary)">برنامه</span><h2 id="classes-title">کلاس‌ها و جلسات</h2><p>جلسه‌های مرتبط با دوره‌ها و کلاس‌های فعال شما.</p></div>
    </div>
    <div class="student-session-list">
        @forelse($classes as $class)
            <article class="student-session {{ $class->can_join ? 'is-open' : 'is-locked' }}">
                <div class="student-session-head"><span class="student-lamp {{ $class->can_join ? 'on':'off' }}" aria-hidden="true"></span><strong>{{ $class->can_join ? 'قابل ورود':'در انتظار' }}</strong><span>{{ \App\Support\PersianUi::date($class->scheduled_at) }}</span></div>
                <div class="student-row-title">{{ $class->title }}</div>
                <div class="student-row-meta">{{ $class->course?->title }} @if($class->teacher) · {{ $class->teacher->name }} @endif</div>
                @if($class->can_join)
                    <a class="student-session-action" href="{{ route('student.live-classes.join',$class) }}">ورود به جلسه ←</a>
                @else
                    <span class="student-session-action disabled">در زمان مجاز فعال می‌شود</span>
                @endif
            </article>
        @empty
            <div class="student-empty"><strong>جلسه‌ای در برنامه نیست.</strong><span>جلسه‌های بعدی دوره‌های فعال در این بخش ظاهر می‌شوند.</span></div>
        @endforelse
    </div>
    @if($classes->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی کلاس‌ها">{{ $classes->links() }}</nav>@endif
</section>
@endsection
