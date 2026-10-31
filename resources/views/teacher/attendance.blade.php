@extends('layouts.teacher')
@section('title', 'حضور و غیاب')
@section('heading', 'حضور و غیاب')
@section('content')
    <x-teacher.section-head title="کلاس‌های من" subtitle="برای ثبت حضور روزانه، کلاس موردنظر را انتخاب کن. فقط roster فعال و مجاز هر کلاس نمایش داده می‌شود." />
    <div class="tp-grid tp-grid--cards">
        @forelse ($classrooms as $classroom)
            <x-teacher.card>
                <div class="tp-class-card__cover"></div>
                <strong>{{ $classroom->title }}</strong>
                <small class="tp-muted">{{ $classroom->course?->title ?? 'بدون دوره' }}</small>
                <div class="tp-metric" style="margin:12px 0"><div><b>{{ \App\Support\PersianUi::digits($classroom->active_students_count ?? 0) }}</b><span>دانش‌آموز فعال</span></div></div>
                <a class="tp-btn" href="{{ route('teacher.classrooms.attendance.edit', $classroom) }}">ثبت حضور و غیاب</a>
            </x-teacher.card>
        @empty
            <div class="tp-empty"><strong>کلاس فعالی برای حضور و غیاب وجود ندارد.</strong><span>پس از تخصیص کلاس، اینجا نمایش داده می‌شود.</span></div>
        @endforelse
    </div>
    <div class="tp-pager">{{ $classrooms->links() }}</div>
@endsection