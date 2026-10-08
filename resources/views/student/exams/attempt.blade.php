@extends('layouts.student')

@section('title','آزمون | شیخان')
@section('header-title','آزمون در حال برگزاری')

@section('content')
    @php
        $questions = $attempt->exam->questions;
        $answerMap = $attempt->answers->keyBy('question_id');
        $durationSeconds = max(0, (int) $attempt->exam->duration_minutes * 60);
        $startedAt = $attempt->started_at;
        $deadline = $startedAt && $durationSeconds
            ? $startedAt->copy()->addSeconds($durationSeconds)
            : null;

        if ($attempt->exam->ends_at && (!$deadline || $attempt->exam->ends_at->lt($deadline))) {
            $deadline = $attempt->exam->ends_at;
        }
    @endphp

    <div
        class="student-exam-shell"
        data-student-exam
        data-storage-key="sheykhan:exam-attempt:{{ $attempt->id }}"
        data-deadline="{{ $deadline?->timestamp ?? 0 }}"
        data-has-timer="{{ $durationSeconds > 0 ? '1' : '0' }}"
    >
        <form
            method="POST"
            action="{{ route('student.exam-attempts.submit',$attempt) }}"
            class="student-exam-form"
            data-exam-form
        >
            @csrf

            <header class="student-exam-header">
                <div class="student-exam-header-brand">
                    <a href="{{ route('student.exams.index') }}" class="student-exam-back" aria-label="بازگشت به آزمون‌ها">→</a>
                    <div>
                        <span>شیخان · چالش امروز</span>
                        <h1>{{ $attempt->exam->title }}</h1>
                    </div>
                </div>

                <div class="student-exam-header-tools">
                    <div class="student-exam-save-state" data-save-state aria-live="polite">
                        <i></i>
                        <span>پاسخ‌ها روی دستگاه ذخیره می‌شوند</span>
                    </div>

                    <div class="student-exam-timer" data-exam-timer aria-live="polite">
                        <span>زمان باقی‌مانده</span>
                        <strong data-timer-value>--:--</strong>
                    </div>
                </div>
            </header>

            <div class="student-exam-main">
                <main class="student-exam-paper">
                    <div class="student-exam-paper-top">
                        <div>
                            <span class="student-exam-chip is-purple">
                                تلاش {{ App\Support\PersianUi::digits($attempt->attempt_number) }}
                            </span>
                            <span class="student-exam-chip">
                                {{ App\Support\PersianUi::digits($questions->count()) }} سؤال
                            </span>
                        </div>

                        <div class="student-exam-progress-copy">
                            <strong data-answered-count>۰</strong>
                            <span>از {{ App\Support\PersianUi::digits($questions->count()) }} پاسخ</span>
                        </div>
                    </div>

                    @if($attempt->exam->description)
                        <div class="student-exam-intro">
                            <span class="student-exam-intro-icon">✦</span>
                            <div>
                                <strong>اول یک نفس عمیق 😊</strong>
                                <p>{{ $attempt->exam->description }}</p>
                            </div>
                        </div>
                    @else
                        <div class="student-exam-intro">
                            <span class="student-exam-intro-icon">✦</span>
                            <div>
                                <strong>سلام دانش‌آموز جان 👋</strong>
                                <p>آرام جلو برو. لازم نیست همه سؤال‌ها را یک‌جا حل کنی؛ هر سؤال فقط یک قدم است.</p>
                            </div>
                        </div>
                    @endif

                    <div class="student-exam-question-stack">
                        @foreach($questions as $question)
                            @php
                                $savedAnswer = $answerMap->get($question->id)?->answer;
                                $savedMultiple = null;

                                if ($savedAnswer !== null && in_array($question->type, ['multiple','checkbox'], true)) {
                                    $decoded = json_decode((string) $savedAnswer, true);
                                    $savedMultiple = json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                                        ? array_map('strval', $decoded)
                                        : preg_split('/s*,s*/', (string) $savedAnswer);
                                }
                            @endphp

                            <section
                                id="exam-question-{{ $question->id }}"
                                class="student-exam-question"
                                data-exam-question
                                data-question-number="{{ $loop->iteration }}"
                            >
                                <div class="student-exam-question-head">
                                    <div class="student-exam-question-number">
                                        {{ App\Support\PersianUi::digits($loop->iteration) }}
                                    </div>

                                    <div class="student-exam-question-meta">
                                        <div>
                                            <span class="student-exam-question-label">
                                                {{ $question->type === 'text' ? 'تشریحی' : 'گزینه‌ای' }}
                                            </span>
                                            <span class="student-exam-score">
                                                {{ App\Support\PersianUi::digits($question->score) }} امتیاز
                                            </span>
                                        </div>
                                        <button
                                            type="button"
                                            class="student-exam-flag"
                                            data-flag-question
                                            aria-pressed="false"
                                        >
                                            ☆ نشان‌گذاری
                                        </button>
                                    </div>
                                </div>

                                <h2>{{ $question->question }}</h2>

                                @if(in_array($question->type,['multiple_choice','single'],true))
                                    <div class="student-exam-options">
                                        @foreach(($question->options ?? []) as $option)
                                            <label class="student-exam-option">
                                                <input
                                                    type="radio"
                                                    name="answers[{{ $question->id }}]"
                                                    value="{{ $option }}"
                                                    @checked((string) $savedAnswer === (string) $option)
                                                    data-answer-input
                                                >
                                                <span class="student-exam-option-mark">{{ chr(65 + $loop->index) }}</span>
                                                <span class="student-exam-option-copy">{{ $option }}</span>
                                                <span class="student-exam-option-check">✓</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif(in_array($question->type,['multiple','checkbox'],true))
                                    <div class="student-exam-options">
                                        @foreach(($question->options ?? []) as $option)
                                            <label class="student-exam-option">
                                                <input
                                                    type="checkbox"
                                                    name="answers[{{ $question->id }}][]"
                                                    value="{{ $option }}"
                                                    @checked($savedMultiple && in_array((string) $option, $savedMultiple, true))
                                                    data-answer-input
                                                >
                                                <span class="student-exam-option-mark">✓</span>
                                                <span class="student-exam-option-copy">{{ $option }}</span>
                                                <span class="student-exam-option-check">✓</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <label class="student-exam-text-answer">
                                        <textarea
                                            name="answers[{{ $question->id }}]"
                                            rows="7"
                                            placeholder="اینجا با خیال راحت توضیح بده..."
                                            data-answer-input
                                        >{{ $savedAnswer }}</textarea>
                                    </label>
                                @endif

                                <div class="student-exam-question-foot">
                                    <span>سؤال {{ App\Support\PersianUi::digits($loop->iteration) }} از {{ App\Support\PersianUi::digits($questions->count()) }}</span>
                                    <button type="button" data-next-question="{{ $question->id }}">
                                        {{ $loop->last ? 'بازبینی پاسخ‌ها' : 'رفتن به سؤال بعدی' }}
                                        <i aria-hidden="true">←</i>
                                    </button>
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="student-exam-submit-panel">
                        <div>
                            <span>رسیدی آخرش؟ عالیه ✨</span>
                            <strong>قبل از تحویل، پاسخ‌هایت را یک‌بار مرور کن.</strong>
                            <p>پس از تحویل، تغییر پاسخ‌ها ممکن نیست و نتیجه برای معلم ارسال می‌شود.</p>
                        </div>
                        <button type="submit" class="student-exam-submit" data-submit-exam>
                            <span>تحویل برگه</span>
                            <i aria-hidden="true">←</i>
                        </button>
                    </div>
                </main>

                <aside class="student-exam-sidebar">
                    <div class="student-exam-sticky-card">
                        <div class="student-exam-side-top">
                            <div>
                                <span>نقشه آزمون</span>
                                <strong>جواب‌هایت اینجاست</strong>
                            </div>
                            <span class="student-exam-side-avatar">😊</span>
                        </div>

                        <div class="student-exam-answer-key" data-answer-key>
                            @foreach($questions as $question)
                                <button
                                    type="button"
                                    class="student-exam-answer-dot"
                                    data-answer-jump="{{ $question->id }}"
                                    data-answer-number="{{ $loop->iteration }}"
                                    aria-label="رفتن به سؤال {{ $loop->iteration }}"
                                >
                                    <span>{{ App\Support\PersianUi::digits($loop->iteration) }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="student-exam-legend">
                            <span><i class="is-answered"></i>پاسخ داده‌ام</span>
                            <span><i class="is-current"></i>در حال حل</span>
                            <span><i class="is-flagged"></i>بعداً برگرد</span>
                        </div>

                        <div class="student-exam-side-tip">
                            <span>💡</span>
                            <p><strong>ترفند کوچک:</strong> سؤال سخت را نشان‌گذاری کن و فعلاً از آن رد شو. زمانت را برای چیزهایی که بلدی نگه دار.</p>
                        </div>

                        <div class="student-exam-side-count">
                            <div>
                                <span>پاسخ داده‌شده</span>
                                <strong data-answered-count-side>۰</strong>
                            </div>
                            <div>
                                <span>نشان‌گذاری</span>
                                <strong data-flagged-count>۰</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="student-exam-mobile-nav">
                <button type="button" data-mobile-question-prev>↑</button>
                <span><strong data-mobile-current>۱</strong> / {{ App\Support\PersianUi::digits($questions->count()) }}</span>
                <button type="button" data-mobile-question-next>↓</button>
            </div>
        </form>
    </div>
@endsection
