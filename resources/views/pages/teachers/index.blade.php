@extends('layouts.app')

@section('title', 'مدرس‌ها | شیخان')
@section('description', 'مدرس‌های تاییدشده و قابل نمایش شیخان.')

@section('content')
    <section class="public-teachers-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">

                {{-- Header --}}
                <div class="mb-10 sm:mb-12">
                    <div class="max-w-3xl">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3.5 py-2 text-xs font-black text-[var(--color-primary-700)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-primary-600)]"></span>
                            مدرس‌های شیخان
                        </div>

                        <h1 class="text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            مدرس‌های تاییدشده
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            مدرس‌های قابل نمایش را ببین و با تخصص و مسیرهای آموزشی آن‌ها آشنا شو.
                        </p>
                    </div>

                    @if($teachers->total())
                        <div class="mt-6 flex flex-wrap items-center gap-3 text-sm">
                            <span class="inline-flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 font-semibold text-[var(--color-text-muted)]">
                                <span class="font-black text-[var(--color-text)]">
                                    {{ $teachers->total() }}
                                </span>
                                مدرس
                            </span>

                            @if($teachers->hasPages())
                                <span class="text-[var(--color-text-subtle)]">
                                    صفحه {{ $teachers->currentPage() }} از {{ $teachers->lastPage() }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Teachers --}}
                @forelse($teachers as $teacher)
                    @if($loop->first)
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @endif

                            @php
                                $teacherName = trim((string) $teacher->name) ?: 'مدرس شیخان';
                                $teacherRole = $teacher->teacherProfile?->specialization ?: 'مدرس';
                                $teacherAvatar = $teacher->teacherProfile?->media?->first()?->url();
                                $teacherBio = $teacher->teacherProfile?->bio;
                                $teacherCourses = (int) ($teacher->courses_count ?? 0);
                            @endphp

                            <article
                                class="group flex h-full min-w-0 flex-col overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[0_8px_30px_rgba(15,23,42,.04)] transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[0_18px_45px_rgba(15,23,42,.10)] sm:p-6"
                            >
                                {{-- Profile --}}
                                <div class="flex items-start gap-4">
                                    <div class="relative shrink-0">
                                        <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-[var(--color-primary-50)] ring-1 ring-[var(--color-border)] sm:h-[4.5rem] sm:w-[4.5rem]">
                                            @if($teacherAvatar)
                                                <img
                                                    src="{{ $teacherAvatar }}"
                                                    alt="{{ $teacherName }}"
                                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                    loading="lazy"
                                                >
                                            @else
                                                <span class="text-xl font-black text-[var(--color-primary-600)] sm:text-2xl">
                                            {{ mb_substr($teacherName, 0, 1) }}
                                        </span>
                                            @endif
                                        </div>

                                        <span
                                            class="absolute -bottom-1.5 -left-1.5 flex h-6 w-6 items-center justify-center rounded-full border-2 border-[var(--color-surface)] bg-emerald-500 text-white shadow-sm"
                                            title="مدرس تاییدشده"
                                            aria-label="مدرس تاییدشده"
                                        >
                                    <svg
                                        class="h-3.5 w-3.5"
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

                                    <div class="min-w-0 flex-1 pt-1">
                                        <h2 class="truncate text-base font-black leading-7 text-[var(--color-text)]">
                                            {{ $teacherName }}
                                        </h2>

                                        <p class="mt-0.5 truncate text-xs font-semibold text-[var(--color-text-muted)]">
                                            {{ $teacherRole }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Bio --}}
                                <div class="mt-6 min-h-[5.5rem]">
                                    @if($teacherBio)
                                        <p class="line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">
                                            {{ $teacherBio }}
                                        </p>
                                    @else
                                        <p class="text-sm leading-7 text-[var(--color-text-muted)]">
                                            مدرس تاییدشده شیخان با مسیرهای آموزشی فعال.
                                        </p>
                                    @endif
                                </div>

                                {{-- Meta --}}
                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-[var(--color-border)] pt-4">
                                    <div class="inline-flex items-center gap-2 text-xs font-bold text-[var(--color-text-muted)]">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-slate-50)] text-[var(--color-primary-600)]">
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

                                        <span>
                                    {{ $teacherCourses }} دوره آموزشی
                                </span>
                                    </div>

                                    {{-- Until teacher.show exists, keep the current valid public route. --}}
                                    <a
                                        href="{{ route('teachers.index') }}"
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-black text-[var(--color-primary-600)] transition hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]"
                                        aria-label="مشاهده مدرس‌ها"
                                    >
                                        <span>مشاهده</span>
                                        <span
                                            aria-hidden="true"
                                            class="transition-transform duration-200 group-hover:-translate-x-1"
                                        >
                                    ←
                                </span>
                                    </a>
                                </div>
                            </article>

                            @if($loop->last)
                        </div>
                    @endif
                @empty
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
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"
                                />
                            </svg>
                        </div>

                        <h2 class="mt-5 text-xl font-black text-[var(--color-text)]">
                            هنوز مدرس قابل نمایشی وجود ندارد
                        </h2>

                        <p class="mx-auto mt-2 max-w-lg text-sm leading-7 text-[var(--color-text-secondary)]">
                            به‌محض ثبت و تایید مدرس، اطلاعات او در این بخش نمایش داده می‌شود.
                        </p>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($teachers->hasPages())
                    <div class="mt-10 border-t border-[var(--color-border)] pt-8 sm:mt-12">
                        <x-navigation.pagination :paginator="$teachers" />
                    </div>
                @endif

            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
