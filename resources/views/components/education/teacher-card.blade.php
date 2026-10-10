@props([
    'name' => 'نام مدرس',
    'role' => 'مدرس',
    'avatar' => null,
    'bio' => null,
    'courses' => null,
    'href' => '#',
])

@php
    $displayName = trim((string) $name) ?: 'مدرس شیخان';
    $initial = mb_substr($displayName, 0, 1);
@endphp

<article {{ $attributes->class(['teacher-card edu-card group h-full min-w-0']) }}>
    <a
        href="{{ $href }}"
        class="teacher-card__media"
        aria-label="مشاهده پروفایل {{ $displayName }}"
    >
        @if($avatar)
            <img
                src="{{ $avatar }}"
                alt="تصویر {{ $displayName }}"
                class="teacher-card__photo"
                loading="lazy"
                decoding="async"
            >
        @else
            <span class="teacher-card__fallback" aria-hidden="true">{{ $initial }}</span>
        @endif

        <span class="teacher-card__media-overlay" aria-hidden="true"></span>
        <span class="teacher-card__media-action">پروفایل مدرس <span aria-hidden="true">↗</span></span>
    </a>

    <div class="teacher-card__body">
        <div class="teacher-card__identity">
            <h3 class="teacher-card__name">
                <a href="{{ $href }}">{{ $displayName }}</a>
            </h3>

            @if(filled($role))
                <p class="teacher-card__role">{{ $role }}</p>
            @endif
        </div>

        @if(filled($bio))
            <p class="teacher-card__bio">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $bio)), 155) }}</p>
        @endif

        <div class="teacher-card__footer">
            <span class="teacher-card__course-count">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"/>
                    <path stroke-linecap="round" d="M4 5.5v16M8 7h8M8 10.5h8"/>
                </svg>
                {{ \App\Support\PersianUi::digits((int) ($courses ?? 0)) }} دوره آموزشی
            </span>

            <a href="{{ $href }}" class="teacher-card__action">
                دیدن پروفایل <span aria-hidden="true">←</span>
            </a>
        </div>
    </div>
</article>
