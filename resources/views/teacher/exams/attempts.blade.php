@extends('layouts.teacher')

@section('title','نتایج آزمون | شیخان')
@section('header-title','تصحیح آزمون')

@section('content')
    <div class="teacher-exam-review">
        <section class="teacher-exam-review-hero">
            <div>
                <span>بررسی و بازخورد</span>
                <h1>{{ $exam->title }}</h1>
                <p>
                    پاسخ‌های دانش‌آموزان این آزمون را بررسی کن؛ پاسخ‌های تستی قابل تصحیح خودکار هستند و سؤال‌های تشریحی برای بازبینی دستی می‌آیند.
                </p>
            </div>

            <div class="teacher-exam-review-summary">
                <div>
                    <strong>{{ $exam->questions->count() }}</strong>
                    <span>سؤال</span>
                </div>
                <div>
                    <strong>{{ $attempts->total() }}</strong>
                    <span>تلاش</span>
                </div>
            </div>
        </section>

        <div class="teacher-exam-attempt-list">
            @forelse($attempts as $attempt)
                @php
                    $isReview = in_array($attempt->status, ['submitted','pending_review','needs_review'], true);
                    $statusLabel = match($attempt->status) {
                        'graded' => 'تصحیح‌شده',
                        'pending_review', 'needs_review' => 'نیازمند بررسی',
                        'submitted' => 'ارسال‌شده',
                        default => 'در حال انجام',
                    };
                    $statusTone = $attempt->status === 'graded'
                        ? 'success'
                        : ($isReview ? 'warning' : 'muted');
                @endphp

                <article class="teacher-exam-attempt-card" id="attempt-{{ $attempt->id }}">
                    <header class="teacher-exam-attempt-head">
                        <div class="teacher-exam-student">
                            <span class="teacher-exam-student-avatar">
                                {{ mb_substr($attempt->student?->name ?? 'د', 0, 1) }}
                            </span>
                            <div>
                                <strong>{{ $attempt->student?->name ?: 'دانش‌آموز' }}</strong>
                                <span>
                                    تلاش {{ App\Support\PersianUi::digits($attempt->attempt_number) }}
                                    ·
                                    {{ $attempt->submitted_at ? App\Support\PersianUi::date($attempt->submitted_at) : 'هنوز تحویل نشده' }}
                                </span>
                            </div>
                        </div>

                        <div class="teacher-exam-attempt-meta">
                            <span class="teacher-exam-review-status {{ $statusTone }}">{{ $statusLabel }}</span>
                            <span class="teacher-exam-score-chip">
                                {{ $attempt->score === null ? '—' : App\Support\PersianUi::digits($attempt->score) }}
                            </span>
                        </div>
                    </header>

                    <div class="teacher-exam-answer-paper">
                        @forelse($attempt->exam->questions as $question)
                            @php($answer = $attempt->answers->firstWhere('question_id', $question->id))
                            <article class="teacher-exam-answer-row">
                                <div class="teacher-exam-answer-number">{{ App\Support\PersianUi::digits($loop->iteration) }}</div>

                                <div class="teacher-exam-answer-content">
                                    <div class="teacher-exam-answer-question">
                                        <strong>{{ $question->question }}</strong>
                                        <span>{{ App\Support\PersianUi::digits($question->score) }} امتیاز</span>
                                    </div>

                                    <div class="teacher-exam-answer-value">
                                        <span>پاسخ دانش‌آموز</span>
                                        <p>
                                            @if($answer?->answer)
                                                {{ is_array($answer->answer) ? implode('، ', $answer->answer) : $answer->answer }}
                                            @else
                                                بدون پاسخ
                                            @endif
                                        </p>
                                    </div>

                                    @if($answer && $answer->is_correct !== null && $question->type !== 'text')
                                        <span class="teacher-exam-answer-result {{ $answer->is_correct ? 'correct' : 'wrong' }}">
                                            {{ $answer->is_correct ? 'پاسخ صحیح' : 'پاسخ نادرست' }}
                                        </span>
                                    @endif
                                </div>

                                <div class="teacher-exam-answer-score">
                                    @if($isReview)
                                        <label>
                                            <span>نمره</span>
                                            <input
                                                type="number"
                                                min="0"
                                                max="{{ $question->score }}"
                                                step="0.01"
                                                name="answers[{{ $answer?->id }}]"
                                                value="{{ $answer?->score ?? 0 }}"
                                                form="grade-attempt-{{ $attempt->id }}"
                                            >
                                        </label>
                                    @elseif($answer)
                                        <strong>
                                            {{ App\Support\PersianUi::digits($answer->score ?? 0) }}
                                        </strong>
                                        <span>از {{ App\Support\PersianUi::digits($question->score) }}</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="teacher-exam-empty">برای این آزمون هنوز سؤالی ثبت نشده است.</div>
                        @endforelse
                    </div>

                    @if($isReview)
                        <footer class="teacher-exam-attempt-actions">
                            <div>
                                <strong>این برگه آماده‌ی بررسی است.</strong>
                                <span>برای هر سؤال نمره وارد کن؛ مجموع نمره بعد از ثبت محاسبه می‌شود.</span>
                            </div>

                            <div class="teacher-exam-review-actions">
                                <form method="POST" action="{{ route('teacher.exam-attempts.grade',$attempt) }}">
                                    @csrf
                                    <button type="submit" class="teacher-exam-secondary-btn">
                                        تصحیح خودکار
                                    </button>
                                </form>

                                <form
                                    id="grade-attempt-{{ $attempt->id }}"
                                    method="POST"
                                    action="{{ route('teacher.exam-attempts.grade-manual',$attempt) }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="teacher-exam-primary-btn">
                                        ثبت نمره و پایان بررسی
                                    </button>
                                </form>
                            </div>
                        </footer>
                    @endif
                </article>
            @empty
                <section class="teacher-exam-empty-state">
                    <div>✓</div>
                    <strong>هنوز دانش‌آموزی این آزمون را تحویل نداده است.</strong>
                    <p>وقتی اولین برگه ارسال شود، همین‌جا برای بررسی ظاهر می‌شود.</p>
                </section>
            @endforelse
        </div>

        @if($attempts->hasPages())
            <div class="mt-6">
                {{ $attempts->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection
