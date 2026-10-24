@extends('layouts.owner')
@section('title', 'ویرایش منبع آموزشی | شیخان')
@section('header-title', 'ویرایش منبع آموزشی')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div><span>کتابخانه داخلی</span><h1>ویرایش: {{ $resource->title }}</h1><p>تغییر عنوان، ارتباط درسی، زمان انتشار یا فایل بدون بازکردن دسترسی عمومی انجام می‌شود.</p></div>
        <a href="{{ route('owner.resources.index', $academy) }}">بازگشت به منابع</a>
    </header>
    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('owner.resources.update', [$academy, $resource]) }}" class="owner-form-grid">
        @csrf @method('PATCH')
        <section class="owner-form-section space-y-4">
            <h2>مشخصات منبع</h2>
            <label><span>عنوان</span><input name="title" value="{{ old('title', $resource->title) }}" maxlength="255" required></label>
            <label><span>توضیح</span><textarea name="description" maxlength="255" rows="4">{{ old('description', $resource->description) }}</textarea><small class="owner-field-help">حداکثر ۲۵۵ نویسه تا با ساختار فعلی پایگاه داده سازگار بماند.</small></label>
            <div class="owner-form-two">
                <label><span>دوره</span><select name="course_id"><option value="">سطح آموزشگاه</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((string)old('course_id', $resource->course_id)===(string)$course->id)>{{ $course->title }}</option>@endforeach</select></label>
                <label><span>کلاس</span><select name="classroom_id"><option value="">بدون محدودیت کلاس</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected((string)old('classroom_id', $resource->classroom_id)===(string)$classroom->id)>{{ $classroom->title }}</option>@endforeach</select></label>
            </div>
            <label><span>درس</span><select name="lesson_id"><option value="">بدون محدودیت درس</option>@foreach($lessons as $lesson)<option value="{{ $lesson->id }}" @selected((string)old('lesson_id', $resource->lesson_id)===(string)$lesson->id)>{{ $lesson->section?->course?->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
        </section>
        <aside class="owner-form-side space-y-4">
            <section class="owner-form-section space-y-4">
                <h2>فایل و انتشار</h2>
                <p class="text-sm text-slate-600">فایل فعلی: {{ $resource->media?->original_name ?? 'در دسترس نیست' }}</p>
                <p class="text-xs text-slate-500">نوع: {{ $resource->media?->mime_type ?? '—' }} · وضعیت: {{ $resource->status }}</p>
                <label><span>جایگزینی فایل (اختیاری)</span><input type="file" name="file" accept=".pdf,.mp4,.webm,.mov,.mp3,.m4a,.jpg,.jpeg,.png,.webp,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip"><small class="owner-field-help">حداکثر ۲۰۰ مگابایت. فایل جدید خصوصی ذخیره می‌شود؛ در صورت خالی‌بودن، فایل قبلی حفظ می‌شود.</small></label>
                <label><span>زمان انتشار</span><input type="datetime-local" name="release_at" value="{{ old('release_at', $resource->release_at?->format('Y-m-d\TH:i')) }}"><small class="owner-field-help">پیش از این زمان فایل برای دانش‌آموزان قابل مشاهده نیست؛ خالی یعنی انتشار بدون تأخیر.</small></label>
                <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-6 text-amber-900">منابع آموزشی خصوصی‌اند و دسترسی از کنترل ثبت‌نام و عضویت می‌گذرد. فایل‌ها URL عمومی نمی‌گیرند.</p>
            </section>
            <button type="submit" class="owner-primary-btn w-full">ذخیره تغییرات</button>
        </aside>
    </form>
</div>
@endsection