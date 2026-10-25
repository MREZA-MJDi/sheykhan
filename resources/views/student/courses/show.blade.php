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
    @if($course->spotplayer_course_id)
        <div class="card" id="spotplayer-access">
            <div class="chead"><h2>ویدئوهای محافظت‌شدهٔ دوره</h2><span class="pill">SpotPlayer DRM</span></div>
            <p>پخش ویدئو داخل همین صفحه و پس از تأیید دسترسی آموزشی انجام می‌شود. واترمارک اختصاصی و محدودیت دستگاه طبق تنظیمات لایسنس اعمال می‌شود.</p>
            @if($spotplayerLicense)
                <div id="spotplayer-player-{{ $course->id }}" class="mt-4 w-full min-h-[360px]" role="region" aria-label="پخش امن ویدئوهای دوره"></div>
                <p id="spotplayer-player-status-{{ $course->id }}" class="mt-3 text-sm leading-6 text-slate-500" role="status" aria-live="polite">در حال آماده‌سازی پلیر امن...</p>
                <noscript><p class="mt-3 text-sm text-rose-600">برای پخش امن ویدئو باید JavaScript مرورگر فعال باشد.</p></noscript>
                @push('scripts')
                    <script src="{{ config('services.spotplayer.web_player_script') }}" defer></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const playerElement = document.getElementById(@json('spotplayer-player-'.$course->id));
                            const statusElement = document.getElementById(@json('spotplayer-player-status-'.$course->id));

                            async function initializeSpotPlayer() {
                                if (!playerElement || !statusElement || typeof window.SpotPlayer !== 'function') {
                                    if (statusElement) statusElement.textContent = 'پلیر امن بارگذاری نشد. اتصال اینترنت و دسترسی به سرویس SpotPlayer را بررسی و صفحه را دوباره بارگذاری کنید.';
                                    return;
                                }

                                try {
                                    const player = new window.SpotPlayer(
                                        playerElement,
                                        @json(route('student.courses.spotplayer-cookie', $course)),
                                        false
                                    );

                                    await player.Open(
                                        @json($spotplayerLicense->license_key),
                                        @json((string) $course->spotplayer_course_id)
                                    );

                                    statusElement.hidden = true;
                                } catch (error) {
                                    statusElement.textContent = 'پخش ویدئو فعلاً برقرار نشد. اگر مشکل ادامه داشت، با پشتیبانی شیخان تماس بگیرید.';
                                }
                            }

                            initializeSpotPlayer();
                        });
                    </script>
                @endpush
            @elseif($spotplayerConfigured)
                <form method="POST" action="{{ route('student.courses.spotplayer-license', $course) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn s">فعال‌سازی پخش امن ویدئو</button>
                </form>
                @error('spotplayer')<p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>@enderror
            @else
                <p class="mt-3 rounded-xl bg-amber-50 p-3 text-sm leading-6 text-amber-900" role="status">اتصال SpotPlayer هنوز در سرور تنظیم نشده است. برای جلوگیری از صدور لایسنس آزمایشی، این قابلیت فعلاً غیرفعال است.</p>
            @endif
        </div>
    @endif
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
