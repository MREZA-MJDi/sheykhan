@extends('layouts.teacher')
@section('title','حضور و غیاب | شیخان')
@section('header-title','حضور و غیاب')
@section('content')
<div class="teacher-form-shell">
    <div class="teacher-form-head"><div><span class="text-xs font-black text-[var(--panel-primary)]">{{ $classroom->course?->title }}</span><h1>{{ $classroom->title }}</h1><p>وضعیت حضور هر دانش‌آموز را برای یک تاریخ مشخص ثبت کن. تغییرات قبلی همان روز به‌روزرسانی می‌شوند.</p></div><a href="{{ route('teacher.classrooms.index') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به کلاس‌ها ←</a></div>
    <form method="POST" action="{{ route('teacher.classrooms.attendance.store',$classroom) }}" class="grid gap-4">
        @csrf
        <section class="teacher-form-section">
            <h2>تاریخ حضور و غیاب</h2>
            <div class="mt-5 max-w-md"><x-teacher.jalali-datetime name="attendance_date" :value="old('attendance_date',today()->toDateString())" label="تاریخ" :dateOnly="true" help="تاریخ به شمسی نمایش داده می‌شود و به شکل استاندارد در سیستم ذخیره می‌شود." /></div>
        </section>
        <section class="teacher-form-section">
            <div class="flex items-center justify-between gap-3"><div><h2>وضعیت دانش‌آموزان</h2><p>فقط دانش‌آموزان فعال این کلاس در این فهرست قرار می‌گیرند.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black">{{ \App\Support\PersianUi::digits($classroom->students->count()) }} نفر</span></div>
            <div class="mt-5 grid gap-2">
                @forelse($classroom->students as $student)
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-sm font-bold">{{ $student->name }}</span>
                        <select name="attendance[{{ $student->id }}]" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                            <option value="present">حاضر</option><option value="late">تأخیر</option><option value="absent">غایب</option><option value="excused">موجه</option>
                        </select>
                    </div>
                @empty
                    <div class="rounded-xl bg-slate-50 p-5 text-center text-xs text-slate-500">دانش‌آموز فعالی در این کلاس نیست.</div>
                @endforelse
            </div>
        </section>
        @if($errors->any())<div class="rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-7 text-rose-700">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="teacher-form-actions"><a href="{{ route('teacher.classrooms.index') }}">انصراف</a><button type="submit">ثبت حضور و غیاب</button></div>
    </form>
</div>
@endsection