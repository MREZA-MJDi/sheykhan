@extends('layouts.app')

@section('title', $course->title . ' | شیخان')
@section('description', $course->short_description ?: 'جزئیات دوره ' . $course->title)

@section('content')
    @php
        $heroMedia = $course->media->first();
        $teacher = $course->teachers->first();

        $lessonCount = $course->sections->sum(
            fn ($section) => $section->lessons->count()
        );

        $sectionCount = $course->sections->count();

        $durationLabel = $course->duration_minutes > 0
            ? (
                intdiv((int) $course->duration_minutes, 60) > 0 && ((int) $course->duration_minutes % 60) > 0
                    ? \App\Support\PersianUi::digits(intdiv((int) $course->duration_minutes, 60)) . ' ساعت و ' . \App\Support\PersianUi::digits((int) $course->duration_minutes % 60) . ' دقیقه'
                    : (intdiv((int) $course->duration_minutes, 60) > 0
                        ? \App\Support\PersianUi::digits(intdiv((int) $course->duration_minutes, 60)) . ' ساعت'
                        : \App\Support\PersianUi::digits((int) $course->duration_minutes) . ' دقیقه')
            )
            : 'مدت زمان متغیر';

        $accessLabel = $canAccessContent
            ? 'دسترسی فعال'
            : ($requiresPayment ? 'نیازمند پرداخت' : 'ورود لازم است');
    @endphp

    <section class="public-course-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">

                {{-- Breadcrumb / Back --}}
                <div class="flex items-center justify-between gap-4">
                    <a
                        href="{{ route('courses.index') }}"
                        class="group inline-flex min-h-10 items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 text-sm font-bold text-[var(--color-text-muted)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]"
                    >
                        <span
                            aria-hidden="true"
                            class="transition-transform duration-200 group-hover:translate-x-1"
                        >
                            →
                        </span>

                        <span>بازگشت به دوره‌ها</span>
                    </a>
                </div>

                {{-- Course Hero --}}
                <div class="public-course-hero mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start lg:gap-10">

                    {{-- Main information --}}
                    <div class="min-w-0">

                        {{-- Tags --}}
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($course->grades as $grade)
                                <span class="rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                                    {{ $grade->title }}
                                </span>
                            @endforeach

                            @if($course->academy)
                                <span class="rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3 py-1.5 text-xs font-bold text-[var(--color-primary-700)]">
                                    {{ $course->academy->name }}
                                </span>
                            @endif

                            @if($course->level)
                                <span class="rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                                    {{ $course->level }}
                                </span>
                            @endif

                            <span
                                class="rounded-full px-3 py-1.5 text-xs font-bold
                                    {{ $course->isFree()
                                        ? 'bg-[var(--color-success-50)] text-[var(--color-success-700)]'
                                        : 'bg-[var(--color-warning-50)] text-[var(--color-warning-700)]' }}"
                            >
                                {{ $course->isFree() ? 'رایگان' : 'پولی' }}
                            </span>
                        </div>

                        {{-- Title --}}
                        <h1 class="mt-5 max-w-4xl text-3xl font-black leading-[1.35] tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            {{ $course->title }}
                        </h1>

                        {{-- Short description --}}
                        @if($course->short_description ?: $course->description)
                            <p class="mt-5 max-w-3xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base lg:text-lg">
                                {{ $course->short_description ?: $course->description }}
                            </p>
                        @endif

                        {{-- Meta --}}
                        <div class="mt-7 grid grid-cols-2 gap-3 sm:flex sm:flex-wrap">
                            @if($teacher)
                                <div class="flex min-w-0 items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <circle cx="12" cy="8" r="3.5"/>
                                            <path stroke-linecap="round" d="M5 20a7 7 0 0 1 14 0"/>
                                        </svg>
                                    </span>

                                    <div class="min-w-0">
                                        <span class="block text-[11px] font-semibold text-[var(--color-text-muted)]">
                                            مدرس
                                        </span>
                                        <strong class="block truncate text-sm font-black text-[var(--color-text)]">
                                            {{ $teacher->name }}
                                        </strong>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"
                                        />
                                    </svg>
                                </span>

                                <div>
                                    <span class="block text-[11px] font-semibold text-[var(--color-text-muted)]">
                                        درس
                                    </span>

                                    <strong class="block text-sm font-black text-[var(--color-text)]">
                                        {{ \App\Support\PersianUi::digits($lessonCount) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true"
                                    >
                                        <circle cx="12" cy="12" r="8.5"/>
                                        <path stroke-linecap="round" d="M12 7v5l3 2"/>
                                    </svg>
                                </span>

                                <div>
                                    <span class="block text-[11px] font-semibold text-[var(--color-text-muted)]">
                                        مدت دوره
                                    </span>

                                    <strong class="block text-sm font-black text-[var(--color-text)]">
                                        {{ $durationLabel }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        {{-- About --}}
                        @if($course->description)
                            <section
                                class="mt-10 overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_8px_30px_rgba(15,23,42,.04)]"
                                aria-labelledby="course-description-title"
                            >
                                <div class="border-b border-[var(--color-border)] px-5 py-5 sm:px-7">
                                    <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                        معرفی دوره
                                    </span>

                                    <h2
                                        id="course-description-title"
                                        class="mt-1 text-xl font-black text-[var(--color-text)] sm:text-2xl"
                                    >
                                        درباره این دوره
                                    </h2>
                                </div>

                                <div class="px-5 py-6 sm:px-7 sm:py-8">
                                    <div class="text-sm leading-9 text-[var(--color-text-secondary)] sm:text-base">
                                        {!! nl2br(e($course->description)) !!}
                                    </div>
                                </div>
                            </section>
                        @endif
                    </div>

                    {{-- Purchase / Access card --}}
                    <aside class="lg:sticky lg:top-24">
                        <div class="overflow-hidden rounded-[1.75rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_20px_60px_rgba(15,23,42,.10)]">

                            {{-- Cover --}}
                            <div class="relative aspect-[16/10] overflow-hidden bg-[var(--color-slate-100)]">
                                @if($heroMedia?->url())
                                    <img
                                        src="{{ $heroMedia->url() }}"
                                        alt="{{ $course->title }}"
                                        class="h-full w-full object-cover"
                                        fetchpriority="high"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center bg-[radial-gradient(circle_at_25%_20%,rgba(104,121,245,.22),transparent_42%),var(--color-slate-100)] text-[var(--color-primary-600)]">
                                        <svg
                                            class="h-14 w-14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            aria-hidden="true"
                                        >
                                            <path d="M4 5h16v14H4z"/>
                                            <path d="m4 15 4-4 3 3 3-4 6 6"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent p-5">
                                    <span class="inline-flex rounded-full border border-white/20 bg-black/35 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur">
                                        {{ $accessLabel }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-5 sm:p-7">

                                {{-- Price --}}
                                <div class="flex items-end justify-between gap-4">
                                    <div>
                                        <span class="block text-xs font-semibold text-[var(--color-text-muted)]">
                                            قیمت دوره
                                        </span>

                                        <strong class="mt-1 block text-2xl font-black text-[var(--color-primary-600)] sm:text-3xl">
                                            {{ $course->isFree() ? 'رایگان' : \App\Support\PersianUi::money($course->price) }}
                                        </strong>
                                    </div>

                                    @if($course->isFree())
                                        <span class="rounded-full bg-[var(--color-success-50)] px-3 py-1.5 text-xs font-black text-[var(--color-success-700)]">
                                            بدون هزینه
                                        </span>
                                    @elseif($requiresPayment)
                                        <span class="rounded-full bg-[var(--color-warning-50)] px-3 py-1.5 text-xs font-black text-[var(--color-warning-700)]">
                                            پرداختی
                                        </span>
                                    @endif
                                </div>

                                {{-- Access state --}}
                                @auth
                                    @if($canAccessContent)
                                        <div class="mt-6 rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] p-4">
                                            <div class="flex items-start gap-3">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-[var(--color-success-700)] shadow-sm">
                                                    ✓
                                                </span>

                                                <div>
                                                    <strong class="block text-sm font-black text-[var(--color-success-700)]">
                                                        دسترسی فعال است
                                                    </strong>

                                                    <p class="mt-1 text-xs leading-6 text-[var(--color-success-700)]/80">
                                                        این حساب می‌تواند محتوای محافظت‌شده دوره را فقط در همین فضای امن مشاهده کند.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($requiresPayment)
                                        <div class="mt-6 rounded-2xl border border-[var(--color-warning-100)] bg-[var(--color-warning-50)] p-4">
                                            <div class="flex items-start gap-3">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-[var(--color-warning-700)] shadow-sm">
                                                    !
                                                </span>

                                                <div>
                                                    <strong class="block text-sm font-black text-[var(--color-warning-700)]">
                                                        این دوره نیاز به پرداخت دارد
                                                    </strong>

                                                    <p class="mt-1 text-xs leading-6 text-[var(--color-warning-700)]/80">
                                                        محتوای محافظت‌شده تا زمان تأیید پرداخت در دسترس نخواهد بود.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-slate-50)] p-4 text-sm leading-7 text-[var(--color-text-secondary)]">
                                            برای ادامه و بررسی دسترسی به محتوای دوره، وضعیت حساب شما بررسی می‌شود.
                                        </div>
                                    @endif
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="mt-6 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[var(--color-primary-600)] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[var(--color-primary-700)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-300)] focus:ring-offset-2"
                                    >
                                        <span>ورود برای ادامه</span>
                                        <span aria-hidden="true">←</span>
                                    </a>

                                    <p class="mt-3 text-center text-xs leading-6 text-[var(--color-text-muted)]">
                                        برای دسترسی به محتوای خصوصی دوره ابتدا وارد حساب کاربری شوید.
                                    </p>
                                @endauth

                                {{-- Course facts --}}
                                <div class="mt-6 divide-y divide-[var(--color-border)] border-t border-[var(--color-border)]">
                                    <div class="flex items-center justify-between gap-4 py-4 text-sm">
                                        <span class="text-[var(--color-text-muted)]">سرفصل‌ها</span>
                                        <strong class="font-black text-[var(--color-text)]">
                                            {{ \App\Support\PersianUi::digits($sectionCount) }}
                                            بخش
                                        </strong>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-4 text-sm">
                                        <span class="text-[var(--color-text-muted)]">تعداد درس</span>
                                        <strong class="font-black text-[var(--color-text)]">
                                            {{ \App\Support\PersianUi::digits($lessonCount) }}
                                        </strong>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-4 text-sm">
                                        <span class="text-[var(--color-text-muted)]">وضعیت دسترسی</span>
                                        <strong class="font-black text-[var(--color-text)]">
                                            {{ $accessLabel }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                {{-- Curriculum --}}
                <section
                    class="mt-14 sm:mt-16"
                    aria-labelledby="course-curriculum-title"
                >
                    <div class="max-w-3xl">
                        <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                            برنامه آموزشی
                        </span>

                        <h2
                            id="course-curriculum-title"
                            class="mt-2 text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl"
                        >
                            سرفصل‌ها و درس‌ها
                        </h2>

                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-secondary)] sm:text-base">
                            ساختار دوره برای همه قابل مشاهده است؛ محتوای محافظت‌شده فقط با دسترسی معتبر نمایش داده می‌شود.
                        </p>
                    </div>

                    <div class="mt-8 space-y-4">
                        @forelse($course->sections as $section)
                            @php
                                $sectionLessonsCount = $section->lessons->count();
                            @endphp

                            <details
                                class="group overflow-hidden rounded-[1.25rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_6px_24px_rgba(15,23,42,.035)]"
                                @if($loop->first) open @endif
                            >
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-5 sm:px-6">
                                    <div class="flex min-w-0 items-center gap-4">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-sm font-black text-[var(--color-primary-600)]">
                                            {{ \App\Support\PersianUi::digits($loop->iteration) }}
                                        </span>

                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-black text-[var(--color-text)] sm:text-base">
                                                {{ $section->title }}
                                            </h3>

                                            <span class="mt-1 block text-xs text-[var(--color-text-muted)]">
                                                {{ \App\Support\PersianUi::digits($sectionLessonsCount) }}
                                                درس
                                            </span>
                                        </div>
                                    </div>

                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[var(--color-border)] text-[var(--color-text-muted)] transition duration-300 group-open:rotate-180 group-open:bg-[var(--color-primary-50)] group-open:text-[var(--color-primary-600)]"
                                        aria-hidden="true"
                                    >
                                        ↓
                                    </span>
                                </summary>

                                <div class="border-t border-[var(--color-border)] px-4 py-4 sm:px-6 sm:py-5">
                                    <div class="space-y-2.5">
                                        @forelse($section->lessons as $lesson)
                                            <div class="rounded-2xl border border-transparent bg-[var(--color-background-soft)] p-4 transition hover:border-[var(--color-border)]">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex min-w-0 items-start gap-3">
                                                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--color-surface)] text-xs font-black text-[var(--color-text-muted)]">
                                                            {{ \App\Support\PersianUi::digits($loop->iteration) }}
                                                        </span>

                                                        <div class="min-w-0">
                                                            <div class="text-sm font-bold leading-7 text-[var(--color-text)]">
                                                                {{ $lesson->title }}
                                                            </div>

                                                            @if($lesson->summary)
                                                                <div class="mt-1 line-clamp-2 text-xs leading-6 text-[var(--color-text-muted)]">
                                                                    {{ $lesson->summary }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="flex shrink-0 items-center gap-2">
                                                        @if($lesson->duration_seconds > 0)
                                                            <span class="hidden rounded-full bg-[var(--color-surface)] px-2.5 py-1 text-[11px] font-semibold text-[var(--color-text-muted)] sm:inline-flex">
                                                                {{ \App\Support\PersianUi::digits(ceil($lesson->duration_seconds / 60)) }}
                                                                دقیقه
                                                            </span>
                                                        @endif

                                                        @if($lesson->is_free)
                                                            <a
                                                                href="{{ route('courses.lessons.preview', [$course, $lesson]) }}"
                                                                class="inline-flex min-h-9 items-center gap-2 rounded-xl bg-[var(--color-success-50)] px-3 text-[11px] font-black text-[var(--color-success-700)] transition hover:bg-[var(--color-success-100)]"
                                                            >
                                                                پیش‌نمایش
                                                            </a>
                                                        @elseif($canAccessContent)
                                                            <span
                                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--color-success-50)] text-[var(--color-success-700)]"
                                                                title="دسترسی فعال"
                                                                aria-label="دسترسی فعال"
                                                            >
                                                                ✓
                                                            </span>
                                                        @else
                                                            <span
                                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--color-slate-100)] text-[var(--color-text-muted)]"
                                                                title="محتوا قفل است"
                                                                aria-label="محتوا قفل است"
                                                            >
                                                                <svg
                                                                    class="h-4 w-4"
                                                                    viewBox="0 0 24 24"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.7"
                                                                    aria-hidden="true"
                                                                >
                                                                    <rect x="5" y="10" width="14" height="10" rx="2"/>
                                                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Mobile duration --}}
                                                @if($lesson->duration_seconds > 0)
                                                    <div class="mt-3 sm:hidden">
                                                        <span class="inline-flex rounded-full bg-[var(--color-surface)] px-2.5 py-1 text-[11px] font-semibold text-[var(--color-text-muted)]">
                                                            مدت:
                                                            {{ \App\Support\PersianUi::digits(ceil($lesson->duration_seconds / 60)) }}
                                                            دقیقه
                                                        </span>
                                                    </div>
                                                @endif

                                                {{-- Protected media --}}
                                                @if($canAccessContent && $lesson->media->isNotEmpty())
                                                    <div class="mt-4 border-t border-[var(--color-border)] pt-4">
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($lesson->media as $media)
                                                                <a
                                                                    href="{{ route('media.view', $media) }}" target="_blank" rel="noopener"
                                                                    class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-3.5 py-2 text-xs font-black text-[var(--color-primary-600)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]"
                                                                >
                                                                    <span>مشاهده فایل</span>
                                                                    <span aria-hidden="true">→</span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="rounded-2xl border border-dashed border-[var(--color-border)] px-5 py-8 text-center">
                                                <p class="text-sm text-[var(--color-text-muted)]">
                                                    برای این بخش هنوز درسی ثبت نشده است.
                                                </p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </details>
                        @empty
                            <x-ui.empty-state
                                title="سرفصلی ثبت نشده"
                                description="محتوای این دوره هنوز توسط مدرس تکمیل نشده است."
                            />
                        @endforelse
                    </div>
                </section>

            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
