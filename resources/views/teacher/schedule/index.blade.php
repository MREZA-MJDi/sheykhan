@extends('layouts.teacher')
@section('title','برنامه هفتگی | شیخان')
@section('header-title','برنامه هفتگی')
@section('content')
<div class="teacher-detail-page">
    @if(session('success'))
        <div class="teacher-builder-alert success">{{ session('success') }}</div>
    @endif

    <section class="teacher-list-hero">
        <div>
            <span class="teacher-kicker">SCHEDULE</span>
            <h2>برنامه هفتگی کلاس‌ها</h2>
            <p>برنامه‌ای که اینجا تعریف می‌کنی مبنای داشبورد استاد و تجربه کلاس دانش‌آموز است.</p>
        </div>
    </section>

    <section class="dashboard-panel p-5 sm:p-7">
        <form method="POST" action="{{ route('teacher.schedule.store') }}" class="grid gap-4 sm:grid-cols-5">
            @csrf
            <select name="classroom_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }}</option>@endforeach</select>
            <select name="weekday" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="6">شنبه</option><option value="0">یکشنبه</option><option value="1">دوشنبه</option><option value="2">سه‌شنبه</option><option value="3">چهارشنبه</option><option value="4">پنجشنبه</option><option value="5">جمعه</option></select>
            <input type="time" name="start_time" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <input type="time" name="end_time" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <input name="room" placeholder="اتاق / عنوان جلسه" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <input name="meeting_url" type="url" placeholder="لینک جلسه آنلاین" class="rounded-xl border border-slate-200 px-3 py-3 text-sm sm:col-span-4">
            <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">افزودن به برنامه</button>
        </form>
    </section>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach($classrooms as $classroom)
            <section class="dashboard-panel p-5">
                <div class="flex items-center justify-between gap-3">
                    <div><h3 class="text-sm font-black">{{ $classroom->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $classroom->course?->title }}</p></div>
                    <a href="{{ route('teacher.classrooms.show', $classroom) }}" class="text-[10px] font-black text-indigo-600">کلاس ←</a>
                </div>

                <div class="mt-4 grid gap-3">
                    @forelse($classroom->schedules as $schedule)
                        <details class="rounded-xl bg-slate-50 p-3">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-xs">
                                <span><strong>{{ ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'][$schedule->weekday] ?? 'روز' }}</strong><span class="mx-2 text-slate-500">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') }} تا {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') }}</span></span>
                                <span class="text-slate-400">ویرایش</span>
                            </summary>
                            <form method="POST" action="{{ route('teacher.schedule.update', $schedule) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                                @csrf @method('PATCH')
                                <select name="weekday" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs">
                                    @foreach([6=>'شنبه',0=>'یکشنبه',1=>'دوشنبه',2=>'سه‌شنبه',3=>'چهارشنبه',4=>'پنجشنبه',5=>'جمعه'] as $dayValue => $dayLabel)
                                        <option value="{{ $dayValue }}" @selected($schedule->weekday === $dayValue)>{{ $dayLabel }}</option>
                                    @endforeach
                                </select>
                                <input type="time" name="start_time" value="{{ $schedule->start_time }}" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs">
                                <input type="time" name="end_time" value="{{ $schedule->end_time }}" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs">
                                <input name="room" value="{{ $schedule->room }}" placeholder="اتاق" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs">
                                <input name="meeting_url" value="{{ $schedule->meeting_url }}" placeholder="لینک آنلاین" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs sm:col-span-2">
                                <button class="rounded-lg bg-slate-900 px-3 py-2 text-[10px] font-black text-white">ذخیره</button>
                            </form>
                            <form method="POST" action="{{ route('teacher.schedule.destroy', $schedule) }}" data-confirm="این جلسه از برنامه حذف شود؟" class="mt-2">
                                @csrf @method('DELETE')
                                <button class="teacher-builder-delete" type="submit">حذف جلسه</button>
                            </form>
                        </details>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">برنامه‌ای ثبت نشده است.</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
