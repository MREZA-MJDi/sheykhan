@extends('layouts.app')

@section('title', $course->title . ' | شیخان')
@section('description', $course->short_description ?: 'جزئیات دوره ' . $course->title)

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-[var(--color-primary-600)]">
                <span aria-hidden="true">→</span>
                بازگشت به دوره‌ها
            </a>

            <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($course->academy)
                            <span class="rounded-full bg-[var(--color-primary-50)] px-3 py-1.5 text-xs font-bold text-[var(--color-primary-700)]">
                                {{ $course->academy->name }}
                            </span>
                        @endif

                        @if($course->level)
                            <span class="rounded-full bg-[var(--color-slate-100)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                                {{ $course->level }}
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-5 max-w-4xl text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                        {{ $course->title }}
                    </h1>

                    <p class="mt-5 max-w-3xl text-base leading-8 text-[var(--color-text-muted)] sm:text-lg">
                        {{ $course->short_description ?: $course->description }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3 text-sm text-[var(--color-text-muted)]">
                        @if($course->teachers->first())
                            <span>مدرس: <strong class="text-[var(--color-text)]">{{ $course->teachers->first()->name }}</strong></span>
                        @endif
                        <span>{{ $course->sections->sum(fn ($section) => $section->lessons->count()) }} درس</span>
                        <span>•</span>
                        <span>{{ $course->duration_minutes > 0 ? floor($course->duration_minutes / 60) . ' ساعت' : 'مدت زمان متغیر' }}</span>
                    </div>

                    @if($course->description)
                        <div class="mt-10 rounded-3xl border border-[var(--color-border)] bg-white p-6 shadow-sm sm:p-8">
                            <h2 class="text-2xl font-black">درباره دوره</h2>
                            <div class="mt-4 text-base leading-9 text-[var(--color-text-secondary)]">
                                {!! nl2br(e($course->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="sticky top-28 overflow-hidden rounded-3xl border border-[var(--color-border)] bg-white shadow-[var(--shadow-lg)]">
                    <div class="relative aspect-[16/10] overflow-hidden bg-[var(--color-slate-100)]">
                        @if($course->media->first()?->url())
                            <img
                                src="{{ $course->media->first()->url() }}"
                                alt="{{ $course->title }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="flex h-full items-center justify-center text-[var(--color-text-subtle)]">
                                <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M4 5h16v14H4z"/>
                                    <path d="m4 15 4-4 3 3 3-4 6 6"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="p-6 sm:p-7">
                        <div class="text-xs font-semibold text-[var(--color-text-muted)]">هزینه دوره</div>
                        <div class="mt-2 text-2xl font-black text-[var(--color-primary-600)]">
                            {{ $course->price > 0 ? number_format($course->price, 0, '.', ',') . ' تومان' : 'رایگان' }}
                        </div>

                        <x-ui.button
                            href="{{ Route::has('login') ? route('login') : route('courses.index') }}"
                            variant="primary"
                            size="lg"
                            block
                            class="mt-6"
                        >
                            شروع دوره
                        </x-ui.button>

                        <div class="mt-5 space-y-3 border-t border-[var(--color-border)] pt-5 text-sm text-[var(--color-text-muted)]">
                            <div class="flex items-center justify-between gap-4">
                                <span>سرفصل</span>
                                <strong class="text-[var(--color-text)]">{{ $course->sections->count() }} بخش</strong>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>درس</span>
                                <strong class="text-[var(--color-text)]">{{ $course->sections->sum(fn ($section) => $section->lessons->count()) }}</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="mt-14">
                <x-layout.section-heading
                    eyebrow="محتوای دوره"
                    title="سرفصل‌ها و درس‌ها"
                    description="ساختار واقعی Sections و Lessons این دوره."
                />

                <div class="mt-8 space-y-4">
                    @forelse ($course->sections as $section)
                        <details
                            class="group overflow-hidden rounded-2xl border border-[var(--color-border)] bg-white shadow-sm"
                            @if($loop->first) open @endif
                        >
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-5 sm:px-6">
                                <span class="font-bold text-[var(--color-text)]">
                                    {{ $loop->iteration }}. {{ $section->title }}
                                </span>

                                <span class="shrink-0 text-xs text-[var(--color-text-muted)]">
                                    {{ $section->lessons->count() }} درس
                                </span>
                            </summary>

                            <div class="border-t border-[var(--color-border)] px-5 py-4 sm:px-6">
                                <div class="space-y-2">
                                    @forelse($section->lessons as $lesson)
                                        <div class="flex items-center justify-between gap-4 rounded-xl bg-[var(--color-background-soft)] px-4 py-3">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-[var(--color-text)]">
                                                    {{ $lesson->title }}
                                                </div>

                                                @if($lesson->summary)
                                                    <div class="mt-1 line-clamp-1 text-xs text-[var(--color-text-muted)]">
                                                        {{ $lesson->summary }}
                                                    </div>
                                                @endif
                                            </div>

                                            <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-[var(--color-text-muted)]">
                                                {{ $lesson->duration_seconds > 0 ? ceil($lesson->duration_seconds / 60) . ' دقیقه' : 'درس' }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="py-4 text-sm text-[var(--color-text-muted)]">
                                            برای این بخش هنوز درسی ثبت نشده است.
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
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection
