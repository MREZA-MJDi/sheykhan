@extends('layouts.teacher')
@section('title', ($classroom->exists ? 'ویرایش کلاس' : 'ایجاد کلاس') . ' | شیخان')
@section('header-title', $classroom->exists ? 'ویرایش کلاس' : 'ایجاد کلاس')
@section('content')
<div class="mx-auto max-w-3xl">
    <div class="dashboard-panel p-5 sm:p-7">
        <div class="mb-6">
            <span class="teacher-kicker">CLASSROOM SETUP</span>
            <h2 class="mt-2 text-2xl font-black">{{ $classroom->exists ? 'تنظیمات کلاس' : 'کلاس جدید' }}</h2>
            <p class="mt-2 text-xs leading-7 text-slate-500">کلاس را به یک دوره متصل نگه دار و ظرفیت آن را با تعداد دانش‌آموزان واقعی هماهنگ کن.</p>
        </div>

        @if($errors->any())
            <div class="teacher-builder-alert danger mb-5">
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ $classroom->exists ? route('teacher.classrooms.update', $classroom) : route('teacher.classrooms.store') }}" class="grid gap-5 sm:grid-cols-2">
            @csrf
            @if($classroom->exists)
                @method('PATCH')
            @endif

            <label class="grid gap-2 sm:col-span-2">
                <span class="text-xs font-bold">دوره</span>
                <select name="course_id" required class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm">
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) old('course_id', $classroom->course_id) === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </label>

            <label class="grid gap-2">
                <span class="text-xs font-bold">عنوان کلاس</span>
                <input name="title" required value="{{ old('title', $classroom->title) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            </label>

            <label class="grid gap-2">
                <span class="text-xs font-bold">کد کلاس</span>
                <input name="code" required value="{{ old('code', $classroom->code) }}" dir="ltr" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            </label>

            <label class="grid gap-2">
                <span class="text-xs font-bold">ظرفیت</span>
                <input type="number" min="1" name="capacity" value="{{ old('capacity', $classroom->capacity) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            </label>

            <label class="grid gap-2">
                <span class="text-xs font-bold">وضعیت</span>
                <select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <option value="active" @selected(old('status', $classroom->status ?: 'active') === 'active')>فعال</option>
                    <option value="archived" @selected(old('status', $classroom->status) === 'archived')>آرشیو</option>
                </select>
            </label>

            <label class="grid gap-2">
                <span class="text-xs font-bold">شروع</span>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $classroom->starts_at?->format('Y-m-d\TH:i')) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            </label>

            <label class="grid gap-2">
                <span class="text-xs font-bold">پایان</span>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $classroom->ends_at?->format('Y-m-d\TH:i')) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            </label>

            <label class="grid gap-2 sm:col-span-2">
                <span class="text-xs font-bold">توضیحات</span>
                <textarea name="description" rows="5" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('description', $classroom->description) }}</textarea>
            </label>

            <div class="flex flex-wrap gap-2 sm:col-span-2">
                <a href="{{ $classroom->exists ? route('teacher.classrooms.show', $classroom) : route('teacher.classrooms.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">انصراف</a>
                <button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">{{ $classroom->exists ? 'ذخیره تغییرات' : 'ساخت کلاس' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
