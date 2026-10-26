@extends('layouts.owner')

@section('title', 'حضور و غیاب آموزشگاه | شیخان')
@section('header-title', 'سوابق حضور و غیاب')

@section('content')
@php
    $statusLabels = ['present' => 'حاضر', 'late' => 'با تأخیر', 'absent' => 'غایب', 'excused' => 'غیبت موجه'];
    $statusStyles = ['present' => 'bg-emerald-50 text-emerald-700', 'late' => 'bg-amber-50 text-amber-700', 'absent' => 'bg-rose-50 text-rose-700', 'excused' => 'bg-slate-100 text-slate-600'];
@endphp

<div class="space-y-6 panel-page-enter">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">کارتابل ادمین / منشی</p>
            <h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">فهرست صفحه‌بندی‌شدهٔ رکوردهای حضور و غیاب همین آموزشگاه؛ با جست‌وجوی دانش‌آموز، کلاس، تاریخ و وضعیت.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2"><a class="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold" href="{{ route('owner.attendance.export', array_merge(['academy' => $academy], request()->only(['q','classroom_id','attendance_date','status']), ['format' => 'csv'])) }}">خروجی Excel / CSV</a><a class="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold" href="{{ route('owner.attendance.export', array_merge(['academy' => $academy], request()->only(['q','classroom_id','attendance_date','status']), ['format' => 'word'])) }}">گزارش Word</a><a href="{{ route('owner.dashboard') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به کارتابل ←</a></div>
    </header>

    <section class="grid gap-3 grid-cols-2 xl:grid-cols-5">
        @foreach([['کل رکوردها', $summary->total ?? 0], ['حاضر', $summary->present_count ?? 0], ['با تأخیر', $summary->late_count ?? 0], ['غایب', $summary->absent_count ?? 0], ['موجه', $summary->excused_count ?? 0]] as [$label, $value])
            <article class="owner-stat-card"><span>{{ $label }}</span><strong>{{ \App\Support\PersianUi::digits($value) }}</strong><small>بر اساس فیلترهای فعلی</small></article>
        @endforeach
    </section>

    <section class="dashboard-panel p-5 sm:p-6">
        <form method="GET" action="{{ route('owner.attendance.index', $academy) }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <label class="grid gap-1"><span class="text-xs font-bold">نام یا ایمیل دانش‌آموز</span><input name="q" value="{{ $filters['q'] ?? '' }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="جست‌وجو..."></label>
            <label class="grid gap-1"><span class="text-xs font-bold">کلاس</span><select name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه کلاس‌ها</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected((string)($filters['classroom_id'] ?? '') === (string)$classroom->id)>{{ $classroom->title }}</option>@endforeach</select></label>
            <label class="grid gap-1"><span class="text-xs font-bold">تاریخ</span><input type="date" name="attendance_date" value="{{ $filters['attendance_date'] ?? '' }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
            <label class="grid gap-1"><span class="text-xs font-bold">وضعیت</span><select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه وضعیت‌ها</option>@foreach($statusLabels as $status => $label)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>@endforeach</select></label>
            <div class="flex flex-wrap items-center gap-2 sm:col-span-2 xl:col-span-4"><button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">اعمال فیلتر</button><a href="{{ route('owner.attendance.index', $academy) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-black text-slate-600">پاک‌کردن فیلترها</a><span class="text-xs text-slate-500">{{ \App\Support\PersianUi::digits($attendances->total()) }} رکورد منطبق</span></div>
        </form>
    </section>

    <section class="dashboard-panel p-5 sm:p-6">
        <div class="mb-4"><h2 class="text-base font-black">سوابق ثبت‌شده</h2><p class="mt-1 text-xs text-slate-500">نمایش ۲۵ رکورد در هر صفحه؛ فیلترها بین صفحه‌ها حفظ می‌شوند.</p></div>
        <div class="grid gap-3">
            @forelse($attendances as $attendance)
                <article class="flex flex-col gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <strong class="block text-sm font-black">{{ $attendance->student?->name ?? 'حساب دانش‌آموز در دسترس نیست' }}</strong>

                        <p class="mt-1 text-xs text-slate-600">{{ $attendance->classroom?->title ?? 'کلاس' }} · {{ $attendance->classroom?->course?->title ?? 'دوره' }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-3 py-1.5 text-xs font-black {{ $statusStyles[$attendance->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$attendance->status] ?? $attendance->status }}</span>
                        <span class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-600">{{ \App\Support\PersianUi::date($attendance->attendance_date) }}</span>
                        @if($attendance->marker)<span class="text-xs text-slate-500">ثبت توسط {{ $attendance->marker->name }}</span>@endif
                    </div>
                    @if($attendance->note)<p class="text-xs leading-6 text-slate-500 sm:basis-full">{{ $attendance->note }}</p>@endif
                    @if(auth()->user()->hasPermission('attendance.manage'))
                        <details class="w-full rounded-xl border border-slate-200 bg-white p-3 sm:basis-full">
                            <summary class="cursor-pointer text-xs font-black text-[var(--panel-primary)]">اصلاح وضعیت، مدت حضور یا یادداشت</summary>
                            <form method="POST" action="{{ route('owner.attendance.update', [$academy, $attendance->id]) }}" class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                @csrf @method('PATCH')
                                <label class="grid gap-1 text-xs font-bold">وضعیت
                                    <select name="status" class="min-h-10 rounded-lg border border-slate-200 px-3 text-sm">
                                        @foreach($statusLabels as $key => $label)<option value="{{ $key }}" @selected($attendance->status === $key)>{{ $label }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="grid gap-1 text-xs font-bold">مدت برنامه‌ریزی‌شده (دقیقه)<input type="number" name="scheduled_minutes" min="0" max="1440" value="{{ $attendance->scheduled_minutes }}" class="min-h-10 rounded-lg border border-slate-200 px-3 text-sm"></label>
                                <label class="grid gap-1 text-xs font-bold">مدت حضور (دقیقه)<input type="number" name="attended_minutes" min="0" max="1440" value="{{ $attendance->attended_minutes }}" class="min-h-10 rounded-lg border border-slate-200 px-3 text-sm"></label>
                                <label class="grid gap-1 text-xs font-bold">یادداشت<input name="note" maxlength="1000" value="{{ $attendance->note }}" class="min-h-10 rounded-lg border border-slate-200 px-3 text-sm"></label>
                                <div class="sm:col-span-2 xl:col-span-4 flex flex-wrap items-center justify-between gap-2"><p class="text-xs text-slate-500">آخرین ویرایش با نام کاربر ثبت‌کننده نگهداری می‌شود.</p><button class="min-h-10 rounded-lg bg-[var(--panel-primary)] px-4 text-xs font-black text-white">ذخیره اصلاحات</button></div>
                            </form>
                        </details>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm leading-7 text-slate-500">رکوردی با این فیلترها پیدا نشد. فیلترها را تغییر بده یا پاک کن.</div>
            @endforelse
        </div>
        @if($attendances->hasPages())
            <div class="mt-5 border-t border-slate-100 pt-4 text-sm">{{ $attendances->links('components.navigation.pagination') }}</div>
        @endif
    </section>
</div>
@endsection
