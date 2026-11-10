@extends('layouts.owner')

@section('title', 'لینک کلاس‌های آنلاین | شیخان')
@section('header-title', 'لینک کلاس‌های آنلاین')

@section('content')
@php
    $weekdays = [0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه'];
@endphp

<div class="space-y-6 panel-page-enter owner-workspace-page owner-online-links-page">
    <header class="owner-page-intro flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">کارتابل ادمین / منشی</p>
            <h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">لینک و زمان تکرارشوندهٔ هر کلاس را ثبت کن. زمان‌ها با برنامهٔ هفتگی فعلی مدرس و کلاس مشترک‌اند؛ جلسه‌های واقعیِ دارای تاریخ از بخش کلاس‌های آنلاین مدرس مدیریت می‌شوند.</p>
        </div>
        <a href="{{ route('owner.dashboard') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به کارتابل ←</a>
    </header>

    <section class="owner-info-banner rounded-2xl border border-sky-100 bg-sky-50 p-4 text-xs leading-7 text-sky-900" aria-label="راهنمای لینک جلسه">
        برای هر برنامهٔ هفتگی، لینک واقعی اتاق Skyroom را از پنل اسکای‌روم وارد کن. لینک باید از دامنهٔ رسمی اسکای‌روم و مسیر اتاق باشد؛ شیخان لینک ساختگی تولید نمی‌کند.
    </section>

    <section class="owner-calendar-section" aria-label="تقویم هفتگی کلاس‌ها">
        <div class="owner-section-heading"><div><h2>برنامه هفتگی</h2><p>روز و ساعت برگزاری کلاس‌ها را بررسی کن؛ برای جلسه‌های تاریخ‌دار از صفحهٔ کلاس‌های آنلاین مدرس استفاده می‌شود.</p></div></div>
        @include('owner.schedules.partials.weekly-calendar', ['classrooms' => $classrooms])
    </section>

    <section class="grid gap-4 owner-schedule-list" aria-label="کلاس‌ها و لینک‌های ورود">
        @forelse($classrooms as $classroom)
            <article class="dashboard-panel owner-session-card overflow-hidden p-5 sm:p-6">
                <div class="owner-session-heading flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-base font-black">{{ $classroom->title }}</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $classroom->course?->title ?? 'دوره نامشخص' }} · {{ \App\Support\PersianUi::digits($classroom->schedules->count()) }} بازه هفتگی</p>
                    </div>
                    <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">{{ $classroom->code }}</span>
                </div>

                <div class="owner-session-slots mt-4 grid gap-3">
                    @forelse($classroom->schedules as $schedule)
                        <div class="owner-session-slot flex flex-col gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <strong class="block text-sm font-black">{{ $weekdays[$schedule->weekday] ?? 'روز نامشخص' }} · {{ \App\Support\PersianUi::digits(\Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i')) }} تا {{ \App\Support\PersianUi::digits(\Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i')) }}</strong>
                                <p class="mt-1 text-xs text-slate-500">{{ $schedule->room ?: 'بدون اتاق' }} · {{ $schedule->provider ?: 'سرویس ثبت‌نشده' }}</p>
                                @if($schedule->meeting_url)
                                    <a href="{{ $schedule->meeting_url }}" target="_blank" rel="noopener noreferrer" dir="ltr" class="mt-2 inline-block max-w-full break-all text-xs font-bold text-[var(--panel-primary)] underline underline-offset-4">ورود به جلسه ↗</a>
                                @endif
                            </div>
                            @if(auth()->user()->hasPermission('live_classes.manage'))
