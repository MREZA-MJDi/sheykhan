@extends('layouts.app')

@section('title', $lesson->title . ' | پیش‌نمایش | شیخان')
@section('description', $lesson->summary ?: 'پیش‌نمایش رایگان درس «' . $lesson->title . '» از دوره ' . $course->title)

@section('content')
    <section class="public-course-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <div class="mx-auto max-w-4xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-[var(--color-success-50)] px-3 py-1.5 text-xs font-black text-[var(--color-success-700)]">
                            پیش‌نمایش رایگان
                        </span>
                        <span class="rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                            {{ $course->title }}
                        </span>
                    </div>

                    <h1 class="mt-5 text-3xl font-black leading-[1.4] tracking-tight text-[var(--color-text)] sm:text-4xl">
                        {{ $lesson->title }}
                    </h1>

                    @if($lesson->summary)
                        <p class="mt-4 text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            {{ $lesson->summary }}
                        </p>
                    @endif

                    <article class="mt-8 overflow-hidden rounded-[1.75rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_20px_60px_rgba(15,23,42,.07)]">
                        <div class="border-b border-[var(--color-border)] bg-[var(--color-background-soft)] px-5 py-4 sm:px-7">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <span class="text-xs font-black text-[var(--color-primary-600)]">درس آزمایشی</span>
                                @if($lesson->duration_seconds > 0)
                                    <span class="rounded-full bg-[var(--color-surface)] px-3 py-1.5 text-[11px] font-bold text-[var(--color-text-muted)]">
                                        {{ AppSupportPersianUi::digits(ceil($lesson->duration_seconds / 60)) }} دقیقه
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="px-5 py-7 sm:px-8 sm:py-9">
                            <div class="prose prose-slate max-w-none text-sm leading-9 text-[var(--color-text-secondary)] sm:text-base">
                                {!! nl2br(e($lesson->content ?: 'محتوای این پیش‌نمایش هنوز تکمیل نشده است.')) !!}
                            </div>

                            @if($lesson->media->isNotEmpty())
                                <div class="mt-8 border-t border-[var(--color-border)] pt-6">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($lesson->media as $media)
                                            @if($media->url())
                                                <a
                                                    href="{{ $media->url() }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-background-soft)] px-3.5 py-2 text-xs font-black text-[var(--color-primary-600)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)]"
                                                >
                                                    <span>مشاهده پیش‌نمایش</span>
                                                    <span aria-hidden="true">←</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>

                    <div class="mt-7 grid gap-3 sm:grid-cols-2">
                        <a
                            href="{{ route('courses.show', $course) }}"
                            class="flex min-h-12 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-5 py-3 text-sm font-black text-[var(--color-text)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)]"
                        >
                            برگشت به معرفی دوره
                        </a>

                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="flex min-h-12 items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-5 py-3 text-sm font-black text-white transition hover:bg-[var(--color-primary-700)]"
                            >
                                ادامه در فضای شخصی
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="flex min-h-12 items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-5 py-3 text-sm font-black text-white transition hover:bg-[var(--color-primary-700)]"
                            >
                                ورود برای ادامه یادگیری
                            </a>
                        @endauth
                    </div>
                </div>
            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
