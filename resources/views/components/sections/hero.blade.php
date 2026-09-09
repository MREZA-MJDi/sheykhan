@props([
'eyebrow' => 'آموزش تخصصی تیزهوشان',
'title' => 'مسیر موفقیتت را با فرزین شروع کن',
'description' => 'دوره‌های تخصصی، کلاس‌های هدفمند و آموزش اصولی برای دانش‌آموزانی که می‌خواهند یک قدم جلوتر باشند.',
'primaryText' => 'مشاهده دوره‌ها',
'primaryHref' => '#',
'secondaryText' => 'آشنایی با فرزین',
'secondaryHref' => '#',
])

<section
    {{ $attributes->merge([
        'class' => '
            relative
            isolate
            overflow-hidden
            border-b
            border-[var(--color-border)]
            bg-[var(--color-surface)]
        ',
    ]) }}
    dir="rtl"
>
    {{-- Background decoration --}}
    <div
        class="pointer-events-none absolute inset-0 -z-10 overflow-hidden"
        aria-hidden="true"
    >
        <div
            class="absolute -right-32 -top-32 h-80 w-80 rounded-full bg-[var(--color-brand-100)]/60 blur-3xl"
        ></div>

        <div
            class="absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-[var(--color-info-50)] blur-3xl"
        ></div>

        <div
            class="absolute inset-x-0 top-1/2 h-px bg-gradient-to-l from-transparent via-[var(--color-brand-100)] to-transparent"
        ></div>
    </div>

    <div class="container-farzin">
        <div class="flex min-h-[620px] items-center py-16 sm:py-20 lg:py-24">

            <div class="grid w-full items-center gap-12 lg:grid-cols-12 lg:gap-8">

                {{-- Main content --}}
                <div class="max-w-3xl lg:col-span-7">

                    {{-- Eyebrow --}}
                    @if($eyebrow)
                        <div class="inline-flex items-center gap-2 rounded-full border border-[var(--color-brand-100)] bg-[var(--color-brand-50)] px-3.5 py-2">
                            <span class="h-2 w-2 rounded-full bg-[var(--color-brand-600)]"></span>

                            <span class="text-xs font-bold text-[var(--color-brand-700)] sm:text-sm">
                                {{ $eyebrow }}
                            </span>
                        </div>
                    @endif

                    {{-- Title --}}
                    <h1
                        class="mt-6 max-w-3xl text-4xl font-black leading-[1.35] tracking-tight text-[var(--color-text-primary)] sm:text-5xl lg:text-6xl"
                    >
                        {{ $title }}
                    </h1>

                    {{-- Description --}}
                    @if($description)
                        <p
                            class="mt-6 max-w-2xl text-base leading-8 text-[var(--color-text-secondary)] sm:text-lg"
                        >
                            {{ $description }}
                        </p>
                    @endif

                    {{-- Actions --}}
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">

                        <x-ui.button
                            :href="$primaryHref"
                            size="lg"
                        >
                            {{ $primaryText }}

                            <svg
                                class="h-4.5 w-4.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </x-ui.button>

                        <x-ui.button
                            :href="$secondaryHref"
                            variant="secondary"
                            size="lg"
                        >
                            {{ $secondaryText }}
                        </x-ui.button>
                    </div>

                    {{-- Trust indicators --}}
                    <div class="mt-10 flex flex-wrap items-center gap-x-7 gap-y-4">

                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path d="m5 12 4.5 4.5L19 7" />
                                </svg>
                            </div>

                            <span class="text-sm font-semibold text-[var(--color-text-secondary)]">
                                مدرسین متخصص
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-brand-50)] text-[var(--color-brand-600)]">
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                                    <path d="M8 9h8M8 13h6M8 17h4" />
                                </svg>
                            </div>

                            <span class="text-sm font-semibold text-[var(--color-text-secondary)]">
                                آموزش هدفمند
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-warning-50)] text-[var(--color-warning-600)]">
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path d="m12 3 2.5 5.2 5.75.85-4.15 4.05.98 5.72L12 16.1 6.92 18.82l.98-5.72-4.15-4.05 5.75-.85L12 3Z" />
                                </svg>
                            </div>

                            <span class="text-sm font-semibold text-[var(--color-text-secondary)]">
                                مسیر یادگیری منظم
                            </span>
                        </div>

                    </div>

                    {{-- Custom slot --}}
                    @if($slot->isNotEmpty())
                        <div class="mt-8">
                            {{ $slot }}
                        </div>
                    @endif

                </div>

                {{-- Visual area --}}
                <div class="relative lg:col-span-5">

                    <div class="relative mx-auto aspect-square w-full max-w-[440px]">

                        {{-- Main visual --}}
                        <div class="absolute inset-8 rounded-[2rem] border border-[var(--color-brand-100)] bg-white/80 p-5 shadow-[var(--shadow-lg)] backdrop-blur">

                            <div class="flex h-full flex-col justify-between rounded-[1.5rem] bg-[var(--color-brand-50)] p-6">

                                {{-- Top --}}
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-[var(--color-text-primary)]">
                                            مسیر یادگیری
                                        </span>

                                        <x-ui.badge
                                            variant="success"
                                            size="sm"
                                        >
                                            فعال
                                        </x-ui.badge>
                                    </div>

                                    <p class="mt-2 text-xs leading-5 text-[var(--color-text-muted)]">
                                        پیشرفتت را قدم‌به‌قدم دنبال کن.
                                    </p>
                                </div>

                                {{-- Progress --}}
                                <div>
                                    <div class="flex items-end justify-between">
                                        <div>
                                            <p class="text-xs text-[var(--color-text-muted)]">
                                                پیشرفت این هفته
                                            </p>

                                            <p class="mt-1 text-3xl font-black text-[var(--color-brand-700)]">
                                                ۷۸٪
                                            </p>
                                        </div>

                                        <div class="text-xs font-semibold text-[var(--color-success-600)]">
                                            عالی پیش می‌ری!
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <x-ui.progress
                                            :value="78"
                                            :show-value="false"
                                            size="md"
                                        />
                                    </div>
                                </div>

                                {{-- Mini lessons --}}
                                <div class="space-y-2">

                                    <div class="flex items-center gap-3 rounded-xl border border-[var(--color-border)] bg-white p-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                                            ✓
                                        </span>

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-bold text-[var(--color-text-primary)]">
                                                هوش کلامی
                                            </p>

                                            <p class="text-[11px] text-[var(--color-text-muted)]">
                                                تکمیل شده
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-xl border border-[var(--color-brand-100)] bg-white p-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-brand-50)] text-[var(--color-brand-600)]">
                                            →
                                        </span>

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-bold text-[var(--color-text-primary)]">
                                                هوش تصویری
                                            </p>

                                            <p class="text-[11px] text-[var(--color-text-muted)]">
                                                ادامه یادگیری
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-xl border border-[var(--color-border)] bg-white p-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-neutral-100)] text-[var(--color-text-muted)]">
                                            ۳
                                        </span>

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-bold text-[var(--color-text-primary)]">
                                                آزمون جامع
                                            </p>

                                            <p class="text-[11px] text-[var(--color-text-muted)]">
                                                به‌زودی
                                            </p>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        {{-- Floating stat --}}
                        <div class="absolute right-0 top-12 rounded-2xl border border-[var(--color-border)] bg-white px-4 py-3 shadow-[var(--shadow-md)]">
                            <p class="text-[11px] text-[var(--color-text-muted)]">
                                دوره‌های فعال
                            </p>

                            <p class="mt-1 text-lg font-black text-[var(--color-text-primary)]">
                                ۱۲ دوره
                            </p>
                        </div>

                        {{-- Floating teacher --}}
                        <div class="absolute bottom-8 left-0 flex items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-white px-4 py-3 shadow-[var(--shadow-md)]">
                            <x-ui.avatar
                                name="استاد احمدی"
                                size="sm"
                            />

                            <div>
                                <p class="text-[11px] text-[var(--color-text-muted)]">
                                    مدرس
                                </p>

                                <p class="text-xs font-bold text-[var(--color-text-primary)]">
                                    اساتید متخصص
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>
</section>