<div class="flex shrink-0 flex-wrap items-center gap-2">
                                <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-schedule-edit-{{ $schedule->id }}">ویرایش</button>
                                <form method="POST" action="{{ route('owner.schedules.destroy', [$academy, $classroom, $schedule]) }}" data-confirm="این زمان هفتگی از کلاس حذف شود؟">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-black text-rose-700">حذف بازه</button>
                                </form>
                            </div>
                            <dialog class="owner-floating-modal" id="owner-schedule-edit-{{ $schedule->id }}" aria-labelledby="owner-schedule-edit-title-{{ $schedule->id }}" @if($errors->any() && old('form_modal') === 'schedule-edit-' . $schedule->id) data-owner-modal-open-on-load @endif>
                                <header class="owner-floating-modal__header">
                                    <div><span class="owner-record-modal-kicker">ویرایش برنامه هفتگی</span><h2 id="owner-schedule-edit-title-{{ $schedule->id }}">ویرایش زمان · {{ $classroom->title }}</h2></div>
                                    <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
                                </header>
                                <div class="owner-floating-modal__body">
                                    @include('owner.schedules.partials.form', [
                                        'schedule' => $schedule,
                                        'formModal' => 'schedule-edit-' . $schedule->id,
                                        'action' => route('owner.schedules.update', [$academy, $classroom, $schedule]),
                                        'method' => 'PATCH',
                                        'buttonLabel' => 'ذخیره تغییرات',
                                    ])
                                </div>
                            </dialog>
                            @endif
                        </div>
                    @empty
                        <div class="owner-empty-state rounded-xl border border-dashed border-slate-200 p-4 text-xs text-slate-500">برای این کلاس هنوز زمان و لینک هفتگی تعریف نشده است. با دکمهٔ «افزودن زمان / لینک» روز، ساعت و سرویس جلسه را در پنجرهٔ شناور ثبت کن.</div>
                    @endforelse
                </div>

                @if(auth()->user()->hasPermission('live_classes.manage'))
                <div class="owner-schedule-editor mt-5 border-t border-slate-100 pt-5">
                    <button type="button" class="owner-modal-trigger owner-modal-trigger--primary" data-owner-modal-open="owner-schedule-modal-{{ $classroom->id }}">
                        ＋ افزودن زمان / لینک برای این کلاس
                    </button>
                </div>
                <dialog class="owner-floating-modal" id="owner-schedule-modal-{{ $classroom->id }}" aria-labelledby="owner-schedule-modal-title-{{ $classroom->id }}" @if($errors->any() && old('form_modal') === 'schedule-' . $classroom->id) data-owner-modal-open-on-load @endif>
                    <header class="owner-floating-modal__header">
                        <div><span class="owner-record-modal-kicker">برنامه هفتگی کلاس</span><h2 id="owner-schedule-modal-title-{{ $classroom->id }}">افزودن زمان / لینک · {{ $classroom->title }}</h2></div>
                        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
                    </header>
                    <div class="owner-floating-modal__body">
                    @include('owner.schedules.partials.form', [
                        'schedule' => null,
                        'formModal' => 'schedule-' . $classroom->id,
                        'action' => route('owner.schedules.store', [$academy, $classroom]),
                        'method' => 'POST',
                        'buttonLabel' => 'ذخیره زمان و لینک',
                    ])
                    </div>
                </dialog>
                @else
                    <p class="mt-5 border-t border-slate-100 pt-4 text-xs leading-7 text-slate-500">این بخش فقط در حالت مشاهده است؛ برای افزودن یا حذف زمان و لینک، مجوز مدیریت کلاس آنلاین لازم است.</p>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center">
                <strong class="text-sm font-black">کلاس فعالی برای برنامه‌ریزی آنلاین وجود ندارد.</strong>
                <p class="mt-2 text-xs leading-6 text-slate-500">ابتدا کلاس را بساز و مدرس فعال آن را مشخص کن.</p>
                <a href="{{ route('owner.classrooms.index', $academy) }}" class="mt-3 inline-block font-black text-[var(--panel-primary)]">رفتن به کلاس‌ها ←</a>
            </div>
        @endforelse
    </section>

    @if($classrooms->hasPages())
        <div class="dashboard-panel p-4 text-sm">{{ $classrooms->links('components.navigation.pagination') }}</div>
    @endif
</div>
@endsection
