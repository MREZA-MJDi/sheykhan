@extends('layouts.teacher')
@section('title','جلسه آنلاین جدید | شیخان')
@section('header-title','ایجاد جلسه آنلاین')
@section('content')
<div class="mx-auto max-w-3xl"><div class="dashboard-panel p-5 sm:p-7"><form method="POST" action="{{ route('teacher.live-classes.store') }}" class="grid gap-5 sm:grid-cols-2">@csrf
<label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">دوره</span><select name="course_id" class="rounded-xl border border-slate-200 px-3 py-3">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select></label>
<label class="grid gap-2"><span class="text-xs font-bold">کلاس</span><select name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3"><option value="">عمومی دوره</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }}</option>@endforeach</select></label>
<label class="grid gap-2"><span class="text-xs font-bold">عنوان</span><input name="title" class="rounded-xl border border-slate-200 px-3 py-3"></label>
<label class="grid gap-2"><span class="text-xs font-bold">زمان</span><input type="datetime-local" name="scheduled_at" class="rounded-xl border border-slate-200 px-3 py-3"></label>
<label class="grid gap-2"><span class="text-xs font-bold">مدت (دقیقه)</span><input type="number" name="duration_minutes" value="{{ $liveClass->duration_minutes }}" min="1" class="rounded-xl border border-slate-200 px-3 py-3"></label>
<label class="grid gap-2"><span class="text-xs font-bold">ارائه‌دهنده</span><input name="provider" placeholder="مثلاً داخلی / Jitsi" class="rounded-xl border border-slate-200 px-3 py-3"></label>
<label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">لینک جلسه</span><input type="url" name="meeting_url" class="rounded-xl border border-slate-200 px-3 py-3"></label>
<label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">توضیحات</span><textarea name="description" rows="5" class="rounded-xl border border-slate-200 px-3 py-3"></textarea></label>
<div class="flex gap-2 sm:col-span-2"><a href="{{ route('teacher.live-classes.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">انصراف</a><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ثبت جلسه</button></div>
</form></div></div>
@endsection