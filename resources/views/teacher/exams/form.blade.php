@extends('layouts.teacher')
@section('title','آزمون جدید | شیخان')
@section('header-title','ایجاد آزمون')
@section('content')
<div class="mx-auto max-w-4xl">
    <form method="POST" action="{{ route('teacher.exams.store') }}" class="grid gap-5" data-exam-builder>
        @csrf
        <section class="dashboard-panel p-5 sm:p-7">
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2"><span class="text-xs font-bold">دوره</span><select name="course_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select></label>
                <label class="grid gap-2"><span class="text-xs font-bold">کلاس</span><select name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">عمومی دوره</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }}</option>@endforeach</select></label>
                <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">عنوان آزمون</span><input name="title" class="rounded-xl border border-slate-200 px-3 py-3"></label>
                <label class="grid gap-2"><span class="text-xs font-bold">مدت (دقیقه)</span><input type="number" name="duration_minutes" value="{{ $exam->duration_minutes }}" min="1" class="rounded-xl border border-slate-200 px-3 py-3"></label>
                <label class="grid gap-2"><span class="text-xs font-bold">تعداد دفعات مجاز</span><input type="number" name="attempts_allowed" value="{{ $exam->attempts_allowed }}" min="1" class="rounded-xl border border-slate-200 px-3 py-3"></label>
                <label class="grid gap-2"><span class="text-xs font-bold">شروع</span><input type="datetime-local" name="starts_at" class="rounded-xl border border-slate-200 px-3 py-3"></label>
                <label class="grid gap-2"><span class="text-xs font-bold">پایان</span><input type="datetime-local" name="ends_at" class="rounded-xl border border-slate-200 px-3 py-3"></label>
                <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">وضعیت</span><select name="status" class="rounded-xl border border-slate-200 px-3 py-3"><option value="draft">پیش‌نویس</option><option value="published">منتشرشده</option><option value="closed">بسته</option></select></label>
                <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">توضیحات</span><textarea name="description" rows="5" class="rounded-xl border border-slate-200 px-3 py-3"></textarea></label>
            </div>
        </section>

        <section class="dashboard-panel p-5 sm:p-7">
            <div class="flex items-center justify-between gap-3"><div><h3 class="text-base font-black">سؤال‌ها</h3><p class="mt-1 text-xs text-slate-500">سؤال‌های objective را می‌توانی خودکار تصحیح کنی.</p></div><button type="button" data-exam-add-question class="rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-black text-white">+ سؤال</button></div>
            <div class="mt-5 grid gap-4" data-exam-questions></div>
            <template data-exam-question-template>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" data-exam-question>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">متن سؤال</span><textarea data-name="question" rows="3" class="rounded-xl border border-slate-200 bg-white px-3 py-3"></textarea></label>
                        <label class="grid gap-2"><span class="text-xs font-bold">نوع</span><select data-name="type" class="rounded-xl border border-slate-200 bg-white px-3 py-3"><option value="text">تشریحی</option><option value="single">تک‌گزینه‌ای</option><option value="multiple">چندگزینه‌ای</option><option value="checkbox">چک‌باکسی</option></select></label>
                        <label class="grid gap-2"><span class="text-xs font-bold">نمره</span><input data-name="score" type="number" value="1" min="0" step="0.25" class="rounded-xl border border-slate-200 bg-white px-3 py-3"></label>
                        <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">گزینه‌ها (هر گزینه در یک خط)</span><textarea data-name="options_text" rows="3" class="rounded-xl border border-slate-200 bg-white px-3 py-3"></textarea></label>
                        <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">پاسخ صحیح</span><input data-name="correct_answer" class="rounded-xl border border-slate-200 bg-white px-3 py-3"></label>
                    </div>
                    <button type="button" data-exam-remove-question class="mt-3 text-xs font-bold text-rose-600">حذف سؤال</button>
                </div>
            </template>
        </section>

        <div class="flex gap-2"><a href="{{ route('teacher.exams.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">انصراف</a><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ساخت آزمون</button></div>
    </form>
</div>
@endsection