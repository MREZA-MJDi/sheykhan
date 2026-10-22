@extends('layouts.owner')

@section('title', 'هماهنگی جلسات با اولیا | شیخان')
@section('header-title', 'هماهنگی جلسات')

@section('content')
@php
    $statusLabels = ['scheduled' => 'برنامه‌ریزی‌شده', 'completed' => 'برگزارشده', 'cancelled' => 'لغوشده'];
@endphp

<div class="space-y-6 panel-page-enter">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">کارتابل ادمین / منشی</p>
            <h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">جلسه را برای کلاس، دانش‌آموز و ولیِ متصل به همان دانش‌آموز ثبت کن. زمان و پیام جلسه در پنل هر دو نفر نمایش داده می‌شود.</p>
        </div>
        <a href="{{ route('owner.dashboard') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به کارتابل ←</a>
    </header>

    <section class="dashboard-panel p-5 sm:p-7">
        <div>
            <h2 class="text-base font-black">ثبت جلسه هماهنگی</h2>
            <p class="mt-1 text-xs leading-6 text-slate-500">فقط کلاس‌های فعال، دانش‌آموزان عضو همان کلاس و اولیای واقعاً متصل در فهرست قرار می‌گیرند.</p>
        </div>

        @if(!auth()->user()->hasPermission('live_classes.manage'))
            <p class="mt-5 rounded-xl bg-slate-50 p-4 text-xs leading-7 text-slate-500">دسترسی شما فقط برای مشاهدهٔ جلسات است؛ برای ثبت یا تغییر جلسه باید مجوز مدیریت کلاس آنلاین داشته باشید.</p>
        @elseif($targets->isEmpty())
            <div class="mt-5 rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm leading-7 text-slate-500">
                برای ثبت جلسه، حداقل یک کلاس فعال، دانش‌آموز عضو کلاس و حساب ولیِ متصل لازم است. ابتدا عضویت‌ها را در بخش اعضا بررسی کن.
                <a href="{{ route('owner.people.index', $academy) }}" class="mt-2 inline-block font-black text-[var(--panel-primary)]">رفتن به مدیریت اعضا ←</a>
            </div>
        @else
            <form method="POST" action="{{ route('owner.meetings.store', $academy) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-bold">کلاس، دانش‌آموز و ولی</span>
                    <select name="meeting_target" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="">انتخاب مخاطب جلسه</option>
                        @foreach($targets as $target)
                            <option value="{{ $target->classroom_id }}:{{ $target->student_id }}:{{ $target->parent_id }}" @selected(old('meeting_target') === $target->classroom_id.':'.$target->student_id.':'.$target->parent_id)>
                                {{ $target->classroom_title }} — {{ $target->student_name }} — ولی: {{ $target->parent_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-owner.field-error field="meeting_target"/>
                </label>

                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-bold">عنوان جلسه</span>
                    <input name="title" value="{{ old('title') }}" placeholder="مثلاً: بررسی روند تحصیلی و هماهنگی با خانواده" required maxlength="255" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <x-owner.field-error field="title"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-bold">زمان شروع</span>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <x-owner.field-error field="scheduled_at"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-bold">زمان پایان <span class="font-normal text-slate-400">(اختیاری)</span></span>
                    <input type="datetime-local" name="scheduled_end_at" value="{{ old('scheduled_end_at') }}" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <x-owner.field-error field="scheduled_end_at"/>
                </label>

                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-bold">پیام برای دانش‌آموز و ولی <span class="font-normal text-slate-400">(اختیاری)</span></span>
                    <textarea name="message" rows="3" maxlength="1500" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="موضوع جلسه، توضیح لازم یا آمادگی موردنیاز...">{{ old('message') }}</textarea>
                    <x-owner.field-error field="message"/>
                </label>

                @if($errors->any())
                    <div class="rounded-xl bg-rose-50 p-3 text-xs leading-7 text-rose-700 md:col-span-2" role="alert">
                        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                <button type="submit" class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white md:col-span-2">ثبت جلسه و اطلاع‌رسانی به هر دو نفر</button>
            </form>
        @endif
    </section>

    <section class="dashboard-panel p-5 sm:p-7">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-base font-black">سوابق هماهنگی</h2><p class="mt-1 text-xs text-slate-500">سوابق قبلی حفظ می‌شوند؛ برای لغو یا ثبت برگزاری، وضعیت همان جلسه را تغییر بده.</p></div>
            <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">{{ \App\Support\PersianUi::digits($meetings->total()) }} جلسه</span>
        </div>

        <div class="mt-4 grid gap-3">
            @forelse($meetings as $meeting)
                <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <h3 class="text-sm font-black">{{ $meeting->title }}</h3>
                            <p class="mt-1 text-xs leading-6 text-slate-500">{{ $meeting->classroom?->title ?? 'کلاس' }} · {{ $meeting->course?->title ?? 'دوره' }}</p>
                            <p class="mt-1 text-xs leading-6 text-slate-600">دانش‌آموز: {{ $meeting->student?->name ?? '—' }} · ولی: {{ $meeting->guardian?->name ?? '—' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ \App\Support\PersianUi::date($meeting->scheduled_at) }} · {{ \App\Support\PersianUi::time($meeting->scheduled_at) }}</p>
                            @if($meeting->message)<p class="mt-2 text-xs leading-6 text-slate-500">{{ $meeting->message }}</p>@endif
                        </div>
                        @if(auth()->user()->hasPermission('live_classes.manage'))
                        <form method="POST" action="{{ route('owner.meetings.status', [$academy, $meeting]) }}" class="flex shrink-0 flex-wrap items-center gap-2">
                            @csrf @method('PATCH')
                            <label class="sr-only" for="meeting-status-{{ $meeting->id }}">وضعیت جلسه</label>
                            <select id="meeting-status-{{ $meeting->id }}" name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                                @foreach($statusLabels as $status => $label)
                                    <option value="{{ $status }}" @selected($meeting->status === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700">ذخیره وضعیت</button>
                        </form>
                        @else
                            <span class="rounded-full bg-white px-3 py-2 text-xs font-bold text-slate-600">{{ $statusLabels[$meeting->status] ?? $meeting->status }}</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">هنوز جلسه‌ای ثبت نشده است.</div>
            @endforelse
        </div>

        @if($meetings->hasPages())
            <div class="mt-5 border-t border-slate-100 pt-4 text-sm">{{ $meetings->links('components.navigation.pagination') }}</div>
        @endif
    </section>
</div>
@endsection
