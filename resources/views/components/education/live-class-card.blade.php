@props([
'title',
'teacher' => null,
'subject' => null,
'grade' => null,
'date' => null,
'startTime' => null,
'endTime' => null,
'status' => 'upcoming',
'studentsCount' => null,
'href' => '#',
])

@php
    $statusConfig = [
        'live' => [
            'label' => 'همین حالا در حال برگزاری',
            'badge' => 'danger',
            'dot' => 'bg-[var(--color-danger-500)]',
        ],
        'upcoming' => [
            'label' => 'کلاس بعدی',
            'badge' => 'brand',
            'dot' => 'bg-[var(--color-brand-500)]',
        ],
        'starting' => [
            'label' => 'به‌زودی شروع می‌شود',
            'badge' => 'warning',
            'dot' => 'bg-[var(--color-warning-500)]',
        ],
        'completed' => [
            'label' => 'برگزار شد',
            'badge' => 'success',
            'dot' => 'bg-[var(--color-success-500)]',
        ],
    ];

    $currentStatus = $statusConfig[$status] ?? $statusConfig['upcoming'];
@endphp

<article
    {{ $attributes->merge([
        'class' => '
            relative
            overflow-hidden
            rounded-2xl
            border
            border-[var(--color-border)]
            bg-[var(--color-surface)]
            shadow-[var(--shadow-sm)]
        ',
    ]) }}
    dir="rtl"
>
    {{-- Live indicator --}}
    @if($status === 'live')
        <div class="absolute inset-x-0 top-0 h-1 bg-[var(--color-danger-500)]"></div>
    @endif

    <div class="p-5 sm:p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge
                        :variant="$currentStatus['badge']"
                        size="sm"
                    >
                        <span class="relative flex h-2 w-2">
                            @if($status === 'live')
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $currentStatus['dot'] }} opacity-75"></span>
                            @endif

                            <span class="relative inline-flex h-2 w-2 rounded-full {{ $currentStatus['dot'] }}"></span>
                        </span>

                        {{ $currentStatus['label'] }}
                    </x-ui.badge>

                    @if($subject)
                        <x-ui.badge
                            variant="neutral"
                            size="sm"
                        >
                            {{ $subject }}
                        </x-ui.badge>
                    @endif
                </div>

                <a
                    href="{{ $href }}"
                    class="mt-3 block"
                >
                    <h3 class="text-xl font-extrabold leading-8 text-[var(--color-text-primary)] transition-colors duration-200 hover:text-[var(--color-brand-700)]">
                        {{ $title }}
                    </h3>
                </a>

                @if($grade)
                    <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                        {{ $grade }}
                    </p>
                @endif
            </div>

            {{-- Live icon --}}
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
                {{ $status === 'live'
                    ? 'bg-[var(--color-danger-50)] text-[var(--color-danger-600)]'
                    : 'bg-[var(--color-brand-50)] text-[var(--color-brand-600)]' }}"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <rect x="3.5" y="5" width="17" height="14" rx="2" />
                    <path d="m10 9 5 3-5 3V9Z" />
                </svg>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="mt-6 grid gap-3 sm:grid-cols-2">

            @if($date)
                <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                    <p class="text-xs font-medium text-[var(--color-text-muted)]">
                        تاریخ
                    </p>

                    <div class="mt-1 flex items-center gap-2">
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
                        ساعت کلاس
                    </p>

                    <div class="mt-1 flex items-center gap-2">
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

                            @if($endTime)
                                تا {{ $endTime }}
                            @endif
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Teacher + students --}}
        <div class="mt-5 flex flex-wrap items-center justify-between gap-4 border-t border-[var(--color-border)] pt-5">

            @if($teacher)
                <div class="flex items-center gap-3">
                    <x-ui.avatar
                        :name="is_array($teacher) ? ($teacher['name'] ?? null) : $teacher"
                        :src="is_array($teacher) ? ($teacher['avatar'] ?? null) : null"
                        size="sm"
                    />

                    <div class="min-w-0">
                        <p class="text-xs text-[var(--color-text-muted)]">
                            مدرس
                        </p>

                        <p class="truncate text-sm font-semibold text-[var(--color-text-primary)]">
                            {{ is_array($teacher) ? ($teacher['name'] ?? '') : $teacher }}
                        </p>
                    </div>
                </div>
            @endif

            @if($studentsCount)
                <div class="flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        <path d="M4.5 20.25a8.25 8.25 0 0 1 15 0" />
                    </svg>

                    {{ $studentsCount }} دانش‌آموز
                </div>
            @endif
        </div>

        {{-- CTA --}}
        <div class="mt-6">
            @if($status === 'live')
                <x-ui.button
                    :href="$href"
                    size="lg"
                    full-width
                >
                    ورود به کلاس

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
            @elseif($status === 'completed')
                <x-ui.button
                    :href="$href"
                    variant="secondary"
                    full-width
                >
                    مشاهده جزئیات
                </x-ui.button>
            @else
                <x-ui.button
                    :href="$href"
                    variant="soft"
                    full-width
                >
                    مشاهده کلاس
                </x-ui.button>
            @endif
        </div>
    </div>
</article>
