@props([
'name',
'avatar' => null,
'specialty' => null,
'bio' => null,
'experience' => null,
'coursesCount' => null,
'studentsCount' => null,
'rating' => null,
'href' => '#',
'verified' => false,
])

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
            p-5
            shadow-[var(--shadow-xs)]
            transition-all
            duration-200
            hover:-translate-y-1
            hover:border-[var(--color-brand-200)]
            hover:shadow-[var(--shadow-md)]
        ',
    ]) }}
    dir="rtl"
>
    {{-- Top --}}
    <div class="flex items-start justify-between gap-4">

        <div class="flex min-w-0 items-center gap-3">
            <x-ui.avatar
                :src="$avatar"
                :name="$name"
                size="lg"
                class="ring-2 ring-[var(--color-brand-50)]"
            />

            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <a
                        href="{{ $href }}"
                        class="truncate text-base font-extrabold text-[var(--color-text-primary)] transition-colors duration-200 hover:text-[var(--color-brand-600)]"
                    >
                        {{ $name }}
                    </a>

                    @if($verified)
                        <span
                            class="shrink-0 text-[var(--color-brand-600)]"
                            title="مدرس تأییدشده"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-label="مدرس تأییدشده"
                            >
                                <path
                                    d="M12 3.5 14.6 5l3-.1.9 2.8 2.3 1.9-1.2 2.7.5 3-2.6 1.5-1.2 2.7-3-.7-2.7 1.3-2.1-2.2-3-.4-.3-3-2-2.3 1.5-2.6-.2-3 2.8-.9 1.6-2.6 3 .4L12 3.5Z"
                                />
                                <path
                                    d="m8.5 12 2.2 2.2 4.8-5"
                                />
                            </svg>
                        </span>
                    @endif
                </div>

                @if($specialty)
                    <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                        {{ $specialty }}
                    </p>
                @endif
            </div>
        </div>

        @if($rating !== null)
            <div class="flex shrink-0 items-center gap-1 rounded-full bg-[var(--color-warning-50)] px-2.5 py-1 text-xs font-bold text-[var(--color-warning-700)]">
                <svg
                    class="h-3.5 w-3.5 fill-current"
                    viewBox="0 0 20 20"
                    aria-hidden="true"
                >
                    <path d="m10 1.8 2.5 5.1 5.6.8-4 4 1 5.6-5.1-2.7-5.1 2.7 1-5.6-4-4 5.6-.8L10 1.8Z" />
                </svg>

                {{ $rating }}
            </div>
        @endif
    </div>

    {{-- Bio --}}
    @if($bio)
        <p class="mt-5 line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">
            {{ $bio }}
        </p>
    @endif

    {{-- Stats --}}
    @if($experience || $coursesCount || $studentsCount)
        <div class="mt-5 grid grid-cols-3 divide-x divide-[var(--color-border)] divide-x-reverse border-y border-[var(--color-border)] py-4">

            @if($experience)
                <div class="px-2 text-center first:pr-0 last:pl-0">
                    <p class="text-base font-extrabold text-[var(--color-text-primary)]">
                        {{ $experience }}
                    </p>

                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                        سال تجربه
                    </p>
                </div>
            @endif

            @if($coursesCount)
                <div class="px-2 text-center first:pr-0 last:pl-0">
                    <p class="text-base font-extrabold text-[var(--color-text-primary)]">
                        {{ $coursesCount }}
                    </p>

                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                        دوره
                    </p>
                </div>
            @endif

            @if($studentsCount)
                <div class="px-2 text-center first:pr-0 last:pl-0">
                    <p class="text-base font-extrabold text-[var(--color-text-primary)]">
                        {{ $studentsCount }}
                    </p>

                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                        دانش‌آموز
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- Footer --}}
    <div class="mt-auto pt-5">
        <x-ui.button
            :href="$href"
            variant="secondary"
            size="sm"
            full-width
        >
            مشاهده پروفایل

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
</article>
