@extends('layouts.student')

@section('title', 'کلاس‌های من')
@section('header-title', 'کلاس‌های من')

@section('content')
<section class="page" id="live-classes">
    <div class="card">
        <div class="chead"><h2>جلسه‌های تاریخ‌دار</h2><span class="pill">{{ \App\Support\PersianUi::digits($classes->total()) }} جلسه</span></div>
        @forelse($classes as $class)
            <div class="trk">
                <div class="th"><svg class="i" aria-hidden="true"><use href="#i-video"/></svg></div>
                <div>
                    <b>{{ $class->title }}</b>
                    <span>{{ $class->course?->title }} @if($class->classroom) · {{ $class->classroom->title }} @endif</span>
                    <span>{{ \App\Support\PersianUi::dateTime($class->scheduled_at) }} @if($class->teacher) · {{ $class->teacher->name }} @endif</span>
                </div>
                @if($class->can_join)
                    <a class="btn s" href="{{ route('student.live-classes.join', $class->id) }}">ورود به کلاس</a>
                @else
                    @php
                        $sessionStatusLabel = match ($class->status) {
                            'scheduled' => 'در انتظار',
                            'live' => 'در حال برگزاری',
                            'completed' => 'برگزارشده',
                            'published' => 'ضبط منتشرشده',
                            default => 'غیرقابل ورود',
                        };
                        $sessionStatusClass = in_array($class->status, ['completed', 'published'], true) ? 'ok' : 'w';
                    @endphp
                    <span class="tag {{ $sessionStatusClass }}">{{ $sessionStatusLabel }}</span>
                @endif
            </div>
        @empty
            <div class="empty">جلسه‌ای در بازهٔ برنامه‌ریزی‌شدهٔ کلاس‌ها پیدا نشد.</div>
        @endforelse
        @if($classes->hasPages())<div class="frm">{{ $classes->links() }}</div>@endif
    </div>

    <div class="card">
        <div class="chead"><h2>برنامهٔ هفتگی کلاس‌ها</h2><span class="pill">{{ \App\Support\PersianUi::digits($weeklySchedules->count()) }} برنامه</span></div>
        @forelse($weeklySchedules as $schedule)
            <div class="trk">
                <div class="th"><svg class="i" aria-hidden="true"><use href="#i-cal"/></svg></div>
                <div>
                    <b>{{ $schedule->classroom?->title ?? 'کلاس هفتگی' }}</b>
                    <span>{{ $schedule->classroom?->course?->title }}</span>
                    <span>{{ \App\Support\PersianUi::digits($schedule->start_time) }} – {{ \App\Support\PersianUi::digits($schedule->end_time) }} · روز هفته {{ ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'][(int) $schedule->weekday] ?? 'نامشخص' }}</span>
                </div>
                @if($schedule->can_join)
                    <a class="btn s" href="{{ route('student.schedule.join', $schedule->id) }}">ورود به کلاس</a>
                @else
                    <span class="tag">برنامهٔ تکرارشونده</span>
                @endif
            </div>
        @empty
            <div class="empty">برنامهٔ هفتگی برای کلاس‌های فعال تو ثبت نشده است.</div>
        @endforelse
    </div>

    <div class="card">
        <div class="chead"><h2>ضبط‌های منتشرشده</h2><span class="pill">{{ \App\Support\PersianUi::digits($recordings->total()) }} فایل</span></div>
        @forelse($recordings as $recording)
            <div class="trk">
                <div class="th"><svg class="i" aria-hidden="true"><use href="#i-play"/></svg></div>
                <div>
                    <b>{{ $recording->title }}</b>
                    <span>{{ $recording->course?->title }} · {{ \App\Support\PersianUi::dateTime($recording->scheduled_at) }}</span>
                    <span>{{ $recording->recording?->original_name ?? 'ضبط کلاس' }}</span>
                </div>
                @if($recording->recording)
                    <a class="btn s" href="{{ route('student.live-classes.recording', $recording->id) }}">تماشای ضبط</a>
                @endif
            </div>
        @empty
            <div class="empty">ضبط منتشرشده‌ای برای دوره‌هایت در دسترس نیست.</div>
        @endforelse
        @if($recordings->hasPages())<div class="frm">{{ $recordings->links() }}</div>@endif
    </div>
</section>
@endsection
