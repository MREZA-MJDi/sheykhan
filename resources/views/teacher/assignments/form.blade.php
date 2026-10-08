@extends('layouts.teacher')
@section('title','تکلیف جدید | شیخان')
@section('header-title','ایجاد تکلیف')
@section('content')
<div class="teacher-form-shell">
    <div class="teacher-form-head"><div><span class="text-xs font-black text-[var(--panel-primary)]">ارزیابی</span><h1>تکلیف جدید</h1><p>تکلیف را به دوره و در صورت نیاز به یک کلاس مشخص وصل کن.</p></div><a href="{{ route('teacher.assignments.index') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت ←</a></div>
    <form method="POST" action="{{ route('teacher.assignments.store') }}" class="grid gap-4 md:grid-cols-2">
        @csrf
        <section class="teacher-form-section md:col-span-2"><h2>این تکلیف برای چه کسانی است؟</h2><p>اگر کلاس انتخاب نکنی، تکلیف برای همه دانش‌آموزان ثبت‌نام‌شده در دوره قابل مشاهده خواهد بود.</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="teacher-form-field"><span>دوره</span><select name="course_id" required>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>@endforeach</select></label>
                <label class="teacher-form-field"><span>کلاس / گروه <small class="text-slate-400">اختیاری</small></span><select name="classroom_id"><option value="">همه دانش‌آموزان دوره</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->title }}</option>@endforeach</select></label>
            </div>
        </section>
        <section class="teacher-form-section md:col-span-2"><h2>جزئیات تکلیف</h2><p>عنوان و دستورالعمل را طوری بنویس که دانش‌آموز دقیقاً بداند چه کاری باید انجام دهد.</p>
            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <label class="teacher-form-field md:col-span-3"><span>عنوان</span><input name="title" value="{{ old('title') }}" placeholder="مثلاً حل تمرین فصل سوم" required></label>
                <x-teacher.jalali-datetime name="due_at" :value="old('due_at')" label="مهلت تحویل" />
                <label class="teacher-form-field"><span>نمره کل</span><input type="number" step="0.01" min="0" name="max_score" value="{{ old('max_score',$assignment->max_score) }}" placeholder="۲۰" inputmode="decimal"></label>
                <label class="teacher-form-field"><span>وضعیت</span><select name="status"><option value="draft" @selected(old('status',$assignment->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$assignment->status)==='published')>منتشرشده</option><option value="closed" @selected(old('status',$assignment->status)==='closed')>بسته</option></select></label>
                <label class="teacher-form-field md:col-span-3"><span>دستورالعمل <small class="text-slate-400">اختیاری</small></span><textarea name="instructions" rows="8" placeholder="توضیح، منابع لازم، فرمت تحویل یا نکات مهم...">{{ old('instructions') }}</textarea></label>
            </div>
        </section>
        @if($errors->any())<div class="md:col-span-2 rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-7 text-rose-700">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="teacher-form-actions md:col-span-2"><a href="{{ route('teacher.assignments.index') }}">انصراف</a><button type="submit">ساخت تکلیف</button></div>
    </form>
</div>
@endsection