@props(['challenge'])

@php
    $result = session('hero_challenge_result');
    $hasResult = is_array($result) && (int) ($result['id'] ?? 0) === (int) $challenge['id'];
    $isCorrect = $hasResult && !empty($result['correct']);
@endphp
<div class="shx-public-puzzle" id="hero-challenge">
    <div class="shx-public-puzzle__top">
        <span>سؤال {{ \App\Support\PersianUi::digits($challenge['position']) }} از {{ \App\Support\PersianUi::digits($challenge['total']) }}</span>
        <span>{{ empty($challenge['sequence']) ? 'چالش منطق' : 'الگوی بعدی چیست؟' }}</span>
    </div>
    <h2>{{ $challenge['title'] }}</h2>
    <p class="shx-public-puzzle__prompt">{{ $challenge['prompt'] }}</p>
    @if(!empty($challenge['sequence']))
        <div class="shx-public-puzzle__sequence" aria-label="دنباله سؤال">
            @foreach($challenge['sequence'] as $item)<span>{{ $item }}</span>@endforeach
            <span class="is-question">؟</span>
        </div>
    @endif
    @if($hasResult)
        <div class="shx-public-puzzle__feedback {{ $isCorrect ? 'is-correct' : 'is-incorrect' }}" role="status" aria-live="polite">
            @if($isCorrect)
                <strong>درست است!</strong><p>{{ $result['explanation'] }}</p>
                @if(!empty($result['next_id']))
                    <a class="shx-public-puzzle__next" href="{{ route('home', ['challenge_id' => $result['next_id']]) }}#hero-challenge">سؤال بعدی ←</a>
                @else
                    <p>هر سه چالش را پشت سر گذاشتی.</p><a class="shx-public-puzzle__next" href="{{ route('courses.index') }}">دیدن دوره‌ها ←</a>
                @endif
            @else
                <strong>این گزینه درست نبود؛ دوباره امتحان کن.</strong><p>صورت سؤال را دوباره با دقت بخوان و به جزئیات آن توجه کن.</p>
            @endif
        </div>
    @endif
    @if(!$isCorrect)
        <form method="POST" action="{{ route('hero-challenges.answer', $challenge['id']) }}" class="shx-public-puzzle__options">
            @csrf
            @foreach($challenge['options'] as $index => $option)
                <button type="submit" name="option_id" value="{{ $option['id'] }}"
                    @if($hasResult && (int) ($result['option_id'] ?? 0) === (int) $option['id']) disabled aria-disabled="true" @endif
                    aria-label="گزینه {{ \App\Support\PersianUi::digits($index + 1) }}: {{ $option['content'] }}">{{ $option['content'] }}</button>
            @endforeach
        </form>
        @error('option_id')<p class="shx-public-puzzle__error" role="alert">{{ $message }}</p>@enderror
    @endif
    <p class="shx-public-puzzle__hint">بررسی پاسخ در سرور انجام می‌شود.</p>
</div>
