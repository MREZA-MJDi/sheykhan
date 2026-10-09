@extends('layouts.app')

@section('title', 'دوره‌ها | شیخان')
@section('description', 'دوره‌های آموزشی منتشرشده در شیخان.')

@section('content')
    <section class="public-courses-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">

                {{-- Header --}}
                <header class="mb-10 sm:mb-12">
                    <div class="max-w-3xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3.5 py-2 text-xs font-black text-[var(--color-primary-700)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-primary-600)]"></span>
                            کاتالوگ آموزش
                        </span>

                        <h1 class="mt-4 text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            دوره‌های آموزشی
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            دوره‌های منتشرشده را بر اساس پایه و مسیر آموزشی انتخاب کن و مناسب‌ترین مسیر یادگیری را پیدا کن.
                        </p>
                    </div>

                    @if($courses->total())
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 text-sm font-semibold text-[var(--color-text-muted)]">
                                <strong class="font-black text-[var(--color-text)]">
                                    {{ \App\Support\PersianUi::digits($courses->total()) }}
                                </strong>
                                دوره منتشرشده
                            </span>

                            @if($courses->hasPages())
                                <span class="text-sm text-[var(--color-text-subtle)]">
                                    صفحه {{ \App\Support\PersianUi::digits($courses->currentPage()) }}
                                    از
                                    {{ \App\Support\PersianUi::digits($courses->lastPage()) }}
                                </span>
                            @endif
                        </div>
                    @endif
                </header>

                {{-- Grade filters --}}
                @if(($navigationGrades ?? collect())->count())
                    <section
                        class="mb-10 rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] p-4 shadow-[0_8px_30px_rgba(15,23,42,.04)] sm:p-5"
                        aria-labelledby="course-grade-filter"
                    >
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                    مسیر آموزشی
                                </span>

                                <h2
                                    id="course-grade-filter"
                                    class="mt-1 text-base font-black text-[var(--color-text)] sm:text-lg"
                                >
                                    انتخاب پایه
                                </h2>
                            </div>

                            @if(!empty($selectedGradeId))
                                <a
                                    href="{{ route('courses.index') }}"
                                    class="shrink-0 rounded-xl px-3 py-2 text-xs font-black text-[var(--color-primary-600)] transition hover:bg-[var(--color-primary-50)]"
                                >
                                    حذف فیلتر
                                </a>
                            @endif
                        </div>

                        <div
                            class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
                            role="list"
                            aria-label="فیلتر پایه‌های تحصیلی"
                        >
                            <a
                                href="{{ route('courses.index') }}"
                                @class([
                                    'inline-flex shrink-0 items-center justify-center rounded-full border px-4 py-2.5 text-sm font-black transition',
                                    'border-[var(--color-primary-600)] bg-[var(--color-primary-600)] text-white shadow-sm' => empty($selectedGradeId),
                                    'border-[var(--color-border)] bg-[var(--color-surface)] text-[var(--color-text-muted)] hover:border-[var(--color-primary-300)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]' => !empty($selectedGradeId),
                                ])
                                role="listitem"
                                @if(empty($selectedGradeId)) aria-current="page" @endif
                            >
                                همه دوره‌ها
                            </a>

                            @foreach($navigationGrades as $grade)
                                <a
                                    href="{{ route('courses.index', ['grade' => $grade->id]) }}"
                                    @class([
                                        'inline-flex shrink-0 items-center justify-center rounded-full border px-4 py-2.5 text-sm font-black transition',
                                        'border-[var(--color-primary-600)] bg-[var(--color-primary-600)] text-white shadow-sm' => (int) $selectedGradeId === (int) $grade->id,
                                        'border-[var(--color-border)] bg-[var(--color-surface)] text-[var(--color-text-muted)] hover:border-[var(--color-primary-300)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]' => (int) $selectedGradeId !== (int) $grade->id,
                                    ])
                                    role="listitem"
                                    @if((int) $selectedGradeId === (int) $grade->id) aria-current="page" @endif
                                >
                                    {{ $grade->title }}
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Courses --}}
                <section aria-labelledby="published-courses-title">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                آموزش
                            </span>

                            <h2
                                id="published-courses-title"
                                class="mt-1 text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl"
                            >
                                دوره‌های منتشرشده
                            </h2>
                        </div>

                        @if($courses->total())
                            <span class="hidden text-sm font-semibold text-[var(--color-text-muted)] sm:block">
                                {{ \App\Support\PersianUi::digits($courses->total()) }}
                                دوره
                            </span>
                        @endif
                    </div>

                    @if($courses->count())
                        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($courses as $course)
                                <x-education.course-card
                                    :title="$course->title"
                                    :description="$course->short_description ?: $course->description"
                                    :category="$course->academy?->name"
                                    :teacher="$course->teachers->first()?->name"
                                    :lessons="\App\Support\PersianUi::digits($course->lessons_count ?? 0)"
                                    :duration="$course->duration_minutes > 0
                                        ? (
                                            intdiv((int) $course->duration_minutes, 60) > 0 && ((int) $course->duration_minutes % 60) > 0
                                                ? \App\Support\PersianUi::digits(intdiv((int) $course->duration_minutes, 60)) . ' ساعت و ' . \App\Support\PersianUi::digits((int) $course->duration_minutes % 60) . ' دقیقه'
                                                : (intdiv((int) $course->duration_minutes, 60) > 0
                                                    ? \App\Support\PersianUi::digits(intdiv((int) $course->duration_minutes, 60)) . ' ساعت'
                                                    : \App\Support\PersianUi::digits((int) $course->duration_minutes) . ' دقیقه')
                                        )
                                        : 'مدت زمان متغیر'"
                                    :price="$course->price > 0
                                        ? \App\Support\PersianUi::money($course->price)
                                        : 'رایگان'"
                                    :level="$course->level"
                                    :grades="$course->grades->pluck('title')->all()"
                                    :image="$course->media->first()?->url()"
                                    :href="route('courses.show', $course)"
                                />
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-[1.5rem] border border-dashed border-[var(--color-border)] bg-[var(--color-slate-50)] px-6 py-14 text-center sm:px-10">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
                                <svg
                                    class="h-8 w-8"
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
                            </div>

                            <h2 class="mt-5 text-xl font-black text-[var(--color-text)]">
                                دوره‌ای برای نمایش وجود ندارد
                            </h2>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[var(--color-text-secondary)]">
                                هنوز دوره‌ای با وضعیت انتشار فعال برای این مسیر پیدا نشده است.
                            </p>

                            @if(!empty($selectedGradeId))
                                <a
                                    href="{{ route('courses.index') }}"
                                    class="mt-6 inline-flex items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-[var(--color-primary-700)]"
                                >
                                    مشاهده همه دوره‌ها
                                    <span class="mr-2" aria-hidden="true">←</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </section>

                {{-- Pagination --}}
                @if($courses->hasPages())
                    <div class="mt-10 border-t border-[var(--color-border)] pt-8 sm:mt-12">
                        <x-navigation.pagination :paginator="$courses" />
                    </div>
                @endif

            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
