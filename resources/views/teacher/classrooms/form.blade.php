@extends('layouts.teacher')
@section('title','ایجاد کلاس | شیخان')
@section('header-title','ایجاد کلاس')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="dashboard-panel p-5 sm:p-7">
        <div class="mb-6"><p class="text-xs font-black text-[var(--panel-primary)]">کلاس</p><h2 class="mt-1 text-2xl font-black">کلاس جدید</h2></div>
        <form method="POST" action="{{ route('teacher.classrooms.store') }}" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">دوره</span><select name="course_id" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select>@error('course_id')<small class="text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="grid gap-2"><span class="text-xs font-bold">عنوان کلاس</span><input name="title" value="{{ old('title',$classroom->title) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">کد کلاس</span><input name="code" value="{{ old('code',$classroom->code) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">ظرفیت</span><input type="number" min="1" name="capacity" value="{{ old('capacity',$classroom->capacity) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">شروع</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">پایان</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
            <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">توضیحات</span><textarea name="description" rows="5" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('description',$classroom->description) }}</textarea></label>
            <div class="flex flex-wrap gap-2 sm:col-span-2"><a href="{{ route('teacher.classrooms.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">انصراف</a><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ذخیره کلاس</button></div>
        </form>
    </div>
</div>
@endsection