@extends('layouts.teacher')
@section('title','ایجاد کلاس | شیخان')
@section('header-title','ایجاد کلاس')
@section('content')
<div class="teacher-form-shell">
    <div class="teacher-form-head">
        <div><span class="text-xs font-black text-[var(--panel-primary)]">کلاس و گروه</span><h1>کلاس جدید</h1><p>کلاس را به یکی از دوره‌های خودت وصل کن تا دانش‌آموز، حضور و غیاب و برنامه آن یکجا مدیریت شود.</p>
            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-7 text-slate-700" role="note">
                <strong>دوره با کلاس فرق دارد:</strong> دوره محتوای آموزشی و درس‌هاست؛ کلاس یک گروه از دانش‌آموزان همان دوره است. جلسهٔ آنلاین را جداگانه زمان‌بندی کن و ویدئوی ضبط‌شده را در SpotPlayer دوره قرار بده.
            </div>
        </div>
        <a href="{{ route('teacher.classrooms.index') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به کلاس‌ها ←</a>
    </div>
    <form method="POST" action="{{ route('teacher.classrooms.store') }}" class="grid gap-4 md:grid-cols-2">
        @csrf
        <section class="teacher-form-section md:col-span-2">
            <h2>هویت کلاس</h2><p>دوره و نامی را انتخاب کن که دانش‌آموزان در پنل خودشان خواهند دید.</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="teacher-form-field teacher-lookup-picker md:col-span-2" data-teacher-lookup-picker data-options-url="{{ route('teacher.lookup-options', ['type' => 'courses']) }}" data-lookup-type="courses" data-required="true">
                    <label for="classroom-course-search"><span>دوره</span></label>
                    <input id="classroom-course-search" type="search" data-teacher-lookup-search value="{{ old('course_label', $classroom->course?->title) }}" placeholder="نام دوره را جست‌وجو کن…" autocomplete="off">
                    <input type="hidden" id="classroom-course-id" name="course_id" data-teacher-lookup-value value="{{ old('course_id', $classroom->course_id) }}">
                    <input type="hidden" name="course_label" data-teacher-lookup-label value="{{ old('course_label', $classroom->course?->title) }}">
                    <div data-teacher-lookup-results class="teacher-lookup-results" role="listbox"></div>
                    <button type="button" data-teacher-lookup-more hidden>نتایج بعدی</button>
                    <small data-teacher-lookup-status aria-live="polite">برای جست‌وجو حداقل دو حرف وارد کن.</small>
                    @error('course_id')<small class="teacher-field-error">{{ $message }}</small>@enderror
                </div>
                <label class="teacher-form-field"><span>عنوان کلاس</span><input name="title" value="{{ old('title',$classroom->title) }}" placeholder="مثلاً کلاس دهم - گروه A" required>@error('title')<small class="teacher-field-error">{{ $message }}</small>@enderror</label>
                <label class="teacher-form-field"><span>کد داخلی کلاس · خودکار</span><input name="code" value="{{ old('code',$classroom->code) }}" required readonly dir="ltr" class="is-generated-code" aria-describedby="teacher-classroom-code-help"><small id="teacher-classroom-code-help">کد یکتا را شیخان تولید می‌کند؛ لازم نیست چیزی وارد کنی.</small>@error('code')<small class="teacher-field-error">{{ $message }}</small>@enderror</label>
                <label class="teacher-form-field"><span>ظرفیت <small class="text-slate-400">اختیاری</small></span><input type="number" min="1" name="capacity" value="{{ old('capacity',$classroom->capacity) }}" placeholder="۳۰" inputmode="numeric"></label>
            </div>
        </section>
        <section class="teacher-form-section md:col-span-2">
            <h2>بازه برگزاری</h2><p>اگر کلاس بازه زمانی مشخصی ندارد، این دو فیلد را خالی بگذار.</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="teacher-jalali-field" data-jalali-field data-jalali-date-only="0">
    <label>
        <span>شروع کلاس</span>
        <div class="teacher-jalali-field-grid ">
            <input type="text" data-jalali-visible-date dir="ltr" inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۷/۰۲" aria-label="شروع کلاس شمسی">
            <input type="time" data-jalali-visible-time aria-label="ساعت شروع کلاس">

        </div>
        <input type="hidden" name="starts_at" data-jalali-target value="{{ old('starts_at', $classroom->starts_at ? (is_string($classroom->starts_at) ? $classroom->starts_at : $classroom->starts_at->format('Y-m-d\TH:i')) : '') }}">
        @error('starts_at')<small class="teacher-field-error">{{ $message }}</small>@enderror
    </label>
</div>
                <div class="teacher-jalali-field" data-jalali-field data-jalali-date-only="0">
    <label>
        <span>پایان کلاس</span>
        <div class="teacher-jalali-field-grid ">
            <input type="text" data-jalali-visible-date dir="ltr" inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۷/۰۲" aria-label="پایان کلاس شمسی">
            <input type="time" data-jalali-visible-time aria-label="ساعت پایان کلاس">

        </div>
        <input type="hidden" name="ends_at" data-jalali-target value="{{ old('ends_at', $classroom->ends_at ? (is_string($classroom->ends_at) ? $classroom->ends_at : $classroom->ends_at->format('Y-m-d\TH:i')) : '') }}">
        @error('ends_at')<small class="teacher-field-error">{{ $message }}</small>@enderror
    </label>
</div>
                <label class="teacher-form-field md:col-span-2"><span>توضیحات <small class="text-slate-400">اختیاری</small></span><textarea name="description" rows="5" placeholder="مثلاً این کلاس مخصوص حل تمرین و رفع اشکال است.">{{ old('description',$classroom->description) }}</textarea></label>
            </div>
        </section>
        @if($errors->any())<div class="md:col-span-2 rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-7 text-rose-700">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="teacher-form-actions md:col-span-2"><a href="{{ route('teacher.classrooms.index') }}">انصراف</a><button type="submit">ساخت کلاس</button></div>
    </form>
</div>
@endsection