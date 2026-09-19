@extends('layouts.teacher')
@section('title','برنامه هفتگی | شیخان')
@section('header-title','برنامه هفتگی')
@section('content')
<div class="grid gap-5">
    <div><p class="text-xs font-black text-[var(--panel-primary)]">Schedule</p><h2 class="mt-1 text-2xl font-black">برنامه هفتگی کلاس‌ها</h2><p class="mt-2 text-sm text-slate-500">زمان ثابت کلاس‌ها در داشبورد و برنامه دانش‌آموزان استفاده می‌شود.</p></div>
    <section class="dashboard-panel p-5 sm:p-7">
        <form method="POST" action="{{ route('teacher.schedule.store') }}" class="grid gap-4 sm:grid-cols-5">
            @csrf
            <select name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }}</option>@endforeach</select>
            <select name="weekday" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="6">شنبه</option><option value="0">یکشنبه</option><option value="1">دوشنبه</option><option value="2">سه‌شنبه</option><option value="3">چهارشنبه</option><option value="4">پنجشنبه</option><option value="5">جمعه</option></select>
            <input type="time" name="start_time" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <input type="time" name="end_time" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <input name="room" placeholder="اتاق / عنوان جلسه" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <input name="meeting_url" type="url" placeholder="لینک جلسه آنلاین" class="rounded-xl border border-slate-200 px-3 py-3 text-sm sm:col-span-4">
            <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">افزودن به برنامه</button>
        </form>
    </section>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($classrooms as $classroom)
            <section class="dashboard-panel p-5">
                <h3 class="text-sm font-black">{{ $classroom->title }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ $classroom->course?->title }}</p>
                <div class="mt-4 grid gap-2">
                    @forelse($classroom->schedules as $schedule)
                        <div class="rounded-xl bg-slate-50 p-3 text-xs"><strong>{{ ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'][$schedule->weekday] ?? 'روز' }}</strong><span class="mx-2 text-slate-500">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') }} تا {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') }}</span><small class="text-slate-500">{{ $schedule->room }}</small></div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">برنامه‌ای ثبت نشده است.</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection