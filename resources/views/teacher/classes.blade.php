@extends('layouts.teacher')
@section('title', 'کلاس‌ها')
@section('heading', 'کلاس‌ها')
@section('content')
    <div class="tp-toolbar"><div><h2>کلاس‌های تحت مدیریت من</h2><p class="tp-muted">کلاس‌ها به دوره‌ها و آموزشگاه‌های فعال تخصیص‌یافته به حساب تو متصل‌اند.</p></div>
        @if($tpUser->hasPermission('classrooms.manage'))<a class="tp-btn" href="{{ route('teacher.classrooms.create') }}">+ کلاس جدید</a>@endif
    </div>
    <div class="tp-grid tp-grid--cards">
        @forelse($classrooms as $classroom)
            <x-teacher.card>
                <div class="tp-class__cover"></div>
                <div class="tp-status-row"><strong class="tp-class__title">{{ $classroom->title }}</strong><x-teacher.pill :type="$classroom->status === 'active' ? 'ok' : 'warn'">{{ $classroom->status === 'active' ? 'فعال' : 'آرشیو' }}</x-teacher.pill></div>
                <small class="tp-muted">{{ $classroom->course?->title ?? 'بدون دوره' }}</small>
                <div class="tp-metric-grid" style="grid-template-columns:1fr 1fr;margin:12px 0 8px">
                    <div><b>{{ \App\Support\PersianUi::digits($classroom->active_students_count ?? 0) }}</b><span>دانش‌آموز فعال</span></div>
                    <div><b>{{ \App\Support\PersianUi::digits($classroom->schedules_count ?? 0) }}</b><span>زمان هفتگی</span></div>
                </div>
                <div class="tp-actions">
                    @if($tpUser->hasPermission('attendance.view'))<a class="tp-btn tp-btn--sm" href="{{ route('teacher.classrooms.attendance.edit', $classroom) }}">حضور و غیاب</a>@endif
                    <a class="tp-btn tp-btn--light tp-btn--sm" href="{{ route('teacher.attendance.index') }}">سوابق حضور</a>
                </div>
            </x-teacher.card>
        @empty
            <div class="tp-empty"><strong>کلاسی برای نمایش وجود ندارد.</strong><span>از بخش کلاس جدید، گروه آموزشی بساز.</span></div>
        @endforelse
    </div>
    <div class="tp-pager">{{ $classrooms->links() }}</div>
@endsection