@extends('layouts.student')

@section('title', $course->title)
@section('header-title', 'جزئیات دوره')

@section('content')
@php
    $allLessons = $lessons;
    $completed = $allLessons->filter(fn ($lesson) => (float) ($lesson->student_progress ?? 0) >= 100)->count();
@endphp
<section class="page">
    <div class="hero">
        <div class="av"><svg viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#i-book"/></svg></div>
        <div class="txt">
            <h2>{{ $course->title }}</h2>
            <p>{{ $course->short_description ?: 'مسیر یادگیری این دوره را از همین‌جا دنبال کن.' }}</p>
            <span class="tag">{{ \App\Support\PersianUi::digits($completed) }} از {{ \App\Support\PersianUi::digits($allLessons->count()) }} درس تکمیل‌شده</span>
        </div>
    </div>
    <div class="card">
        <div class="chead"><h2>محتوای دوره</h2><a class="pill" href="{{ route('student.courses.index') }}">بازگشت به دوره‌ها</a></div>
        @forelse($course->sections as $section)
            <div class="chead"><h2>{{ $section->title }}</h2><span class="tag">{{ \App\Support\PersianUi::digits($section->lessons->count()) }} درس</span></div>
            @forelse($section->lessons as $lesson)
                @php $progress = max(0, min(100, (float) ($lesson->student_progress ?? 0))); @endphp
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-book"/></svg></div>
                    <div>
                        <b>{{ $lesson->title }}</b>
                        <span>{{ $lesson->summary ?: ($lesson->type === 'video' ? 'درس ویدئویی' : 'محتوای آموزشی') }} · {{ \App\Support\PersianUi::number($progress) }}٪</span>
                        <div class="prog"><i style="width:{{ $progress }}%"></i></div>
                    </div>
                    <a class="pl" href="{{ route('student.lessons.show', $lesson->id) }}" aria-label="باز کردن درس {{ $lesson->title }}"><svg class="i" aria-hidden="true"><use href="#i-play"/></svg></a>
                </div>
            @empty
                <div class="empty">در این بخش هنوز درس منتشرشده‌ای نیست.</div>
            @endforelse
        @empty
            <div class="empty">محتوای منتشرشده‌ای برای این دوره وجود ندارد.</div>
        @endforelse
    </div>
</section>
@endsection
