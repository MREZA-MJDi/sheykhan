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
                <div id="spotplayer-stage-{{ $course->id }}" class="spotplayer-stage mt-4 w-full" role="region" aria-label="پخش امن ویدئوهای دوره">
                    <div id="spotplayer-player-{{ $course->id }}" class="relative z-0 w-full min-h-[360px]"></div>
                    <div class="spotplayer-view-watermark" aria-hidden="true">
                        @for($mark = 0; $mark < 8; $mark++)
                            <span>شیخان · USER-{{ auth()->id() }} · <b data-spotplayer-view-reference>ثبت شناسهٔ مشاهده...</b></span>
                        @endfor
                    </div>
                </div>
                <p id="spotplayer-player-status-{{ $course->id }}" class="mt-3 text-sm leading-6 text-slate-500" role="status" aria-live="polite">در حال آماده‌سازی پلیر امن...</p>
                <noscript><p class="mt-3 text-sm text-rose-600">برای پخش امن ویدئو باید JavaScript مرورگر فعال باشد.</p></noscript>
                @push('styles')
                    <style>
                        .spotplayer-stage { position:relative; isolation:isolate; min-height:360px; overflow:hidden; }
                        .spotplayer-view-watermark {
                            position:absolute; inset:0; z-index:20; pointer-events:none; overflow:hidden;
                            display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); grid-auto-rows:minmax(70px,1fr);
                            align-items:center; justify-items:center; opacity:.18; user-select:none;
                        }
                        .spotplayer-view-watermark span {
                            color:#4338ca; font-size:clamp(9px,1.25vw,14px); font-weight:900;
                            white-space:nowrap; transform:rotate(-24deg); text-shadow:0 1px 1px rgba(255,255,255,.85);
                        }
                    </style>
                @endpush
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
                                    const csrfToken = @json(csrf_token());
                                    const traceResponse = await fetch(
                                        @json(route('student.courses.spotplayer-view', $course)),
                                        {
                                            method: 'POST',
                                            credentials: 'same-origin',
                                            headers: {
                                                'Accept': 'application/json',
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken
                                            },
                                            body: JSON.stringify({})
                                        }
                                    );

                                    if (!traceResponse.ok) {
                                        throw new Error('The protected playback view could not be logged.');
                                    }

                                    const traceData = await traceResponse.json();
                                    if (!traceData.reference) {
                                        throw new Error('The protected playback reference is missing.');
                                    }

                                    document.querySelectorAll('#spotplayer-stage-{{ $course->id }} [data-spotplayer-view-reference]').forEach(function (element) {
                                        element.textContent = traceData.reference;
                                    });

                                    const player = new window.SpotPlayer(
                                        playerElement,
                                        @json(route('student.courses.spotplayer-cookie', $course)),
                                        false,
                                        'SPOTPLAYER_X'
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
