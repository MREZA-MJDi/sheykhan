@props(['challenge'])

@php
    $result = session('hero_challenge_result');
    $hasResult = is_array($result) && (int) ($result['id'] ?? 0) === (int) $challenge['id'];
    $isCorrect = $hasResult && !empty($result['correct']);
@endphp

<div
    class="shx-public-puzzle"
    id="hero-challenge"
    data-hero-challenge
    data-answer-endpoint="{{ url('/hero-challenges') }}"
    data-courses-url="{{ route('courses.index') }}"
    data-correct-label="درست است!"
    data-incorrect-label="این گزینه درست نبود؛ دوباره امتحان کن."
    data-incorrect-hint="صورت سؤال را دوباره با دقت بخوان و به جزئیات آن توجه کن."
    data-next-label="سؤال بعدی ←"
    data-complete-label="هر سه چالش را پشت سر گذاشتی."
    data-courses-label="دیدن دوره‌ها ←"
    data-network-error="ارتباط با سرور برقرار نشد. دوباره تلاش کن."
>
    <div class="shx-public-puzzle__top">
        <span data-challenge-position>سؤال {{ \App\Support\PersianUi::digits($challenge['position']) }} از {{ \App\Support\PersianUi::digits($challenge['total']) }}</span>
        <span data-challenge-kind>{{ empty($challenge['sequence']) ? 'چالش منطق' : 'الگوی بعدی چیست؟' }}</span>
    </div>

    <h2 data-challenge-title>{{ $challenge['title'] }}</h2>
    <p class="shx-public-puzzle__prompt" data-challenge-prompt>{{ $challenge['prompt'] }}</p>

    <div class="shx-public-puzzle__sequence" data-challenge-sequence aria-label="دنباله سؤال" @if(empty($challenge['sequence'])) hidden @endif>
        @foreach($challenge['sequence'] as $item)
            <span>{{ $item }}</span>
        @endforeach
        <span class="is-question">؟</span>
    </div>

    <div
        class="shx-public-puzzle__feedback {{ $isCorrect ? 'is-correct' : ($hasResult ? 'is-incorrect' : '') }}"
        data-challenge-feedback
        role="status"
        aria-live="polite"
        @if(!$hasResult) hidden @endif
    >
        @if($hasResult && $isCorrect)
            <strong>درست است!</strong>
            <p>{{ $result['explanation'] }}</p>
            @if(!empty($result['next_id']))
                <button class="shx-public-puzzle__next" type="button" data-next-challenge="{{ $result['next_id'] }}">سؤال بعدی ←</button>
            @else
                <p>هر سه چالش را پشت سر گذاشتی.</p>
                <a class="shx-public-puzzle__next" href="{{ route('courses.index') }}">دیدن دوره‌ها ←</a>
            @endif
        @elseif($hasResult)
            <strong>این گزینه درست نبود؛ دوباره امتحان کن.</strong>
            <p>صورت سؤال را دوباره با دقت بخوان و به جزئیات آن توجه کن.</p>
        @endif
    </div>

    <form method="POST" action="{{ route('hero-challenges.answer', $challenge['id']) }}" class="shx-public-puzzle__options" data-hero-challenge-form @if($isCorrect) hidden @endif>
        @csrf
        @foreach($challenge['options'] as $index => $option)
            <button
                type="submit"
                name="option_id"
                value="{{ $option['id'] }}"
                @if($hasResult && (int) ($result['option_id'] ?? 0) === (int) $option['id']) disabled aria-disabled="true" class="is-incorrect" @endif
                aria-label="گزینه {{ \App\Support\PersianUi::digits($index + 1) }}: {{ $option['content'] }}"
            >{{ $option['content'] }}</button>
        @endforeach
    </form>

    @error('option_id')
        <p class="shx-public-puzzle__error" role="alert">{{ $message }}</p>
    @enderror
    <p class="shx-public-puzzle__hint">بررسی پاسخ در سرور انجام می‌شود.</p>
</div>