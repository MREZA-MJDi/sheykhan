@extends('layouts.teacher')
@section('title','محتوای دوره | شیخان')
@section('header-title','محتوای دوره')
@section('content')
<div class="grid gap-5">
    <div class="dashboard-panel p-5 sm:p-7"><div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"><div><span class="text-xs font-black text-[var(--panel-primary)]">محتوای آموزشی</span><h2 class="mt-1 text-2xl font-black">{{ $course->title }}</h2><p class="mt-2 text-sm text-slate-500">{{ $course->sections->count() }} سرفصل</p></div><a href="{{ route('teacher.courses.progress',$course) }}" class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">مشاهده پیشرفت دانش‌آموزان</a></div></div>
    @foreach($course->sections as $section)
        <section class="dashboard-panel p-5">
            <h3 class="text-base font-black">{{ $section->title }}</h3>
            <div class="mt-4 grid gap-3">
                @forelse($section->lessons as $lesson)
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <strong class="text-sm">{{ $lesson->title }}</strong>
                                <p class="mt-1 text-[10px] text-slate-500">{{ $lesson->type }} · {{ $lesson->status }} · {{ $lesson->duration_seconds }} ثانیه</p>
                            </div>
                            <div class="flex flex-wrap gap-2 items-center">
                                @if($lesson->media->isNotEmpty())
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-[9px] text-emerald-700">{{ $lesson->media->count() }} فایل</span>
                                @endif
                                <form method="POST" action="{{ route('teacher.lessons.media.store',$lesson) }}" enctype="multipart/form-data" class="flex flex-wrap gap-2">
                                    @csrf
                                    <input type="file" name="media" class="max-w-[220px] rounded-lg border border-slate-200 bg-white px-2 py-2 text-[9px]">
                                    <button class="rounded-lg bg-slate-900 px-3 py-2 text-[9px] font-black text-white">آپلود فایل</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-slate-50 p-5 text-center text-xs text-slate-500">در این سرفصل هنوز درسی وجود ندارد.</div>
                @endforelse
            </div>
        </section>
    @endforeach

    <section class="dashboard-panel p-5 sm:p-7">
        <div><h3 class="text-base font-black">افزودن درس</h3><p class="mt-1 text-xs text-slate-500">درس به یکی از سرفصل‌های موجود اضافه می‌شود.</p></div>
        <form method="POST" action="{{ route('teacher.lessons.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            <label class="grid gap-2"><span class="text-xs font-bold">سرفصل</span><select name="course_section_id" class="rounded-xl border border-slate-200 px-3 py-3">@foreach($course->sections as $section)<option value="{{ $section->id }}">{{ $section->title }}</option>@endforeach</select></label>
            <label class="grid gap-2"><span class="text-xs font-bold">نوع</span><select name="type" class="rounded-xl border border-slate-200 px-3 py-3"><option value="video">ویدیو</option><option value="text">متن</option><option value="file">فایل</option><option value="quiz">کوئیز</option><option value="live">زنده</option></select></label>
            <label class="grid gap-2"><span class="text-xs font-bold">عنوان</span><input name="title" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <label class="grid gap-2"><span class="text-xs font-bold">Slug</span><input name="slug" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">خلاصه</span><input name="summary" class="rounded-xl border border-slate-200 px-3 py-3"></label>
            <label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">محتوا</span><textarea name="content" rows="6" class="rounded-xl border border-slate-200 px-3 py-3"></textarea></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_free" value="1"><span class="text-xs font-bold">این درس رایگان باشد</span></label>
            <select name="status" class="rounded-xl border border-slate-200 px-3 py-3"><option value="draft">پیش‌نویس</option><option value="published">منتشرشده</option></select>
            <button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white sm:col-span-2">افزودن درس</button>
        </form>
        <div class="mt-6 rounded-2xl border border-dashed border-slate-300 p-5">
            <div class="text-sm font-black">آپلود فایل درس</div>
            <p class="mt-1 text-xs text-slate-500">پس از ساخت درس، از مسیر فایل‌های خصوصی همان درس استفاده کن.</p>
        </div>
    </section>
</div>
@endsection