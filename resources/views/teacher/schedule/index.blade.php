@extends('layouts.teacher')
@section('title','برنامه هفتگی | شیخان')
@section('header-title','برنامه هفتگی')
@section('content')
<div class="teacher-form-shell">
    <div class="teacher-form-head"><div><span class="text-xs font-black text-[var(--panel-primary)]">برنامه‌ریزی</span><h1>برنامه هفتگی کلاس‌ها</h1><p>زمان‌های ثابت کلاس را ثبت کن تا در داشبورد استاد و برنامه دانش‌آموزان نمایش داده شوند.</p></div></div>
    <section class="teacher-form-section">
        <h2>افزودن زمان ثابت</h2><p>هر ردیف یک بازه تکرارشونده در هفته است؛ لینک آنلاین فقط در صورت نیاز وارد شود.</p>
        <form method="POST" action="{{ route('teacher.schedule.store') }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @csrf
            <label class="teacher-form-field"><span>کلاس</span><select name="classroom_id" required>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }} · {{ $classroom->course?->title }}</option>@endforeach</select></label>
            <label class="teacher-form-field"><span>روز هفته</span><select name="weekday" required><option value="6">شنبه</option><option value="0">یکشنبه</option><option value="1">دوشنبه</option><option value="2">سه‌شنبه</option><option value="3">چهارشنبه</option><option value="4">پنجشنبه</option><option value="5">جمعه</option></select></label>
            <label class="teacher-form-field"><span>ساعت شروع</span><input type="time" name="start_time" required></label>
            <label class="teacher-form-field"><span>ساعت پایان</span><input type="time" name="end_time" required></label>
            <label class="teacher-form-field"><span>اتاق / عنوان جلسه <small class="text-slate-400">اختیاری</small></span><input name="room" placeholder="مثلاً کلاس ۳"></label>
            <label class="teacher-form-field"><span>لینک جلسه آنلاین <small class="text-slate-400">اختیاری</small></span><input name="meeting_url" type="url" dir="ltr" placeholder="https://..."></label>
            <div class="teacher-form-actions md:col-span-2 xl:col-span-3"><button type="submit">افزودن به برنامه</button></div>
        </form>
    </section>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach($classrooms as $classroom)
            <section class="teacher-form-section">
                <div class="flex items-center justify-between gap-3"><div><h2>{{ $classroom->title }}</h2><p>{{ $classroom->course?->title }}</p></div><span class="rounded-full bg-indigo-50 px-3 py-1 text-[9px] font-black text-indigo-700">{{ \App\Support\PersianUi::digits($classroom->schedules->count()) }} زمان</span></div>
                <div class="mt-4 grid gap-2">
                    @forelse($classroom->schedules->sortBy(['weekday','start_time']) as $schedule)
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3 text-xs">
                            <div><strong>{{ ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'][$schedule->weekday] ?? 'روز' }}</strong><span class="mx-2 text-slate-500">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') }} تا {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') }}</span></div>
                            <small class="text-slate-500">{{ $schedule->room ?: 'بدون اتاق' }}</small>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">برای این کلاس زمانی ثبت نشده است.</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection