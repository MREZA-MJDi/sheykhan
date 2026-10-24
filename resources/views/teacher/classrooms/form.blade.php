@extends('layouts.teacher')
@section('title','ایجاد کلاس | شیخان')
@section('header-title','ایجاد کلاس')
@section('content')
<div class="teacher-form-shell">
    <div class="teacher-form-head">
        <div><span class="text-xs font-black text-[var(--panel-primary)]">کلاس و گروه</span><h1>کلاس جدید</h1><p>کلاس را به یکی از دوره‌های خودت وصل کن تا دانش‌آموز، حضور و غیاب و برنامه آن یکجا مدیریت شود.</p></div>
        <a href="{{ route('teacher.classrooms.index') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به کلاس‌ها ←</a>
    </div>
    <form method="POST" action="{{ route('teacher.classrooms.store') }}" class="grid gap-4 md:grid-cols-2">
        @csrf
        <section class="teacher-form-section md:col-span-2">
            <h2>هویت کلاس</h2><p>دوره و نامی را انتخاب کن که دانش‌آموزان در پنل خودشان خواهند دید.</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="teacher-form-field md:col-span-2"><span>دوره</span><select name="course_id" required>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>@endforeach</select>@error('course_id')<small class="teacher-field-error">{{ $message }}</small>@enderror</label>
                <label class="teacher-form-field"><span>عنوان کلاس</span><input name="title" value="{{ old('title',$classroom->title) }}" placeholder="مثلاً کلاس دهم - گروه A" required>@error('title')<small class="teacher-field-error">{{ $message }}</small>@enderror</label>
                <label class="teacher-form-field"><span>کد کلاس</span><input name="code" value="{{ old('code',$classroom->code) }}" placeholder="مثلاً PHY10-A" required dir="ltr">@error('code')<small class="teacher-field-error">{{ $message }}</small>@enderror</label>
                <label class="teacher-form-field"><span>ظرفیت <small class="text-slate-400">اختیاری</small></span><input type="number" min="1" name="capacity" value="{{ old('capacity',$classroom->capacity) }}" placeholder="۳۰" inputmode="numeric"></label>
            </div>
        </section>
        <section class="teacher-form-section md:col-span-2">
            <h2>بازه برگزاری</h2><p>اگر کلاس بازه زمانی مشخصی ندارد، این دو فیلد را خالی بگذار.</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <x-teacher.jalali-datetime name="starts_at" :value="old('starts_at')" label="شروع کلاس" />
                <x-teacher.jalali-datetime name="ends_at" :value="old('ends_at')" label="پایان کلاس" />
                <label class="teacher-form-field md:col-span-2"><span>توضیحات <small class="text-slate-400">اختیاری</small></span><textarea name="description" rows="5" placeholder="مثلاً این کلاس مخصوص حل تمرین و رفع اشکال است.">{{ old('description',$classroom->description) }}</textarea></label>
            </div>
        </section>
        @if($errors->any())<div class="md:col-span-2 rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-7 text-rose-700">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="teacher-form-actions md:col-span-2"><a href="{{ route('teacher.classrooms.index') }}">انصراف</a><button type="submit">ساخت کلاس</button></div>
    </form>
</div>
@endsection