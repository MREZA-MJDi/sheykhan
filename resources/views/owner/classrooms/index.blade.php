@extends('layouts.owner')

@section('title','کلاس‌های آموزشگاه | شیخان')
@section('header-title','کلاس‌های آموزشگاه')

@section('content')
@php
    $activeCount = $classrooms->where('status', 'active')->count();
    $archivedCount = $classrooms->where('status', 'archived')->count();
    $studentTotal = $classrooms->sum('active_students_count');
    $capacityTotal = $classrooms->whereNotNull('capacity')->sum('capacity');
@endphp

<div class="space-y-6 panel-page-enter">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">مرکز مدیریت کلاس‌ها</p>
            <h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                کلاس‌ها را بساز، مدرس‌ها را تعیین کن و وضعیت اجرایی و ظرفیت را از همین‌جا زیر نظر داشته باش.
            </p>
        </div>

        <a href="{{ route('owner.classrooms.create', $academy) }}"
           class="inline-flex min-h-11 items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white shadow-sm">
            + ساخت کلاس جدید
        </a>
    </div>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="owner-stat-card">
            <span>کل کلاس‌ها</span>
            <strong>{{ $classrooms->count() }}</strong>
            <small>{{ $activeCount }} فعال · {{ $archivedCount }} آرشیو</small>
        </article>
        <article class="owner-stat-card">
            <span>دانش‌آموز فعال</span>
            <strong>{{ $studentTotal }}</strong>
            <small>در تمام کلاس‌های این آموزشگاه</small>
        </article>
        <article class="owner-stat-card">
            <span>ظرفیت تعریف‌شده</span>
            <strong>{{ $capacityTotal ?: '∞' }}</strong>
            <small>{{ $capacityTotal ? ($capacityTotal - $studentTotal) . ' ظرفیت باقیمانده' : 'بدون سقف کلی' }}</small>
        </article>
        <article class="owner-stat-card">
            <span>کلاس‌های نیازمند توجه</span>
            <strong>{{ $classrooms->where('occupancy_percent', '>=', 90)->count() }}</strong>
            <small>۹۰٪ ظرفیت یا بیشتر</small>
        </article>
    </section>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($classrooms as $classroom)
            @php
                $executionLabels = [
                    'running' => 'در حال برگزاری',
                    'scheduled' => 'برنامه‌ریزی‌شده',
                    'finished' => 'پایان‌یافته',
                    'active' => 'فعال',
                    'archived' => 'آرشیو',
                ];
                $executionClasses = [
                    'running' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'scheduled' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                    'finished' => 'bg-slate-100 text-slate-600 border-slate-200',
                    'active' => 'bg-sky-50 text-sky-700 border-sky-100',
                    'archived' => 'bg-slate-100 text-slate-500 border-slate-200',
                ];
            @endphp

            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-base font-black">{{ $classroom->title }}</h2>
                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $executionClasses[$classroom->execution_status] ?? $executionClasses['active'] }}">
                                {{ $executionLabels[$classroom->execution_status] ?? 'فعال' }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $classroom->course?->title }} · کد {{ $classroom->code }}
                        </p>
                        @if($classroom->grade)
                            <p class="mt-1 text-[10px] text-slate-400">
                                {{ $classroom->grade->title }}{{ $classroom->academicYear ? ' · '.$classroom->academicYear->title : '' }}
                            </p>
                        @endif
                    </div>

                    <span class="shrink-0 rounded-xl bg-slate-50 px-3 py-2 text-[10px] font-bold text-slate-500">
                        {{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}
                    </span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <strong class="block text-sm font-black">{{ $classroom->active_students_count }}</strong>
                        <span class="text-[9px] text-slate-500">دانش‌آموز</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <strong class="block text-sm font-black">{{ $classroom->capacity ?? '∞' }}</strong>
                        <span class="text-[9px] text-slate-500">ظرفیت</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <strong class="block text-sm font-black">{{ $classroom->capacity_remaining ?? '∞' }}</strong>
                        <span class="text-[9px] text-slate-500">خالی</span>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <strong class="block text-sm font-black">{{ $classroom->teachers->count() }}</strong>
                        <span class="text-[9px] text-slate-500">مدرس</span>
                    </div>
                </div>

                @if($classroom->occupancy_percent !== null)
                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between text-[10px] text-slate-500">
                            <span>پرشدگی کلاس</span>
                            <strong>{{ $classroom->occupancy_percent }}٪</strong>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <span class="block h-full rounded-full bg-[var(--panel-primary)]" style="width:{{ $classroom->occupancy_percent }}%"></span>
                        </div>
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    @if($classroom->schedules->isNotEmpty())
                        <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[10px] text-slate-500">
                            برنامه {{ $classroom->schedules->count() }} نوبت
                        </span>
                    @else
                        <span class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-[10px] text-amber-700">
                            برنامه هفتگی ثبت نشده
                        </span>
                    @endif

                    @if($classroom->starts_at)
                        <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[10px] text-slate-500">
                            شروع {{ $classroom->starts_at->format('Y/m/d H:i') }}
                        </span>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                    <a href="{{ route('owner.classrooms.show', [$academy, $classroom]) }}"
                       class="rounded-xl bg-slate-900 px-4 py-2.5 text-[10px] font-black text-white">
                        مشاهده جزئیات
                    </a>
                    <a href="{{ route('owner.classrooms.edit', [$academy, $classroom]) }}"
                       class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[10px] font-black text-slate-700">
                        ویرایش
                    </a>
                </div>
            </article>
        @empty
            <div class="dashboard-panel p-12 text-center xl:col-span-2">
                <div class="mx-auto max-w-md">
                    <span class="text-xs font-black text-[var(--panel-primary)]">شروع مدیریت کلاس</span>
                    <h2 class="mt-2 text-lg font-black">هنوز کلاسی در این آموزشگاه نساخته‌ای.</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-500">
                        اولین کلاس را بساز تا ظرفیت، مدرس، دانش‌آموز و اتفاقات آموزشی‌اش از همین‌جا قابل مدیریت باشد.
                    </p>
                    <a href="{{ route('owner.classrooms.create', $academy) }}"
                       class="mt-5 inline-flex rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white">
                        ساخت اولین کلاس
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
