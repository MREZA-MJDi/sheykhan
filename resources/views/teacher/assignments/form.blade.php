@extends('layouts.teacher')
@section('title','تکلیف جدید | شیخان')
@section('header-title','ایجاد تکلیف')
@section('content')
<div class="mx-auto max-w-3xl"><div class="dashboard-panel p-5 sm:p-7"><form method="POST" action="{{ route('teacher.assignments.store') }}" class="grid gap-5">
@csrf
<label class="grid gap-2"><span class="text-xs font-bold">دوره</span><select name="course_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select></label>
<label class="grid gap-2"><span class="text-xs font-bold">کلاس</span><select name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه دانش‌آموزان دوره</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }}</option>@endforeach</select></label>
<label class="grid gap-2"><span class="text-xs font-bold">عنوان</span><input name="title" value="{{ old('title') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
<div class="grid gap-4 sm:grid-cols-3"><label class="grid gap-2"><span class="text-xs font-bold">مهلت</span><input type="datetime-local" name="due_at" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label><label class="grid gap-2"><span class="text-xs font-bold">نمره کل</span><input type="number" step="0.01" name="max_score" value="{{ $assignment->max_score }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label><label class="grid gap-2"><span class="text-xs font-bold">وضعیت</span><select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="draft">پیش‌نویس</option><option value="published">منتشرشده</option><option value="closed">بسته</option></select></label></div>
<label class="grid gap-2"><span class="text-xs font-bold">دستورالعمل</span><textarea name="instructions" rows="8" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('instructions') }}</textarea></label>
<div class="flex gap-2"><a href="{{ route('teacher.assignments.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">انصراف</a><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ساخت تکلیف</button></div>
</form></div></div>
@endsection