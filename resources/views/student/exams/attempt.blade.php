@extends('layouts.student')

@section('title','پاسخ‌گویی آزمون | شیخان')
@section('header-title','پاسخ‌گویی آزمون')

@section('content')
<form method="POST" action="{{ route('student.exam-attempts.submit',$attempt) }}" class="student-dashboard">
    @csrf
    <section class="student-welcome">
        <div class="student-welcome-copy">
            <span class="student-kicker">تلاش {{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</span>
            <h1>{{ $attempt->exam->title }}</h1>
            <p>پاسخ‌ها را با دقت ثبت کن. زمان آزمون از سمت سرور کنترل می‌شود.</p>
        </div>
        <span class="student-status" style="background:rgba(255,255,255,.12);color:#fff">ثبت امن</span>
    </section>

    @foreach($attempt->exam->questions as $question)
        <section class="student-panel dashboard-panel" aria-labelledby="q-{{ $question->id }}">
            <div class="student-panel-head">
                <div>
                    <span class="student-kicker" style="color:var(--panel-primary)">سؤال {{ \App\Support\PersianUi::digits($loop->iteration) }}</span>
                    <h2 id="q-{{ $question->id }}">{{ $question->question }}</h2>
                </div>
                <span class="student-status">{{ \App\Support\PersianUi::digits($question->score) }} امتیاز</span>
            </div>

            @if(in_array($question->type,['multiple_choice','single'],true))
                <div class="student-question-options">
                    @foreach(($question->options ?? []) as $option)
                        <label class="student-question-option">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}">
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            @elseif(in_array($question->type,['multiple','checkbox'],true))
                <div class="student-question-options">
                    @foreach(($question->options ?? []) as $option)
                        <label class="student-question-option">
                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}">
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <textarea name="answers[{{ $question->id }}]" rows="6" class="student-input" placeholder="پاسخ خود را بنویسید..."></textarea>
            @endif
        </section>
    @endforeach

    <button type="submit" class="student-welcome-action primary" style="justify-self:start">ثبت نهایی پاسخ‌ها</button>
</form>
@endsection
