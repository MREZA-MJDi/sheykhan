@extends('layouts.owner')

@section('title', $classroom->title.' | شیخان')
@section('header-title', 'جزئیات کلاس')

@section('content')
@php
    $statusLabels = [
        'running' => 'در حال برگزاری',
        'scheduled' => 'برنامه‌ریزی‌شده',
        'finished' => 'پایان‌یافته',
        'active' => 'فعال',
        'archived' => 'آرشیو',
    ];

    $activityLabels = [
        'assignment' => 'تکلیف',
        'exam' => 'آزمون',
        'live' => 'کلاس آنلاین',
        'enrollment' => 'ثبت‌نام',
        'attendance' => 'حضور و غیاب',
    ];
@endphp

<div class="space-y-6 panel-page-enter">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-xs font-black text-[var(--panel-primary)]">{{ $academy->name }}</p>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">
                    {{ $statusLabels[$classroom->execution_status] ?? $classroom->status }}
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-black">{{ $classroom->title }}</h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ $classroom->course?->title }} · کد {{ $classroom->code }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('owner.classrooms.edit', [$academy, $classroom]) }}"
               class="rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white">
                ویرایش کلاس
            </a>
            <a href="{{ route('owner.classrooms.index', $academy) }}"
               class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-black text-slate-700">
                بازگشت به کلاس‌ها
            </a>
        </div>
    </div>

    @if($liveNow)
        <section class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="text-[10px] font-black text-emerald-700">اتفاق جاری</span>
                    <h2 class="mt-1 text-base font-black text-emerald-950">{{ $liveNow->title }}</h2>
                    <p class="mt-1 text-xs text-emerald-800">کلاس آنلاین در حال برگزاری است.</p>
                </div>
                @if($liveNow->meeting_url)
                    <a href="{{ $liveNow->meeting_url }}" target="_blank" rel="noopener"
                       class="rounded-xl bg-emerald-700 px-4 py-2.5 text-[10px] font-black text-white">
                        ورود به جلسه
                    </a>
                @endif
            </div>
        </section>
    @endif

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <article class="owner-stat-card"><span>دانش‌آموز فعال</span><strong>{{ $classroom->active_students_count }}</strong><small>عضو فعال کلاس</small></article>
        <article class="owner-stat-card"><span>ظرفیت</span><strong>{{ $classroom->capacity ?? '∞' }}</strong><small>{{ $classroom->capacity_remaining ?? '∞' }} جای خالی</small></article>
        <article class="owner-stat-card"><span>مدرس</span><strong>{{ $classroom->teachers->count() }}</strong><small>{{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}</small></article>
        <article class="owner-stat-card"><span>حضور امروز</span><strong>{{ $todayAttendance['rate'] === null ? '—' : $todayAttendance['rate'].'٪' }}</strong><small>{{ $todayAttendance['attended'] }} از {{ $todayAttendance['total'] }}</small></article>
        <article class="owner-stat-card"><span>حضور کل ثبت‌شده</span><strong>{{ $attendance['rate'] === null ? '—' : $attendance['rate'].'٪' }}</strong><small>{{ $attendance['attended'] }} از {{ $attendance['total'] }}</small></article>
    </section>

    <div class="grid gap-5 xl:grid-cols-[1.35fr_.85fr]">
        <section class="dashboard-panel p-5 sm:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-black">اتفاقات جاری کلاس</h2>
                    <p class="mt-1 text-xs text-slate-500">رویدادهایی که از داده‌های واقعی کلاس ثبت شده‌اند.</p>
                </div>
                <span class="rounded-xl bg-slate-50 px-3 py-2 text-[9px] font-bold text-slate-500">
                    {{ $activities->count() }} رویداد
                </span>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse($activities as $activity)
                    <article class="flex gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-[10px] font-black text-[var(--panel-primary)]">
                            {{ $activityLabels[$activity['type']] ?? 'رویداد' }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-xs font-black">{{ $activity['title'] }}</h3>
                                <time class="text-[9px] text-slate-400">{{ $activity['timestamp']->format('Y/m/d H:i') }}</time>
                            </div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">{{ $activity['description'] }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl bg-slate-50 p-8 text-center text-xs text-slate-500">
                        هنوز فعالیت ثبت‌شده‌ای برای این کلاس وجود ندارد.
                    </div>
                @endforelse
            </div>
        </section>

        <div class="grid gap-5">
            <section class="dashboard-panel p-5 sm:p-6">
                <h2 class="text-base font-black">مدرس‌های کلاس</h2>
                <div class="mt-4 grid gap-2">
                    @forelse($classroom->teachers as $teacher)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                            <span class="text-xs font-bold">{{ $teacher->name }}</span>
                            <a href="{{ route('owner.people.index', $academy) }}"
                               class="text-[10px] font-black text-[var(--panel-primary)]">
                                مدیریت مدرس
                            </a>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">مدرسی تعیین نشده.</div>
                    @endforelse
                </div>
            </section>

            <section class="dashboard-panel p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-black">دانش‌آموزان</h2>
                    <a href="{{ route('owner.people.index', $academy) }}"
                       class="text-[10px] font-black text-[var(--panel-primary)]">
                        مدیریت اعضا
                    </a>
                </div>
                <div class="mt-4 grid gap-2">
                    @forelse($classroom->students->take(8) as $student)
                        <div class="rounded-xl bg-slate-50 p-3">
                            <strong class="text-xs">{{ $student->name }}</strong>
                            <span class="mt-1 block truncate text-[9px] text-slate-500">{{ $student->email }}</span>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">دانش‌آموز فعالی در کلاس نیست.</div>
                    @endforelse
                </div>
                @if($classroom->students->count() > 8)
                    <p class="mt-3 text-[9px] text-slate-400">و {{ $classroom->students->count() - 8 }} دانش‌آموز دیگر…</p>
                @endif
            </section>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="dashboard-panel p-5 sm:p-6">
            <h2 class="text-base font-black">جلسات آینده</h2>
            <div class="mt-4 grid gap-2">
                @forelse($upcomingLiveClasses as $item)
                    <div class="rounded-xl bg-slate-50 p-3">
                        <strong class="block text-xs">{{ $item->title }}</strong>
                        <span class="mt-1 block text-[9px] text-slate-500">
                            {{ \Illuminate\Support\Carbon::parse($item->scheduled_at)->format('Y/m/d H:i') }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">جلسه آنلاینی در آینده نیست.</div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel p-5 sm:p-6">
            <h2 class="text-base font-black">تکالیف اخیر</h2>
            <div class="mt-4 grid gap-2">
                @forelse($recentAssignments as $item)
                    <div class="rounded-xl bg-slate-50 p-3">
                        <strong class="block text-xs">{{ $item->title }}</strong>
                        <span class="mt-1 block text-[9px] text-slate-500">
                            {{ $item->due_at ? \Illuminate\Support\Carbon::parse($item->due_at)->format('Y/m/d H:i') : 'بدون مهلت' }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">تکلیفی ثبت نشده.</div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel p-5 sm:p-6">
            <h2 class="text-base font-black">آزمون‌های اخیر</h2>
            <div class="mt-4 grid gap-2">
                @forelse($recentExams as $item)
                    <div class="rounded-xl bg-slate-50 p-3">
                        <strong class="block text-xs">{{ $item->title }}</strong>
                        <span class="mt-1 block text-[9px] text-slate-500">
                            {{ $item->starts_at ? \Illuminate\Support\Carbon::parse($item->starts_at)->format('Y/m/d H:i') : 'زمان‌بندی نشده' }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">آزمونی ثبت نشده.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
