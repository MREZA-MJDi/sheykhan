@extends('layouts.app')

@section('title', ($teacher->name ?: 'مدرس') . ' | مدرس شیخان')
@section('description', \Illuminate\Support\Str::limit(
    trim(($teacher->teacherProfile?->bio ?: '') . ' ' . ($teacher->teacherProfile?->specialization ?: 'مدرس تاییدشده در شیخان.')),
    155
))

@section('content')
    @php
        $profile = $teacher->teacherProfile ?? null;
        $name = $teacher->name ?? 'مدرس شیخان';
        $specialization = $profile?->specialization ?: 'مدرس شیخان';
        $bio = $profile?->bio;
        $avatar = $profile?->media?->first()?->url();

        $coursesCount = $teacher->courses_count ?? $teacher->taughtCourses?->count() ?? 0;

        $initial = mb_substr(trim($name), 0, 1);

        $courses = $teacher->taughtCourses ?? collect();
    @endphp

    <section class="teacher-profile-page relative overflow-hidden">
        {{-- Decorative background --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[420px] bg-[radial-gradient(circle_at_75%_20%,rgba(37,99,235,.10),transparent_32%),radial-gradient(circle_at_20%_10%,rgba(14,165,233,.08),transparent_28%)]"></div>

        <x-layout.section spacing="lg">
            <x-layout.container size="2xl">

                {{-- Breadcrumb --}}
                <nav
                    class="mb-8 flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm text-[var(--color-text-muted)]"
                    aria-label="مسیر صفحه"
                >
                    <a
                        href="{{ url('/') }}"
                        class="shrink-0 transition-colors hover:text-[var(--color-primary-600)]"
                    >
                        خانه
                    </a>

                    <span aria-hidden="true" class="text-[var(--color-text-subtle)]">
                        /
                    </span>

                    <a
                        href="{{ route('teachers.index') }}"
                        class="shrink-0 transition-colors hover:text-[var(--color-primary-600)]"
                    >
                        مدرس‌ها
                    </a>

                    <span aria-hidden="true" class="text-[var(--color-text-subtle)]">
                        /
                    </span>

                    <span class="truncate font-semibold text-[var(--color-text)]">
                        {{ $name }}
                    </span>
                </nav>

                {{-- Profile hero --}}
                <div
                    class="overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_24px_70px_rgba(15,23,42,.08)]"
                >
                    <div class="relative overflow-hidden bg-[linear-gradient(135deg,#0f172a,#172554_58%,#1e3a8a)] px-5 py-8 text-white sm:px-8 sm:py-10 lg:px-12 lg:py-12">
                        <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-32 left-10 h-72 w-72 rounded-full bg-sky-400/10 blur-3xl"></div>

                        <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex min-w-0 flex-col gap-6 sm:flex-row sm:items-center">
                                {{-- Avatar --}}
                                <div class="relative shrink-0">
                                    <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-[1.75rem] border border-white/20 bg-white/10 shadow-2xl backdrop-blur sm:h-36 sm:w-36">
                                        @if($avatar)
                                            <img
                                                src="{{ $avatar }}"
                                                alt="{{ $name }}"
                                                class="h-full w-full object-cover"
                                                loading="eager"
                                            >
                                        @else
                                            <span class="text-4xl font-black text-white sm:text-5xl">
                                                {{ $initial }}
                                            </span>
                                        @endif
                                    </div>

                                    <span
                                        class="absolute -bottom-2 -left-2 flex h-9 w-9 items-center justify-center rounded-full border-4 border-[#172554] bg-emerald-500 text-white shadow-lg"
                                        title="مدرس تأییدشده"
                                        aria-label="مدرس تأییدشده"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2.5"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m5 12 4 4L19 6"
                                            />
                                        </svg>
                                    </span>
                                </div>

                                {{-- Identity --}}
                                <div class="min-w-0">
                                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-white/85 backdrop-blur">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        مدرس شیخان
                                    </div>

                                    <h1 class="break-words text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                                        {{ $name }}
                                    </h1>

                                    <p class="mt-3 text-sm font-semibold text-white/70 sm:text-base">
                                        {{ $specialization }}
                                    </p>

                                    @if($bio)
                                        <p class="mt-5 max-w-2xl text-sm leading-8 text-white/75 sm:text-base">
                                            {{ $bio }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Course count --}}
                            <div class="shrink-0 rounded-2xl border border-white/15 bg-white/10 px-6 py-5 backdrop-blur">
                                <span class="block text-xs font-bold text-white/60">
                                    دوره‌های آموزشی
                                </span>

                                <strong class="mt-1 block text-3xl font-black">
                                    {{ $coursesCount }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- Profile body --}}
                    <div class="grid gap-8 p-5 sm:p-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:p-10">

                        {{-- Main --}}
                        <div class="min-w-0">

                            <div class="mb-8">
                                <span class="text-xs font-black tracking-[.12em] text-[var(--color-primary-600)]">
                                    مسیرهای آموزشی
                                </span>

                                <h2 class="mt-2 text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl">
                                    دوره‌های {{ $name }}
                                </h2>

                                <p class="mt-3 text-sm leading-7 text-[var(--color-text-secondary)]">
                                    دوره‌های آموزشی منتشرشده توسط این مدرس را مشاهده کنید.
                                </p>
                            </div>

                            @if($courses->count())
                                <div class="grid gap-5 sm:grid-cols-2">
                                    @foreach($courses as $course)
                                        <article
                                            class="group overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-xl"
                                        >
                                            @if($course->media?->first())
                                                <a href="{{ route('courses.show', $course) }}" aria-label="مشاهده دوره {{ $course->title }}" class="block aspect-[16/9] overflow-hidden bg-[var(--color-slate-100)]">
                                                    <img
                                                        src="{{ $course->media->first()->url() }}"
                                                        alt="{{ $course->title }}"
                                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                        loading="lazy"
                                                    >
                                                </a>
                                            @endif

                                            <div class="p-5">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="rounded-full bg-[var(--color-primary-50)] px-3 py-1 text-xs font-bold text-[var(--color-primary-700)]">
                                                        دوره آموزشی
                                                    </span>

                                                    @if(isset($course->lessons_count))
                                                        <span class="text-xs font-semibold text-[var(--color-text-muted)]">
                                                            {{ \App\Support\PersianUi::digits($course->lessons_count) }} درس
                                                        </span>
                                                    @endif
                                                </div>

                                                <h3 class="mt-4 line-clamp-2 text-lg font-black leading-8 text-[var(--color-text)]">
                                                    <a href="{{ route('courses.show', $course) }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                                                        {{ $course->title }}
                                                    </a>
                                                </h3>

                                                @if($course->description ?? null)
                                                    <p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                                                        {{ $course->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-2xl border border-dashed border-[var(--color-border)] bg-[var(--color-slate-50)] p-8 text-center sm:p-12">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
                                        <svg
                                            class="h-7 w-7"
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

                                    <h3 class="mt-5 text-lg font-black text-[var(--color-text)]">
                                        هنوز دوره‌ای منتشر نشده است
                                    </h3>

                                    <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[var(--color-text-secondary)]">
                                        دوره‌های این مدرس پس از انتشار در این بخش نمایش داده خواهند شد.
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Sidebar --}}
                        <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">

                            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-slate-50)] p-5 sm:p-6">
                                <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                    درباره مدرس
                                </span>

                                <h2 class="mt-2 text-lg font-black text-[var(--color-text)]">
                                    {{ $name }}
                                </h2>

                                <dl class="mt-5 space-y-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-sm text-[var(--color-text-muted)]">
                                            تخصص
                                        </dt>
                                        <dd class="text-left text-sm font-bold text-[var(--color-text)]">
                                            {{ $specialization }}
                                        </dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-sm text-[var(--color-text-muted)]">
                                            دوره‌ها
                                        </dt>
                                        <dd class="text-sm font-bold text-[var(--color-text)]">
                                            {{ $coursesCount }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <a
                                href="{{ route('teachers.index') }}"
                                class="group flex items-center justify-between gap-4 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 transition hover:-translate-y-0.5 hover:border-[var(--color-primary-300)] hover:shadow-lg"
                            >
                                <div>
                                    <span class="block text-xs font-bold text-[var(--color-text-muted)]">
                                        مدرس‌های دیگر
                                    </span>
                                    <strong class="mt-1 block text-sm font-black text-[var(--color-text)]">
                                        مشاهده فهرست مدرس‌ها
                                    </strong>
                                </div>

                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)] transition group-hover:-translate-x-1"
                                    aria-hidden="true"
                                >
                                    ←
                                </span>
                            </a>

                        </aside>
                    </div>
                </div>

            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
