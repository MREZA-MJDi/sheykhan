@php
    $type = $q['type'] ?? 'single';
    $isText = $type === 'text';
    $isMultiple = in_array($type, ['multiple', 'checkbox'], true);
    $options = array_values($q['options'] ?? ['', '', '', '']);
    $correct = $q['correct_answer'] ?? null;
    if (is_string($correct) && in_array($type, ['multiple','checkbox'], true)) {
        $decoded = json_decode($correct, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $correct = $decoded;
    }
    $correctValues = is_array($correct) ? $correct : [$correct];
@endphp
<div class="tp-q" data-tp-q>
    <input type="hidden" name="questions[{{ $i }}][type]" value="{{ $type }}">
    <div class="tp-q__head">
        <span class="tp-q__no" data-tp-qno></span>
        <x-teacher.pill :type="$isText ? 'info' : ($isMultiple ? 'warn' : 'default')">{{ $isText ? 'تشریحی' : ($isMultiple ? 'چندپاسخی' : 'چهارگزینه‌ای') }}</x-teacher.pill>
        <span class="tp-spacer"></span>
        <label class="tp-q__score">نمره <input type="number" min="0" step="0.25" name="questions[{{ $i }}][score]" value="{{ $q['score'] ?? 1 }}" data-tp-score></label>
        <button type="button" class="tp-btn tp-btn--ghost-danger tp-q__tools" data-tp-q-remove aria-label="حذف سؤال">حذف</button>
    </div>
    <textarea rows="2" name="questions[{{ $i }}][question]" placeholder="متن سؤال را بنویس…" required>{{ $q['question'] ?? '' }}</textarea>
    @if ($isText)
        <div class="tp-key"><b>پاسخ مرجع (برای تصحیح مدرس)</b><textarea rows="3" name="questions[{{ $i }}][expected_answer]">{{ $q['expected_answer'] ?? '' }}</textarea></div>
    @else
        <div class="tp-question-options">
            @foreach ($options as $index => $option)
                <label class="tp-question-option">
                    @if ($isMultiple)
                        <input type="checkbox" name="questions[{{ $i }}][correct_answer][]" value="{{ $option }}" data-tp-correct @checked(in_array($option, $correctValues, true)) aria-label="پاسخ صحیح">
                    @else
                        <input type="radio" name="questions[{{ $i }}][correct_answer]" value="{{ $option }}" data-tp-correct @checked($option !== '' && in_array($option, $correctValues, true)) aria-label="پاسخ صحیح">
                    @endif
                    <input type="text" name="questions[{{ $i }}][options][{{ $index }}]" value="{{ $option }}" placeholder="گزینه {{ \App\Support\PersianUi::digits($index + 1) }}" data-tp-option-input>
                </label>
            @endforeach
        </div>
        <small class="tp-muted">{{ $isMultiple ? 'تمام پاسخ‌های صحیح را علامت بزن.' : 'گزینه صحیح را با دکمه کنار آن انتخاب کن.' }}</small>
    @endif
</div>