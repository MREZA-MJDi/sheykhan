@props([
'title',
'subtitle' => null,
'image' => null,
'teacher' => null,
'progress' => 0,
'completedLessons' => null,
'totalLessons' => null,
'lastLesson' => null,
'href' => '#',
])

@php
    $progress = max(0, min(100, (int) $progress));

    if ($progress >= 100) {
        $statusLabel = 'دوره تکمیل شده';
        $statusVariant = 'success';
    } elseif ($progress > 0) {
        $statusLabel = 'در حال یادگیری';
        $statusVariant = 'brand';
    } else {
        $statusLabel = 'شروع نشده';
        $statusVariant = 'neutral';
    }
@endphp

<article
    {{ $attributes->merge([
        'class' => '
            group
            overflow-hidden
            rounded-2xl
            border
            border-[var(--color-border)]
            bg-[var(--color-surface)]
            shadow-[var(--shadow-xs)]
            transition-all
            duration-200
            hover:border-[var(--color-brand-200)]
            hover:shadow-[var(--shadow-md)]
        ',
    ]) }}
    dir="rtl"
>
    <div class="p-5 sm:p-6">

        {{-- Header --}}
        <div class="flex items-start gap-4">

            @if($image)
                <a
                    href="{{ $href }}"
                    class="block shrink-0 overflow-hidden rounded-xl"
                    aria-label="{{ $title }}"
                >
                    <img
                        src="{{ $image }}"
                        alt="{{ $title }}"
                        loading="lazy"
                        class="h-20 w-28 object-cover transition-transform duration-300 group-hover:scale-105 sm:h-24 sm:w-36"
                    >
                </a>
            @else
                <div class="flex h-20 w-28 shrink-0 items-center justify-center rounded-xl bg-[var(--color-brand-50)] text-[var(--color-brand-600)] sm:h-24 sm:w-36">
                    <svg
                        class="h-8 w-8"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                        aria-hidden="true"
                    >
                        <path d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                        <path d="M8 9h8M8 13h6M8 17h4" />
                    </svg>
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge
                        :variant="$statusVariant"
                        size="sm"
                    >
                        {{ $statusLabel }}
                    </x-ui.badge>
                </div>

                <a
                    href="{{ $href }}"
                    class="mt-2 block"
                >
                    <h3 class="line-clamp-2 text-lg font-extrabold leading-8 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]">
                        {{ $title }}
                    </h3>
                </a>

                @if($subtitle)
                    <p class="mt-1 line-clamp-1 text-sm text-[var(--color-text-muted)]">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Progress --}}
        <div class="mt-6">
            <div class="mb-2 flex items-center justify-between gap-4">
                <span class="text-sm font-semibold text-[var(--color-text-primary)]">
                    پیشرفت شما
                </span>

                <span class="text-sm font-extrabold tabular-nums text-[var(--color-brand-600)]">
                    {{ $progress }}٪
                </span>
            </div>

            <x-ui.progress
                :value="$progress"
                :show-value="false"
                size="md"
            />
        </div>

        {{-- Lesson stats --}}
        @if($completedLessons !== null || $totalLessons !== null || $lastLesson)
            <div class="mt-5 grid gap-3 sm:grid-cols-2">

                @if($completedLessons !== null || $totalLessons !== null)
                    <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                        <p class="text-xs font-medium text-[var(--color-text-muted)]">
                            جلسات
                        </p>

                        <p class="mt-1 text-sm font-bold text-[var(--color-text-primary)]">
                            {{ $completedLessons ?? 0 }}

                            @if($totalLessons !== null)
                                <span class="font-medium text-[var(--color-text-muted)]">
                                    از {{ $totalLessons }}
                                </span>
                            @endif
                        </p>
                    </div>
                @endif

                @if($lastLesson)
                    <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                        <p class="text-xs font-medium text-[var(--color-text-muted)]">
                            آخرین درس
                        </p>

                        <p class="mt-1 truncate text-sm font-bold text-[var(--color-text-primary)]">
                            {{ $lastLesson }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Teacher --}}
        @if($teacher)
            <div class="mt-5 flex items-center gap-3 border-t border-[var(--color-border)] pt-5">
                <x-ui.avatar
                    :name="is_array($teacher) ? ($teacher['name'] ?? null) : $teacher"
                    :src="is_array($teacher) ? ($teacher['avatar'] ?? null) : null"
                    size="sm"
                />

                <div class="min-w-0">
                    <p class="text-xs text-[var(--color-text-muted)]">
                        مدرس دوره
                    </p>

                    <p class="truncate text-sm font-semibold text-[var(--color-text-primary)]">
                        {{ is_array($teacher) ? ($teacher['name'] ?? '') : $teacher }}
                    </p>
                </div>
            </div>
        @endif

        {{-- CTA --}}
        <div class="mt-6">
            <x-ui.button
                :href="$href"
                :variant="$progress >= 100 ? 'secondary' : 'primary'"
                full-width
            >
                @if($progress >= 100)
                    مرور دوره
                @elseif($progress > 0)
                    ادامه یادگیری
                @else
                    شروع دوره
                @endif

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
        </div>

    </div>
</article>
