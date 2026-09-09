@props([
'title',
'description' => null,
'course' => null,
'subject' => null,
'date' => null,
'startTime' => null,
'duration' => null,
'questionsCount' => null,
'status' => 'upcoming',
'score' => null,
'maxScore' => null,
'href' => '#',
])

@php
    $statusConfig = [
        'upcoming' => [
            'label' => 'پیش رو',
            'variant' => 'brand',
        ],
        'available' => [
            'label' => 'آماده شروع',
            'variant' => 'success',
        ],
        'in_progress' => [
            'label' => 'در حال انجام',
            'variant' => 'warning',
        ],
        'completed' => [
            'label' => 'انجام شده',
            'variant' => 'success',
        ],
        'expired' => [
            'label' => 'منقضی شده',
            'variant' => 'neutral',
        ],
    ];

    $currentStatus = $statusConfig[$status] ?? $statusConfig['upcoming'];
@endphp

<article
    {{ $attributes->merge([
        'class' => '
            group
            flex
            h-full
            flex-col
            overflow-hidden
            rounded-2xl
            border
            border-[var(--color-border)]
            bg-[var(--color-surface)]
            shadow-[var(--shadow-xs)]
            transition-all
            duration-200
            hover:-translate-y-0.5
            hover:border-[var(--color-brand-200)]
            hover:shadow-[var(--shadow-md)]
        ',
    ]) }}
    dir="rtl"
>
    <div class="p-5 sm:p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">

            <div class="flex min-w-0 items-start gap-3">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[var(--color-brand-50)] text-[var(--color-brand-600)]"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <path d="M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 6.75 4.5Z" />
                        <path d="M8.5 9h7M8.5 12h7M8.5 15h4.5" />
                    </svg>
                </div>

                <div class="min-w-0">
                    @if($course)
                        <p class="text-xs font-medium text-[var(--color-text-muted)]">
                            {{ $course }}
                        </p>
                    @endif

                    <a
                        href="{{ $href }}"
                        class="mt-1 block"
                    >
                        <h3 class="line-clamp-2 text-lg font-extrabold leading-8 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]">
                            {{ $title }}
                        </h3>
                    </a>

                    @if($subject)
                        <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                            {{ $subject }}
                        </p>
                    @endif
                </div>
            </div>

            <x-ui.badge
                :variant="$currentStatus['variant']"
                size="sm"
            >
                {{ $currentStatus['label'] }}
            </x-ui.badge>
        </div>

        {{-- Description --}}
        @if($description)
            <p class="mt-5 line-clamp-2 text-sm leading-6 text-[var(--color-text-secondary)]">
                {{ $description }}
            </p>
        @endif

        {{-- Exam info --}}
        <div class="mt-5 grid gap-3 sm:grid-cols-2">

            @if($date)
                <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                    <p class="text-xs font-medium text-[var(--color-text-muted)]">
                        تاریخ آزمون
                    </p>

                    <div class="mt-1.5 flex items-center gap-2">
                        <svg
                            class="h-4 w-4 text-[var(--color-brand-600)]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path d="M6.75 3v2.25M17.25 3v2.25" />
                            <path d="M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                            <path d="M3 11.25h18" />
                        </svg>

                        <span class="text-sm font-bold text-[var(--color-text-primary)]">
                            {{ $date }}
                        </span>
                    </div>
                </div>
            @endif

            @if($startTime)
                <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                    <p class="text-xs font-medium text-[var(--color-text-muted)]">
                        ساعت شروع
                    </p>

                    <div class="mt-1.5 flex items-center gap-2">
                        <svg
                            class="h-4 w-4 text-[var(--color-brand-600)]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="8.75" />
                            <path d="M12 7.5v5l3 1.75" />
                        </svg>

                        <span class="text-sm font-bold text-[var(--color-text-primary)]">
                            {{ $startTime }}
                        </span>
                    </div>
                </div>
            @endif

            @if($duration)
                <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                    <p class="text-xs font-medium text-[var(--color-text-muted)]">
                        مدت آزمون
                    </p>

                    <div class="mt-1.5 flex items-center gap-2">
                        <svg
                            class="h-4 w-4 text-[var(--color-brand-600)]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="8.75" />
                            <path d="M12 7.5v5l3 1.75" />
                        </svg>

                        <span class="text-sm font-bold text-[var(--color-text-primary)]">
                            {{ $duration }}
                        </span>
                    </div>
                </div>
            @endif

            @if($questionsCount)
                <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                    <p class="text-xs font-medium text-[var(--color-text-muted)]">
                        تعداد سوالات
                    </p>

                    <div class="mt-1.5 flex items-center gap-2">
                        <svg
                            class="h-4 w-4 text-[var(--color-brand-600)]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="8.75" />
                            <path d="M9.5 9a2.5 2.5 0 1 1 4.3 1.75c-.9.9-1.8 1.25-1.8 2.75" />
                            <path d="M12 16.5h.008" />
                        </svg>

                        <span class="text-sm font-bold text-[var(--color-text-primary)]">
                            {{ $questionsCount }} سوال
                        </span>
                    </div>
                </div>
            @endif

        </div>

        {{-- Score --}}
        @if($score !== null)
            <div class="mt-5 rounded-xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] p-4">
                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm font-medium text-[var(--color-success-700)]">
                        نتیجه آزمون
                    </span>

                    <span class="text-lg font-extrabold text-[var(--color-success-700)]">
                        {{ $score }}

                        @if($maxScore !== null)
                            <span class="text-xs font-medium">
                                / {{ $maxScore }}
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="mt-auto border-t border-[var(--color-border)] p-4">

        @if($status === 'available')
            <x-ui.button
                :href="$href"
                full-width
            >
                شروع آزمون

                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </x-ui.button>

        @elseif($status === 'in_progress')
            <x-ui.button
                :href="$href"
                variant="warning"
                full-width
            >
                ادامه آزمون
            </x-ui.button>

        @elseif($status === 'completed')
            <x-ui.button
                :href="$href"
                variant="secondary"
                full-width
            >
                مشاهده نتیجه
            </x-ui.button>

        @elseif($status === 'expired')
            <x-ui.button
                variant="secondary"
                full-width
                disabled
            >
                آزمون منقضی شده
            </x-ui.button>

        @else
            <x-ui.button
                :href="$href"
                variant="soft"
                full-width
            >
                مشاهده آزمون
            </x-ui.button>
        @endif

    </div>
</article>
