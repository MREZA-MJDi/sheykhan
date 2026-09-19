@extends('layouts.teacher')
@section('title', ($assignment->exists ? 'ویرایش تکلیف' : 'تکلیف جدید') . ' | شیخان')
@section('header-title', $assignment->exists ? 'ویرایش تکلیف' : 'ایجاد تکلیف')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="dashboard-panel p-5 sm:p-7">
        <span class="teacher-kicker">ASSIGNMENT</span>
        <h2 class="mt-2 text-2xl font-black">{{ $assignment->exists ? 'تنظیمات تکلیف' : 'تکلیف جدید' }}</h2>

        <form method="POST" action="{{ $assignment->exists ? route('teacher.assignments.update', $assignment) : route('teacher.assignments.store') }}" class="mt-6 grid gap-5">
            @csrf
            @if($assignment->exists) @method('PATCH') @endif

            <label class="grid gap-2"><span class="text-xs font-bold">دوره</span><select name="course_id" required data-course-scope-select data-classroom-target="assignment-classroom" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($courses as $course)<option value="{{ $course->id }}" @selected((string) old('course_id',$assignment->course_id) === (string) $course->id)>{{ $course->title }}</option>@endforeach</select></label>
            <label class="grid gap-2"><span class="text-xs font-bold">کلاس</span><select name="classroom_id" id="assignment-classroom" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه دانش‌آموزان دوره</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" data-course-id="{{ $classroom->course_id }}" @selected((string) old('classroom_id',$assignment->classroom_id) === (string) $classroom->id)>{{ $classroom->title }} · {{ $classroom->course?->title }}</option>@endforeach</select></label>
            <label class="grid gap-2"><span class="text-xs font-bold">عنوان</span><input name="title" required value="{{ old('title',$assignment->title) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="grid gap-2"><span class="text-xs font-bold">مهلت</span><input type="datetime-local" name="due_at" value="{{ old('due_at',$assignment->due_at?->format('Y-m-d\TH:i')) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
                <label class="grid gap-2"><span class="text-xs font-bold">نمره کل</span><input type="number" step="0.01" name="max_score" value="{{ old('max_score',$assignment->max_score) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
                <label class="grid gap-2"><span class="text-xs font-bold">وضعیت</span><select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="draft" @selected(old('status',$assignment->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$assignment->status)==='published')>منتشرشده</option><option value="closed" @selected(old('status',$assignment->status)==='closed')>بسته</option></select></label>
            </div>
            <label class="grid gap-2"><span class="text-xs font-bold">دستورالعمل</span><textarea name="instructions" rows="9" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('instructions',$assignment->instructions) }}</textarea></label>
            <div class="flex gap-2"><a href="{{ route('teacher.assignments.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">انصراف</a><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">{{ $assignment->exists ? 'ذخیره تغییرات' : 'ساخت تکلیف' }}</button></div>
        </form>
    </div>
</div>
@endsection
