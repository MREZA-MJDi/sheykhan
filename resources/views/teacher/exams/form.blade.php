@extends('layouts.teacher')
@section('title','آزمون جدید | شیخان')
@section('header-title','ایجاد آزمون')
@section('content')
<div class="teacher-form-shell">
    <div class="teacher-form-head"><div><span class="text-xs font-black text-[var(--panel-primary)]">ارزیابی</span><h1>ساخت آزمون</h1><p>اول دوره، کلاس و زمان را مشخص کن؛ بعد برای هر سؤال نوع را انتخاب کن. فرم، پاسخ‌دهی و تصحیح دانش‌آموز به نوع هر سؤال وابسته است.</p></div><a href="{{ route('teacher.exams.index') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت ←</a></div>
    <form method="POST" action="{{ route('teacher.exams.store') }}" class="grid gap-4" data-exam-builder>
        @csrf
        <section class="teacher-form-section"><h2>محدوده و مشخصات</h2><p>آزمون را به دوره و در صورت نیاز به یک کلاس مشخص اختصاص بده.</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="teacher-form-field"><span>دوره</span><select name="course_id" data-course-select required>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>@endforeach</select></label>
                <label class="teacher-form-field"><span>کلاس / گروه <small class="text-slate-400">اختیاری</small></span><select name="classroom_id" data-course-classroom-select data-course-select="[name=course_id]"><option value="">همه دانش‌آموزان دوره</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" data-course-id="{{ $classroom->course_id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->title }}</option>@endforeach</select></label>
                <label class="teacher-form-field md:col-span-2"><span>عنوان آزمون</span><input name="title" value="{{ old('title') }}" placeholder="مثلاً آزمون فصل‌های اول فیزیک" required></label>
                <label class="teacher-form-field"><span>مدت آزمون <small class="text-slate-400">دقیقه</small></span><input type="number" name="duration_minutes" value="{{ old('duration_minutes',$exam->duration_minutes) }}" min="1" inputmode="numeric"></label>
                <label class="teacher-form-field"><span>تعداد دفعات مجاز</span><input type="number" name="attempts_allowed" value="{{ old('attempts_allowed',$exam->attempts_allowed) }}" min="1" inputmode="numeric"></label>
                <x-teacher.jalali-datetime name="starts_at" :value="old('starts_at')" label="شروع آزمون" />
                <x-teacher.jalali-datetime name="ends_at" :value="old('ends_at')" label="پایان آزمون" />
                <label class="teacher-form-field"><span>وضعیت</span><select name="status"><option value="draft" @selected(old('status',$exam->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$exam->status)==='published')>منتشرشده</option><option value="closed" @selected(old('status',$exam->status)==='closed')>بسته</option></select></label>
                <label class="teacher-form-field md:col-span-2"><span>توضیحات <small class="text-slate-400">اختیاری</small></span><textarea name="description" rows="5" placeholder="راهنمایی برای دانش‌آموز، زمان‌بندی، منابع مجاز و...">{{ old('description') }}</textarea></label>
            </div>
        </section>
        <section class="teacher-form-section">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><h2>سؤال‌های آزمون</h2><p>تک‌گزینه‌ای و چندپاسخی خودکار تصحیح می‌شوند؛ تشریحی برای معلم به صف بررسی دستی می‌رود. نوع سؤال را عوض کنی، فرم پاسخ صحیح هم عوض می‌شود.</p></div><button type="button" data-exam-add-question class="rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-black text-white">+ افزودن سؤال</button></div>
            <div class="mt-5 grid gap-4" data-exam-questions></div>
            <template data-exam-question-template>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" data-exam-question>
                    <div class="mb-4 flex items-center justify-between"><strong class="text-xs">سؤال جدید</strong><button type="button" data-exam-remove-question class="text-xs font-bold text-rose-600">حذف سؤال</button></div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="teacher-form-field md:col-span-2"><span>متن سؤال</span><textarea data-name="question" rows="3" placeholder="متن سؤال را وارد کن..."></textarea></label>
                        <label class="teacher-form-field"><span>نوع سؤال</span><select data-name="type"><option value="text">تشریحی</option><option value="single">تک‌گزینه‌ای</option><option value="multiple">چندگزینه‌ای</option><option value="checkbox">چک‌باکسی</option></select></label>
                        <label class="teacher-form-field"><span>نمره</span><input data-name="score" type="number" value="1" min="0" step="0.25"></label>
                        <label class="teacher-form-field md:col-span-2" data-question-options>
                            <span>گزینه‌های پاسخ <small class="text-slate-400">هر گزینه را در یک خط بنویس؛ برای سؤال تشریحی لازم نیست.</small></span>
                            <textarea data-name="options_text" rows="4" placeholder="گزینه اول&#10;گزینه دوم&#10;گزینه سوم&#10;گزینه چهارم"></textarea>
                            <small class="text-slate-500">بعد از نوشتن گزینه‌ها، انتخاب پاسخ صحیح در پایین به‌صورت پویا ساخته می‌شود.</small>
                        </label>
                        <div class="teacher-form-field md:col-span-2" data-question-correct>
                            <span data-correct-answer-label>پاسخ صحیح</span>
                            <div class="grid gap-2" data-correct-answer-choices></div>
                            <small data-correct-answer-help class="text-slate-500">نوع سؤال را انتخاب کن تا روش تعیین پاسخ صحیح متناسب با آن نمایش داده شود.</small>
                        </div>
                    </div>
                </div>
            </template>
        </section>
        @if($errors->any())<div class="rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-7 text-rose-700">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="teacher-form-actions"><a href="{{ route('teacher.exams.index') }}">انصراف</a><button type="submit">ساخت آزمون</button></div>
    </form>
</div>
@endsection