@extends('layouts.teacher')
@section('title', ($liveClass->exists ? 'ویرایش جلسه آنلاین' : 'جلسه آنلاین جدید') . ' | شیخان')
@section('header-title', $liveClass->exists ? 'ویرایش جلسه آنلاین' : 'ایجاد جلسه آنلاین')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="dashboard-panel p-5 sm:p-7">
        <span class="teacher-kicker">LIVE CLASS</span>
        <h2 class="mt-2 text-2xl font-black">{{ $liveClass->exists ? 'تنظیمات جلسه' : 'جلسه آنلاین جدید' }}</h2>

        <form method="POST" action="{{ $liveClass->exists ? route('teacher.live-classes.update',$liveClass) : route('teacher.live-classes.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
            @csrf
            @if($liveClass->exists) @method('PATCH') @endif
            <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">دوره</span><select name="course_id" required class="rounded-xl border border-slate-200 px-3 py-3">@foreach($courses as $course)<option value="{{ $course->id }}" @selected((string) old('course_id',$liveClass->course_id)===(string)$course->id)>{{ $course->title }}</option>@endforeach</select></label>
            <label class="grid gap-2"><span class="text-xs font-bold">کلاس</span><select name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3"><option value="">عمومی دوره</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected((string) old('classroom_id',$liveClass->classroom_id)===(string)$classroom->id)>{{ $classroom->title }} · {{ $classroom->course?->title }}</option>@endforeach</select></label>
            <label class="grid gap-2"><span class="text-xs font-bold">عنوان</span><input name="title" required value="{{ old('title',$liveClass->title) }}" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">زمان</span><input type="datetime-local" name="scheduled_at" required value="{{ old('scheduled_at',$liveClass->scheduled_at?->format('Y-m-d\TH:i')) }}" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">مدت (دقیقه)</span><input type="number" name="duration_minutes" value="{{ old('duration_minutes',$liveClass->duration_minutes) }}" min="1" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">ارائه‌دهنده</span><input name="provider" value="{{ old('provider',$liveClass->provider) }}" placeholder="مثلاً داخلی / Jitsi" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">لینک جلسه</span><input type="url" name="meeting_url" value="{{ old('meeting_url',$liveClass->meeting_url) }}" class="rounded-xl border border-slate-200 px-3 py-3" dir="ltr"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">وضعیت</span><select name="status" class="rounded-xl border border-slate-200 px-3 py-3"><option value="scheduled" @selected(old('status',$liveClass->status)==='scheduled')>زمان‌بندی‌شده</option><option value="live" @selected(old('status',$liveClass->status)==='live')>در حال برگزاری</option><option value="ended" @selected(old('status',$liveClass->status)==='ended')>تمام‌شده</option><option value="cancelled" @selected(old('status',$liveClass->status)==='cancelled')>لغوشده</option></select></label>
            <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">توضیحات</span><textarea name="description" rows="5" class="rounded-xl border border-slate-200 px-3 py-3">{{ old('description',$liveClass->description) }}</textarea></label>
            <div class="flex gap-2 sm:col-span-2"><a href="{{ route('teacher.live-classes.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">انصراف</a><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">{{ $liveClass->exists ? 'ذخیره تغییرات' : 'ثبت جلسه' }}</button></div>
        </form>
    </div>
</div>
@endsection
