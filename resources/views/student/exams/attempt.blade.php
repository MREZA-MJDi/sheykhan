@extends('layouts.student')

@section('title', 'پاسخ‌گویی به آزمون')
@section('header-title', 'پاسخ‌گویی به آزمون')

@section('content')
@php
    $exam = $attempt->exam;
    $deadline = null;
    if ((int) $exam->duration_minutes > 0 && $attempt->started_at) {
        $deadline = $attempt->started_at->copy()->addMinutes((int) $exam->duration_minutes);
    }
    if ($exam->ends_at && (!$deadline || $exam->ends_at->lt($deadline))) {
        $deadline = $exam->ends_at->copy();
    }
    $remaining = $deadline ? max(0, $deadline->timestamp - now()->timestamp) : null;
    $savedAnswers = $attempt->answers->keyBy('question_id');
@endphp
<form class="page" id="exam-form" method="POST" enctype="multipart/form-data" action="{{ route('student.exam-attempts.submit', $attempt->id) }}">
    @csrf
    <div class="card">
        <div class="chead">
            <h2>{{ $exam->title }}</h2>
            @if($remaining !== null)
                <span class="tag w">زمان باقی‌مانده: <b data-countdown="{{ $remaining }}">{{ \App\Support\PersianUi::digits(sprintf('%02d:%02d', intdiv($remaining, 60), $remaining % 60)) }}</b></span>
            @else
                <span class="tag">بدون شمارش معکوس</span>
            @endif
        </div>
        <p class="note">پاسخ‌ها با شناسهٔ هر سؤال ارسال می‌شوند. زمان و امکان ارسال نهایی در سرور دوباره بررسی می‌شود.</p>
        <div class="sum">
            <span class="tag">{{ $exam->course?->title ?? 'دوره' }}</span>
            <span class="tag">{{ \App\Support\PersianUi::digits($exam->questions->count()) }} سؤال</span>
            <span class="tag">تلاش شمارهٔ {{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</span>
        </div>
    </div>

    @forelse($exam->questions as $index => $question)
        @php
            $saved = $savedAnswers->get($question->id);
            $savedValue = $saved?->answer;
            $savedArray = is_string($savedValue) ? json_decode($savedValue, true) : $savedValue;
            $options = (array) ($question->options ?? []);
        @endphp
        <div class="card q" data-question-step>
            <div class="chead">
                <h2>سؤال {{ \App\Support\PersianUi::digits($index + 1) }}</h2>
                <span class="tag">{{ \App\Support\PersianUi::number($question->score, 1) }} نمره</span>
            </div>
            <p style="font-size:14px;line-height:2;margin-bottom:14px">{{ $question->question }}</p>
            @foreach($question->media->where('pivot.collection', 'exam-question-prompt') as $promptAttachment)
                <p class="note">فایل صورت سؤال: <a href="{{ route('media.view', $promptAttachment) }}" class="underline">{{ $promptAttachment->original_name }}</a></p>
            @endforeach

            @if($question->type === 'text')
                <label for="answer-{{ $question->id }}" style="display:block;font-size:12px;margin-bottom:8px">پاسخ تشریحی</label>
                <textarea class="fld" id="answer-{{ $question->id }}" name="answers[{{ $question->id }}]" rows="5" maxlength="10000">{{ old('answers.'.$question->id, is_string($savedValue) ? $savedValue : '') }}</textarea>
            @elseif(in_array($question->type, ['checkbox','multiple'], true))
                @php
                    $submittedValues = old('answers.'.$question->id, $savedArray);
                    $submittedValues = is_array($submittedValues) ? array_map('strval', $submittedValues) : [(string) $submittedValues];
                @endphp
                @foreach($options as $optionKey => $optionLabel)
                    @php
                        $optionValue = is_int($optionKey)
                            ? (string) (is_array($optionLabel) ? ($optionLabel['value'] ?? $optionLabel['id'] ?? $optionLabel['text'] ?? $optionLabel['label'] ?? $optionKey) : $optionLabel)
                            : (string) $optionKey;
                        $isChecked = in_array($optionValue, $submittedValues, true);
                    @endphp
                    <label>
                        <input type="checkbox"
                               name="answers[{{ $question->id }}][]"
                               value="{{ $optionValue }}" @checked($isChecked)>
                        <span>{{ is_array($optionLabel) ? ($optionLabel['text'] ?? $optionLabel['label'] ?? json_encode($optionLabel, JSON_UNESCAPED_UNICODE)) : $optionLabel }}</span>
                    </label>
                @endforeach
            @else
                @foreach($options as $optionKey => $optionLabel)
                    @php
                        $optionValue = is_int($optionKey)
                            ? (string) (is_array($optionLabel) ? ($optionLabel['value'] ?? $optionLabel['id'] ?? $optionLabel['text'] ?? $optionLabel['label'] ?? $optionKey) : $optionLabel)
                            : (string) $optionKey;
                    @endphp
                    <label>
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optionValue }}" @checked((string) old('answers.'.$question->id, $savedValue) === $optionValue)>
                        <span>{{ is_array($optionLabel) ? ($optionLabel['text'] ?? $optionLabel['label'] ?? json_encode($optionLabel, JSON_UNESCAPED_UNICODE)) : $optionLabel }}</span>
                    </label>
                @endforeach
            @endif
            <label for="answer-file-{{ $question->id }}" style="display:block;font-size:12px;margin:12px 0 6px">پیوست پاسخ (اختیاری · حداکثر ۲۰ مگابایت)</label>
            <input class="fld" id="answer-file-{{ $question->id }}" type="file" name="answer_attachments[{{ $question->id }}]" accept=".pdf,.txt,.jpg,.jpeg,.png,.webp,.doc,.docx,.zip">
            @if($saved?->media?->isNotEmpty())
                <p class="note">فایل ثبت‌شده:
                    @foreach($saved->media as $answerFile)<a href="{{ route('media.view', $answerFile) }}" class="underline">{{ $answerFile->original_name }}</a>@endforeach
                </p>
            @endif
            <p class="note">فایل پاسخ برای مدرس قابل مشاهده است؛ پاسخ دارای پیوست در صف بررسی مدرس قرار می‌گیرد.</p>
        </div>
    @empty
        <div class="card"><div class="empty">سؤال قابل پاسخی برای این آزمون وجود ندارد.</div></div>
    @endforelse

    <div class="card">
        <div class="frm">
            <span>قبل از ارسال، پاسخ‌هایت را یک‌بار مرور کن.</span>
            <button class="btn" type="submit">ثبت نهایی پاسخ‌ها</button>
        </div>
    </div>
</form>
@endsection
