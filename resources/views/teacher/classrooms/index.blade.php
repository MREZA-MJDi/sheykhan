@extends('layouts.teacher')
@section('title','کلاس‌های من | شیخان')
@section('header-title','کلاس‌های من')
@section('content')
<div class="grid gap-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-black text-[var(--panel-primary)]">کلاس‌ها</p><h2 class="mt-1 text-2xl font-black">کلاس‌های تحت مدیریت</h2><p class="mt-2 text-sm text-slate-500">دانش‌آموز، حضور و غیاب و برنامه هر کلاس را کنترل کن.</p></div>
        <a href="{{ route('teacher.classrooms.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">+ ایجاد کلاس</a>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($classrooms as $classroom)
            <article class="dashboard-panel p-5">
                <div class="flex items-start justify-between gap-3"><div><h3 class="text-sm font-black">{{ $classroom->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $classroom->course?->title }}</p></div><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">{{ $classroom->status === 'active' ? 'فعال' : $classroom->status }}</span></div>
                <div class="mt-5 grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-slate-50 p-3"><span class="text-[10px] text-slate-500">دانش‌آموز</span><strong class="mt-1 block text-lg font-black">{{ $classroom->active_students_count }}</strong></div>
                    <div class="rounded-xl bg-slate-50 p-3"><span class="text-[10px] text-slate-500">ظرفیت</span><strong class="mt-1 block text-lg font-black">{{ $classroom->capacity ?? '—' }}</strong></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('teacher.classrooms.attendance.edit',$classroom) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold">حضور و غیاب</a>
                    <a href="{{ route('teacher.courses.progress',$classroom->course) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold">پیشرفت دوره</a>
                </div>
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">هنوز کلاسی ساخته نشده است.</div>
        @endforelse
    </div>
</div>
@endsection