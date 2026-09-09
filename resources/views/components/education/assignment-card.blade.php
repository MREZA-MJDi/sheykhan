@props([
'title',
'description' => null,
'course' => null,
'dueDate' => null,
'dueTime' => null,
'status' => 'pending',
'score' => null,
'maxScore' => null,
'href' => '#',
])

@php
    $statusConfig = [
        'pending' => [
            'label' => 'انجام نشده',
            'variant' => 'warning',
        ],
        'submitted' => [
            'label' => 'تحویل داده شده',
            'variant' => 'info',
        ],
        'graded' => [
            'label' => 'تصحیح شده',
            'variant' => 'success',
        ],
        'late' => [
            'label' => 'با تأخیر',
            'variant' => 'danger',
        ],
        'closed' => [
            'label' => 'بسته شده',
            'variant' => 'neutral',
        ],
    ];

    $currentStatus = $statusConfig[$status] ?? $statusConfig['pending'];
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
            hover:border-[var(--color-border-strong)]
            hover:shadow-[var(--shadow-md)]
        ',
    ]) }}
    dir="rtl"
>
    <div class="p-5">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">

            <div class="flex min-w-0 items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--color-brand-50)] text-[var(--color-brand-600)]">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <path d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                        <path d="M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h4.5" />
                    </svg>
                </div>

                <div class="min-w-0">
                    @if($course)
                        <p class="mb-1 text-xs font-medium text-[var(--color-text-muted)]">
                            {{ $course }}
                        </p>
                    @endif

                    <a
                        href="{{ $href }}"
                        class="block"
                    >
                        <h3 class="line-clamp-2 text-base font-extrabold leading-7 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]">
                            {{ $title }}
                        </h3>
                    </a>
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
            <p class="mt-4 line-clamp-2 text-sm leading-6 text-[var(--color-text-secondary)]">
                {{ $description }}
            </p>
        @endif

        {{-- Due date --}}
        @if($dueDate || $dueTime)
            <div class="mt-5 rounded-xl bg-[var(--color-neutral-50)] p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">

                    <div>
                        <p class="text-xs font-medium text-[var(--color-text-muted)]">
                            مهلت تحویل
                        </p>

                        @if($dueDate)
                            <p class="mt-1 text-sm font-bold text-[var(--color-text-primary)]">
                                {{ $dueDate }}
                            </p>
                        @endif
                    </div>

                    @if($dueTime)
                        <div class="flex items-center gap-2 text-sm font-semibold text-[var(--color-text-primary)]">
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

                            {{ $dueTime }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Score --}}
        @if($score !== null)
            <div class="mt-5 flex items-center justify-between rounded-xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] px-4 py-3">
                <span class="text-sm font-medium text-[var(--color-success-700)]">
                    نمره
                </span>

                <span class="text-base font-extrabold text-[var(--color-success-700)]">
                    {{ $score }}

                    @if($maxScore !== null)
                        <span class="text-xs font-medium">
                            از {{ $maxScore }}
                        </span>
                    @endif
                </span>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="mt-auto border-t border-[var(--color-border)] p-4">

        @if($status === 'pending')
            <x-ui.button
                :href="$href"
                full-width
            >
                مشاهده و ارسال تکلیف

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
        @elseif($status === 'submitted')
            <x-ui.button
                :href="$href"
                variant="soft"
                full-width
            >
                مشاهده تکلیف
            </x-ui.button>
        @elseif($status === 'graded')
            <x-ui.button
                :href="$href"
                variant="secondary"
                full-width
            >
                مشاهده نتیجه
            </x-ui.button>
        @elseif($status === 'late')
            <x-ui.button
                :href="$href"
                variant="danger"
                full-width
            >
                مشاهده تکلیف
            </x-ui.button>
        @else
            <x-ui.button
                :href="$href"
                variant="secondary"
                full-width
            >
                مشاهده جزئیات
            </x-ui.button>
        @endif

    </div>
</article>
