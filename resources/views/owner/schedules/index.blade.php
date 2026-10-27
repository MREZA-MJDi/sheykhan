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
        لینک Jitsi در صورت خالی بودن خودکار ساخته می‌شود. برای Google Meet و Zoom باید لینک دعوت واقعی را از همان سرویس وارد کنی؛ شیخان لینک ساختگی تولید نمی‌کند.
    </section>

    <section class="owner-calendar-section" aria-label="تقویم هفتگی کلاس‌ها">
        <div class="owner-section-heading"><div><h2>برنامه هفتگی</h2><p>روز و ساعت برگزاری کلاس‌ها را بررسی کن؛ برای جلسه‌های تاریخ‌دار از صفحهٔ کلاس‌های آنلاین مدرس استفاده می‌شود.</p></div></div>
        <x-education.weekly-schedule-calendar :classrooms="$classrooms" />
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
                            <form method="POST" action="{{ route('owner.schedules.destroy', [$academy, $classroom, $schedule]) }}" data-confirm="این زمان هفتگی از کلاس حذف شود؟">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-black text-rose-700">حذف بازه</button>
                            </form>
                            @endif
                        </div>
                    @empty
                        <div class="owner-empty-state rounded-xl border border-dashed border-slate-200 p-4 text-xs text-slate-500">برای این کلاس هنوز زمان و لینک هفتگی تعریف نشده است. از فرم پایین کارت، روز، ساعت و سرویس جلسه را ثبت کن.</div>
                    @endforelse
                </div>

                @if(auth()->user()->hasPermission('live_classes.manage'))
                <div class="owner-schedule-editor mt-5 border-t border-slate-100 pt-5">
                    <div class="owner-section-heading"><div><h3>افزودن زمان / لینک</h3><p>برای هر روز و بازهٔ زمانی یک ورودی جدا بساز. سرویس و لینک باید متعلق به همان جلسه باشند.</p></div></div>
                    <form method="POST" action="{{ route('owner.schedules.store', [$academy, $classroom]) }}" class="owner-schedule-form mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" data-meeting-provider-form>
                        @csrf
                        <label class="grid gap-1">
                            <span class="text-xs font-bold">روز هفته</span>
                            <select name="weekday" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                                @foreach([6 => 'شنبه', 0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string)old('weekday', '6') === (string)$value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-owner.field-error field="weekday"/>
                        </label>
                        <label class="grid gap-1"><span class="text-xs font-bold">شروع · ساعت ایران</span><input lang="fa-IR" dir="ltr" type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="start_time"/></label>
                        <label class="grid gap-1"><span class="text-xs font-bold">پایان · ساعت ایران</span><input lang="fa-IR" dir="ltr" type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="end_time"/></label>
                        <label class="grid gap-1"><span class="text-xs font-bold">اتاق / عنوان <small class="font-normal text-slate-400">اختیاری</small></span><input name="room" value="{{ old('room') }}" maxlength="255" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="مثلاً کلاس ۳"><x-owner.field-error field="room"/></label>
                        <label class="grid gap-1">
                            <span class="text-xs font-bold">سرویس</span>
                            <select name="provider" data-meeting-provider required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                                <option value="Jitsi" @selected(old('provider', 'Jitsi') === 'Jitsi')>Jitsi · خودکار</option>
                                <option value="Google Meet" @selected(old('provider') === 'Google Meet')>Google Meet</option>
                                <option value="Zoom" @selected(old('provider') === 'Zoom')>Zoom</option>
                                <option value="سایر" @selected(old('provider') === 'سایر')>سایر سرویس‌ها</option>
                            </select>
                            <x-owner.field-error field="provider"/>
                        </label>
                        <label class="grid gap-1 sm:col-span-2 xl:col-span-3">
                            <span class="text-xs font-bold">لینک ورود <small class="font-normal text-slate-400">(برای Jitsi اختیاری، برای سرویس‌های دیگر الزامی)</small></span>
                            <input type="url" name="meeting_url" value="{{ old('meeting_url') }}" maxlength="2000" autocomplete="url" dir="ltr" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="https://...">
                            <x-owner.field-error field="meeting_url"/>
                        </label>
                        <div class="flex items-end sm:col-span-2 xl:col-span-4">
                            <button type="submit" class="owner-primary-action w-full rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white sm:w-auto">ذخیره زمان و لینک</button>
                        </div>
                        @if($errors->any())
                            <div class="rounded-xl bg-rose-50 p-3 text-xs leading-7 text-rose-700 sm:col-span-2 xl:col-span-4" role="alert">
                                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif
                    </form>
                </div>
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
