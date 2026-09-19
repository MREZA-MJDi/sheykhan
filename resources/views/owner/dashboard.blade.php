@extends('layouts.owner')

@section('title', 'مدیریت آموزشگاه | شیخان')
@section('header-title', 'مدیریت آموزشگاه')

@section('content')
    <div class="space-y-6 panel-page-enter">
        <div class="rounded-[1.75rem] border border-[var(--color-border)] bg-[linear-gradient(135deg,#ffffff,#f4f6ff)] p-6 shadow-sm sm:p-8">
            <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <p class="text-xs font-black text-[var(--color-brand-600)]">مرکز کنترل آموزشگاه</p>
                    <h2 class="mt-3 max-w-2xl text-2xl font-black leading-tight text-[var(--color-text)] sm:text-4xl">
                        همه‌چیز را از ساختار آموزش تا قیمت‌گذاری دوره‌ها کنترل کن.
                    </h2>
                    <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)]">
                        دوره بساز، رایگان یا پولی بودن را مشخص کن، قیمت بگذار و فایل‌های آموزشی خصوصی را مدیریت کن.
                    </p>
                </div>

                <a href="{{ route('owner.courses.create') }}"
                   class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[var(--color-brand-600)] px-5 text-sm font-black text-white shadow-[0_10px_24px_rgba(83,98,223,.16)] transition hover:-translate-y-0.5 hover:bg-[var(--color-brand-700)]">
                    + ساخت دوره جدید
                </a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="panel-card panel-stat p-5">
                <div class="text-xs font-semibold text-[var(--color-text-muted)]">کل دوره‌ها</div>
                <div class="mt-2 text-3xl font-black">{{ $courseCount }}</div>
            </div>

            <div class="panel-card panel-stat p-5">
                <div class="text-xs font-semibold text-[var(--color-text-muted)]">منتشرشده</div>
                <div class="mt-2 text-3xl font-black">{{ $publishedCount }}</div>
            </div>

            <div class="panel-card panel-stat p-5">
                <div class="text-xs font-semibold text-[var(--color-text-muted)]">رایگان</div>
                <div class="mt-2 text-3xl font-black">{{ $freeCount }}</div>
            </div>

            <div class="panel-card panel-stat p-5">
                <div class="text-xs font-semibold text-[var(--color-text-muted)]">پولی</div>
                <div class="mt-2 text-3xl font-black">{{ $paidCount }}</div>
            </div>
        </div>

        <section class="panel-card overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-[var(--color-border)] px-5 py-4 sm:px-6">
                <div>
                    <h3 class="font-black">آخرین دوره‌ها</h3>
                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">دسترسی و وضعیت انتشار هر دوره را سریع ببین.</p>
                </div>
                <a href="{{ route('owner.courses.index') }}" class="text-xs font-black text-[var(--color-brand-600)]">مشاهده همه ←</a>
            </div>

            <div class="divide-y divide-[var(--color-border)]">
                @forelse($courses->take(5) as $course)
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold">{{ $course->title }}</div>
                            <div class="mt-1 text-xs text-[var(--color-text-muted)]">{{ $course->academy?->name }}</div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $course->isFree() ? 'bg-[var(--color-success-50)] text-[var(--color-success-700)]' : 'bg-[var(--color-warning-50)] text-[var(--color-warning-700)]' }}">
                                {{ $course->isFree() ? 'رایگان' : number_format((float) $course->price, 0, '.', ',') . ' تومان' }}
                            </span>

                            <a href="{{ route('owner.courses.edit', $course) }}" class="rounded-xl border border-[var(--color-border)] bg-white px-3 py-2 text-xs font-bold text-[var(--color-text-secondary)] hover:bg-[var(--color-background-soft)]">
                                مدیریت
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm font-bold">هنوز دوره‌ای وجود ندارد.</p>
                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">اولین دوره را از همین صفحه بساز.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
