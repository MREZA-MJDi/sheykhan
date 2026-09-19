@extends('layouts.app')

@section('title', $course->title . ' | شیخان')
@section('description', $course->short_description ?: 'جزئیات و محتوای دوره ' . $course->title)

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <div class="grid gap-10 lg:grid-cols-[1.3fr_0.7fr]">
                <div>
                    <x-ui.badge variant="primary">{{ $course->level ?: 'دوره آموزشی' }}</x-ui.badge>

                    <h1 class="mt-5 text-3xl font-black sm:text-4xl">
                        {{ $course->title }}
                    </h1>

                    <p class="mt-5 max-w-3xl text-base leading-8 text-[var(--color-text-muted)]">
                        {{ $course->short_description ?: $course->description }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3 text-sm text-[var(--color-text-muted)]">
                        <span>{{ $course->teachers->first()?->name ?: 'مدرس شیخان' }}</span>
                        <span>•</span>
                        <span>{{ $course->sections->sum(fn ($section) => $section->lessons->count()) }} درس</span>
                        <span>•</span>
                        <span>{{ $course->duration_minutes > 0 ? floor($course->duration_minutes / 60) . ' ساعت' : 'مدت زمان متغیر' }}</span>
                    </div>

                    <div class="mt-10 max-w-none leading-8 text-[var(--color-text-secondary)]">
                        {!! nl2br(e($course->description)) !!}
                    </div>
                </div>

                <aside class="h-fit overflow-hidden rounded-3xl border border-[var(--color-border)] bg-white shadow-sm">
                    @if($course->media->first()?->url())
                        <img
                            src="{{ $course->media->first()->url() }}"
                            alt="{{ $course->title }}"
                            class="aspect-[16/10] w-full object-cover"
                        >
                    @endif

                    <div class="p-6">
                        <div class="text-sm text-[var(--color-text-muted)]">قیمت دوره</div>
                        <div class="mt-2 text-2xl font-black text-[var(--color-primary-600)]">
                            {{ $course->price > 0 ? number_format($course->price, 0, '.', ',') . ' تومان' : 'رایگان' }}
                        </div>

                        <a
                            href="{{ Route::has('login') ? route('login') : route('courses.index') }}"
                            class="mt-6 flex items-center justify-center rounded-xl bg-[var(--color-slate-900)] px-5 py-3 font-bold text-white"
                        >
                            شروع دوره
                        </a>
                    </div>
                </aside>
            </div>

            <div class="mt-14">
                <div class="mb-6">
                    <h2 class="text-2xl font-black">سرفصل‌های دوره</h2>
                    <p class="mt-2 text-sm text-[var(--color-text-muted)]">
                        ساختار واقعی Sections و Lessons این دوره.
                    </p>
                </div>

                <div class="space-y-4">
                    @forelse ($course->sections as $section)
                        <details class="group rounded-2xl border border-[var(--color-border)] bg-white p-5" @if($loop->first) open @endif>
                            <summary class="cursor-pointer list-none font-bold">
                                {{ $loop->iteration }}. {{ $section->title }}
                                <span class="float-left text-sm font-normal text-[var(--color-text-muted)]">
                                    {{ $section->lessons->count() }} درس
                                </span>
                            </summary>

                            <div class="mt-5 space-y-2 border-t border-[var(--color-border)] pt-4">
                                @forelse ($section->lessons as $lesson)
                                    <div class="flex items-center justify-between gap-4 rounded-xl bg-[var(--color-slate-50)] px-4 py-3">
                                        <div>
                                            <div class="font-medium">{{ $lesson->title }}</div>
                                            @if($lesson->summary)
                                                <div class="mt-1 text-xs text-[var(--color-text-muted)]">{{ $lesson->summary }}</div>
                                            @endif
                                        </div>
                                        <span class="shrink-0 text-xs text-[var(--color-text-muted)]">
                                            {{ $lesson->duration_seconds > 0 ? ceil($lesson->duration_seconds / 60) . ' دقیقه' : 'ویدئو' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-sm text-[var(--color-text-muted)]">برای این سرفصل هنوز درسی ثبت نشده است.</div>
                                @endforelse
                            </div>
                        </details>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[var(--color-border)] p-10 text-center text-[var(--color-text-muted)]">
                            هنوز سرفصلی برای این دوره تعریف نشده است.
                        </div>
                    @endforelse
                </div>
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection
