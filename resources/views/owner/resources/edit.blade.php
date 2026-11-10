@extends(request()->boolean('modal') ? 'layouts.owner-modal' : 'layouts.owner')
@section('title', 'ویرایش منبع آموزشی | شیخان')
@section('header-title', 'ویرایش منبع آموزشی')
@section('content')
@php($modalMode = request()->boolean('modal'))
<div class="owner-form-shell space-y-6">
    @unless($modalMode)
<header class="owner-form-head">
        <div><span>کتابخانه داخلی</span><h1>ویرایش: {{ $resource->title }}</h1><p>تغییر عنوان، ارتباط درسی، زمان انتشار یا فایل بدون بازکردن دسترسی عمومی انجام می‌شود.</p></div>
        <a href="{{ route('owner.resources.index', $academy) }}">بازگشت به منابع</a>
    </header>
@endunless
    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('owner.resources.update', [$academy, $resource]) . ($modalMode ? '?modal=1' : '') }}" class="owner-form-grid">
        @csrf @method('PATCH')
        <section class="owner-form-section space-y-4">
            <h2>مشخصات منبع</h2>
            <label><span>عنوان</span><input name="title" value="{{ old('title', $resource->title) }}" maxlength="255" required></label>
            <label><span>توضیح</span><textarea name="description" maxlength="255" rows="4">{{ old('description', $resource->description) }}</textarea><small class="owner-field-help">حداکثر ۲۵۵ نویسه تا با ساختار فعلی پایگاه داده سازگار بماند.</small></label>
            <div class="owner-form-two">
                <div class="grid gap-2" data-academy-option-picker data-type="resource-courses" data-endpoint="{{ route('owner.form-options', $academy) }}">
                    <label><span>دوره</span><input type="search" data-academy-option-search minlength="2" maxlength="80" placeholder="نام دوره را جست‌وجو کن…" class="w-full"><select name="course_id" data-academy-option-select><option value="" data-option-placeholder>سطح آموزشگاه</option><option value="{{ old('course_id', $resource->course_id) }}" selected>{{ old('course_id') ? 'دورهٔ انتخاب‌شده از فرم قبلی' : ($resource->course?->title ?? 'دوره فعلی') }}</option></select></label>
                    <p data-academy-option-status class="owner-field-help">برای تغییر دوره، نام آن را جست‌وجو کن.</p><button type="button" data-academy-option-more hidden class="text-xs font-bold">نمایش ۲۰ گزینهٔ بعدی</button>
                </div>
                <div class="grid gap-2" data-academy-option-picker data-type="resource-classrooms" data-depends-on-course="true" data-endpoint="{{ route('owner.form-options', $academy) }}">
                    <label><span>کلاس</span><input type="search" data-academy-option-search minlength="2" maxlength="80" placeholder="نام کلاس را جست‌وجو کن…" class="w-full"><select name="classroom_id" data-academy-option-select><option value="" data-option-placeholder>بدون محدودیت کلاس</option>@if($resource->classroom)<option value="{{ old('classroom_id', $resource->classroom_id) }}" selected>{{ old('classroom_id') ? 'کلاس انتخاب‌شده از فرم قبلی' : $resource->classroom->title }}</option>@endif</select></label>
                    <p data-academy-option-status class="owner-field-help">کلاس‌های دورهٔ انتخاب‌شده را جست‌وجو کن.</p><button type="button" data-academy-option-more hidden class="text-xs font-bold">نمایش ۲۰ گزینهٔ بعدی</button>
                </div>
            </div>
            <div class="grid gap-2" data-academy-option-picker data-type="resource-lessons" data-depends-on-course="true" data-endpoint="{{ route('owner.form-options', $academy) }}">
                <label><span>درس</span><input type="search" data-academy-option-search minlength="2" maxlength="80" placeholder="نام درس را جست‌وجو کن…" class="w-full"><select name="lesson_id" data-academy-option-select><option value="" data-option-placeholder>بدون محدودیت درس</option>@if($resource->lesson)<option value="{{ old('lesson_id', $resource->lesson_id) }}" selected>{{ old('lesson_id') ? 'درس انتخاب‌شده از فرم قبلی' : $resource->lesson->title }}</option>@endif</select></label>
                <p data-academy-option-status class="owner-field-help">درس‌های دورهٔ انتخاب‌شده را جست‌وجو کن.</p><button type="button" data-academy-option-more hidden class="text-xs font-bold">نمایش ۲۰ گزینهٔ بعدی</button>
            </div>
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