@extends('layouts.owner')
@section('title', 'کتابخانه و جزوه‌ها | شیخان')
@section('header-title', 'کتابخانه و جزوه‌ها')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div>
            <span>فضای اختصاصی آموزشگاه</span>
            <h1>انتشار جزوه و فایل آموزشی</h1>
            <p>فایل‌ها خصوصی ذخیره می‌شوند؛ دسترسی دانش‌آموز بر اساس عضویت فعال و ثبت‌نام در دوره یا کلاس کنترل می‌شود.</p>
        </div>
        <a href="{{ route('owner.people.index', $academy) }}">مدیریت اعضا</a>
    </header>

    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('owner.resources.store', $academy) }}" class="owner-form-grid">
        @csrf
        <section class="owner-form-section">
            <h2>فایل جدید</h2>
            <label><span>عنوان</span><input name="title" value="{{ old('title') }}" maxlength="255" required></label>
            <label><span>توضیح کوتاه</span><textarea name="description" rows="3" maxlength="2000">{{ old('description') }}</textarea></label>
            <label><span>فایل</span><input type="file" name="file" required accept=".pdf,.mp4,.webm,.mov,.mp3,.m4a,.jpg,.jpeg,.png,.webp,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip"><small>حداکثر ۲۰۰ مگابایت. PDF، ویدئو، صوت، تصویر و فایل‌های اداری پذیرفته می‌شوند.</small></label>
            <div class="owner-form-two">
                <label><span>دوره (اختیاری)</span><select name="course_id"><option value="">همهٔ دانش‌آموزان مجاز آموزشگاه</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((string)old('course_id')===(string)$course->id)>{{ $course->title }}</option>@endforeach</select></label>
                <label><span>کلاس (اختیاری)</span><select name="classroom_id"><option value="">بدون محدودیت کلاس</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected((string)old('classroom_id')===(string)$classroom->id)>{{ $classroom->title }}</option>@endforeach</select></label>
            </div>
            <label><span>درس مشخص (اختیاری)</span><select name="lesson_id"><option value="">بدون محدودیت درس</option>@foreach($lessons as $lesson)<option value="{{ $lesson->id }}" @selected((string)old('lesson_id')===(string)$lesson->id)>{{ $lesson->section?->course?->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
        </section>
        <aside class="owner-form-side">
            <section class="owner-form-section">
                <h2>دسترسی</h2>
                <label><span>زمان انتشار (اختیاری)</span><input type="datetime-local" name="release_at" value="{{ old('release_at') }}"><small>قبل از این زمان دانش‌آموز فایل را نمی‌بیند.</small></label>
                <label class="owner-check"><input type="checkbox" name="downloadable" value="1" @checked(old('downloadable'))><span>دانلود این فایل مجاز است</span></label>
                <p class="text-xs leading-6 text-slate-500">در صورت غیرفعال بودن دانلود، فایل فقط از مسیر مشاهدهٔ احرازشده ارائه می‌شود. برای PDFهای فروشی از این ابزار استفاده نکنید.</p>
            </section>
            <button type="submit" class="owner-primary-btn w-full">ثبت فایل خصوصی</button>
        </aside>
    </form>

    <section class="owner-form-section">
        <h2>فایل‌های ثبت‌شده</h2>
        <div class="space-y-3">
            @forelse($resources as $resource)
                <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                    <div class="min-w-0">
                        <strong class="block">{{ $resource->title }}</strong>
                        <p class="mt-1 text-xs text-slate-500">{{ $resource->course?->title ?? 'آموزشگاه' }} @if($resource->classroom) · {{ $resource->classroom->title }} @endif @if($resource->lesson) · {{ $resource->lesson->title }} @endif</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $resource->media?->original_name }} · {{ $resource->downloadable ? 'دانلود مجاز' : 'فقط مشاهده' }} · {{ $resource->release_at?->format('Y-m-d H:i') ?? 'بدون تأخیر انتشار' }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">{{ $resource->status }}</span>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">هنوز فایلی ثبت نشده است.</p>
            @endforelse
        </div>
        @if($resources->hasPages())<div class="mt-5">{{ $resources->links() }}</div>@endif
    </section>
</div>
@endsection
