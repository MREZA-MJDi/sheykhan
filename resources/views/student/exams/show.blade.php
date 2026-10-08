@extends('layouts.student')

@section('title',$exam->title.' | شیخان')
@section('header-title','آماده‌سازی آزمون')

@section('content')
    @php
        $open = ($exam->status === 'published')
            && (!$exam->starts_at || now()->gte($exam->starts_at))
            && (!$exam->ends_at || now()->lte($exam->ends_at));

        $activeAttempt = $exam->attempts->firstWhere('status', 'in_progress');
        $completedAttempts = $exam->attempts->whereIn('status', ['submitted','pending_review','needs_review','graded'])->count();
        $remainingAttempts = max(0, (int) $exam->attempts_allowed - $completedAttempts);
    @endphp

    <div class="student-exam-prep">
        <section class="student-exam-prep-hero">
            <div class="student-exam-prep-bubble one">✦</div>
            <div class="student-exam-prep-bubble two">●</div>
            <div class="student-exam-prep-copy">
                <span>آماده‌ای؟ یک چالش کوتاه داریم 😊</span>
                <h1>{{ $exam->title }}</h1>
                <p>
                    آرام شروع کن، سؤال‌ها را یکی‌یکی جلو برو و اگر جایی گیر کردی، نشانش بگذار و بعداً برگرد.
                </p>

                @if($exam->course?->title)
                    <span class="student-exam-prep-course">{{ $exam->course->title }}</span>
                @endif
            </div>

            <div class="student-exam-prep-mascot" aria-hidden="true">
                <span>😊</span>
                <i></i><i></i><i></i>
            </div>
        </section>

        <section class="student-exam-prep-grid" aria-label="اطلاعات آزمون">
            <article>
                <span>تعداد سؤال</span>
                <strong>{{ App\Support\PersianUi::digits($exam->questions->count()) }}</strong>
                <small>هر سؤال ارزش خودش را دارد.</small>
            </article>
            <article>
                <span>زمان</span>
                <strong>{{ $exam->duration_minutes ? App\Support\PersianUi::digits($exam->duration_minutes) : '∞' }}</strong>
                <small>{{ $exam->duration_minutes ? 'دقیقه' : 'بدون محدودیت زمانی' }}</small>
            </article>
            <article>
                <span>تلاش باقی‌مانده</span>
                <strong>{{ App\Support\PersianUi::digits($activeAttempt ? 1 : $remainingAttempts) }}</strong>
                <small>{{ $activeAttempt ? 'یک آزمون نیمه‌تمام داری.' : 'فرصت‌های مجاز' }}</small>
            </article>
        </section>

        <section class="student-exam-prep-body">
            <div class="student-exam-prep-main">
                <div class="student-exam-prep-section-head">
                    <span>قبل از شروع</span>
                    <h2>سه نکته برای یک امتحان بهتر</h2>
                </div>

                <div class="student-exam-prep-tips">
                    <article>
                        <b>۱</b>
                        <div>
                            <strong>با سؤال ساده شروع کن</strong>
                            <p>لازم نیست از سؤال اول تا آخر خطی بروی. سؤال‌هایی که مطمئنی را زودتر جمع کن.</p>
                        </div>
                    </article>
                    <article>
                        <b>۲</b>
                        <div>
                            <strong>سؤال سخت را نشان بگذار</strong>
                            <p>برای یک سؤال، کل زمان امتحان را خرج نکن. برگرد؛ شاید جوابش با یک نفس آرام روشن شد.</p>
                        </div>
                    </article>
                    <article>
                        <b>۳</b>
                        <div>
                            <strong>آخر کار مرور کن</strong>
                            <p>پاسخ‌های انتخاب‌شده، سؤال‌های خالی و سؤال‌های نشان‌گذاری‌شده را قبل از تحویل ببین.</p>
                        </div>
                    </article>
                </div>

                @if($exam->description)
                    <div class="student-exam-prep-note">
                        <span>یادداشت معلم</span>
                        <p>{{ $exam->description }}</p>
                    </div>
                @endif
            </div>

            <aside class="student-exam-prep-action">
                <div class="student-exam-prep-status {{ $open ? 'is-open' : 'is-closed' }}">
                    <i></i>
                    <span>{{ $open ? 'آزمون باز است' : 'آزمون فعلاً باز نیست' }}</span>
                </div>

                @if($open)
                    <form method="POST" action="{{ route('student.exams.start',$exam) }}">
                        @csrf
                        <button type="submit" class="student-exam-prep-start">
                            <span>{{ $activeAttempt ? 'ادامه آزمون' : 'شروع آزمون' }}</span>
                            <i aria-hidden="true">←</i>
                        </button>
                    </form>
                    <small>زمان و دسترسی نهایی در سرور کنترل می‌شود.</small>
                @else
                    <div class="student-exam-prep-closed">
                        <strong>کمی صبر کن 🌱</strong>
                        <p>
                            @if($exam->starts_at && now()->lt($exam->starts_at))
                                شروع آزمون: {{ App\Support\PersianUi::date($exam->starts_at) }} · {{ App\Support\PersianUi::time($exam->starts_at) }}
                            @else
                                بازه‌ی این آزمون به پایان رسیده یا هنوز منتشر نشده است.
                            @endif
                        </p>
                    </div>
                @endif

                <a href="{{ route('student.exams.result',$exam) }}" class="student-exam-prep-results">
                    نتیجه تلاش‌های قبلی
                    <span aria-hidden="true">←</span>
                </a>
            </aside>
        </section>
    </div>
@endsection
