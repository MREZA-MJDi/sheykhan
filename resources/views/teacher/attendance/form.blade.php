@extends('layouts.teacher')
@section('title','حضور و غیاب | شیخان')
@section('header-title','حضور و غیاب')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="dashboard-panel p-5 sm:p-7">
        <div class="mb-6"><p class="text-xs font-black text-[var(--panel-primary)]">{{ $classroom->course?->title }}</p><h2 class="mt-1 text-2xl font-black">{{ $classroom->title }}</h2></div>
        <form method="POST" action="{{ route('teacher.classrooms.attendance.store',$classroom) }}" class="grid gap-4">
            @csrf
            <label class="grid gap-2"><span class="text-xs font-bold">تاریخ</span><input type="date" name="attendance_date" value="{{ old('attendance_date',today()->toDateString()) }}" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <div class="grid gap-2">
                <div class="flex items-center justify-between"><span class="text-xs font-bold">وضعیت دانش‌آموزان</span><span class="text-[10px] text-slate-500">فعال‌های کلاس</span></div>
                @forelse($classroom->students as $student)
                    <div class="flex flex-col gap-3 rounded-xl border border-slate-200 p-3 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-sm font-bold">{{ $student->name }}</span>
                        <select name="attendance[{{ $student->id }}]" class="rounded-lg border border-slate-200 px-3 py-2 text-xs">
                            <option value="present">حاضر</option><option value="late">تأخیر</option><option value="absent">غایب</option><option value="excused">موجه</option>
                        </select>
                    </div>
                @empty
                    <div class="rounded-xl bg-slate-50 p-5 text-center text-xs text-slate-500">دانش‌آموز فعالی در این کلاس نیست.</div>
                @endforelse
            </div>
            <button class="mt-2 rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ثبت حضور و غیاب</button>
        </form>
    </div>
</div>
@endsection