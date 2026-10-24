@extends('layouts.student')

@section('title', 'دوره‌های من')
@section('header-title', 'دوره‌های من')

@section('content')
<section class="page" id="courses">
    <div class="card">
        <div class="chead">
            <h2>دوره‌های در دسترس</h2>
            <span class="pill">{{ \App\Support\PersianUi::digits($courses->total()) }} دوره</span>
        </div>
        @if(request('q'))
            <p class="note">نتیجه‌های جست‌وجو برای «{{ request('q') }}»</p>
        @endif
        <div class="g3">
            @forelse($courses as $course)
                @php
                    $lessonCount = (int) ($course->published_lessons_count ?? 0);
                    $progress = max(0, min(100, (float) ($course->learning_progress ?? 0)));
                @endphp
                <article class="card cc">
                    <a href="{{ route('student.courses.show', $course->id) }}" aria-label="باز کردن دوره {{ $course->title }}">
                        <div class="th"><svg class="i" aria-hidden="true"><use href="#i-book"/></svg></div>
                        <h3>{{ $course->title }}</h3>
                    </a>
                    <p>{{ $course->academy?->name ?? 'آموزشگاه شیخان' }} · {{ \App\Support\PersianUi::digits($lessonCount) }} درس</p>
                    <div class="prog" role="progressbar" aria-label="پیشرفت {{ $course->title }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress) }}">
                        <i style="width:{{ $progress }}%"></i>
                    </div>
                    <p>{{ \App\Support\PersianUi::number($progress) }}٪ تکمیل</p>
                    <a class="btn s" href="{{ route('student.courses.show', $course->id) }}">ادامه یادگیری</a>
                </article>
            @empty
                <div class="empty" style="grid-column:1/-1">
                    @if(request('q'))
                        دوره‌ای با این عبارت پیدا نشد. <a href="{{ route('student.courses.index') }}">دیدن همه دوره‌ها</a>
                    @else
                        هنوز دورهٔ فعالی به حساب تو متصل نشده است.
                    @endif
                </div>
            @endforelse
        </div>
        @if($courses->hasPages())
            <div class="frm">{{ $courses->links() }}</div>
        @endif
    </div>
</section>
@endsection
