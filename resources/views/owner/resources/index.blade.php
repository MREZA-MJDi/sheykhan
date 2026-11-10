@extends('layouts.owner')
@section('title', 'کتابخانه و جزوه‌ها | شیخان')
@section('header-title', 'کتابخانه و جزوه‌ها')
@section('content')
<div class="owner-form-shell space-y-6">
    @include('owner.partials.learning-workspace-tabs', ['academy' => $academy, 'activeSheet' => 'resources'])
    <header class="owner-form-head">
        <div>
            <span>فضای اختصاصی آموزشگاه</span>
            <h1>انتشار جزوه و فایل آموزشی</h1>
            <p>فایل‌ها خصوصی ذخیره می‌شوند؛ دسترسی دانش‌آموز بر اساس عضویت فعال و ثبت‌نام در دوره یا کلاس کنترل می‌شود.</p>
        </div>
        <a href="{{ route('owner.people.index', $academy) }}">مدیریت اعضا</a>
    </header>
    <div class="owner-page-actions">
        <button type="button" class="owner-modal-trigger owner-modal-trigger--primary" data-owner-modal-open="owner-resource-create-modal">＋ افزودن منبع آموزشی</button>
    </div>

    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <dialog class="owner-floating-modal" id="owner-resource-create-modal" aria-labelledby="owner-resource-create-title" @if($errors->any() && old('form_modal') === 'resource') data-owner-modal-open-on-load @endif>
        <header class="owner-floating-modal__header">
            <div><span class="owner-record-modal-kicker">کتابخانهٔ آموزشگاه</span><h2 id="owner-resource-create-title">افزودن منبع جدید</h2></div>
            <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
        </header>
        <div class="owner-floating-modal__body">
<form method="POST" enctype="multipart/form-data" action="{{ route('owner.resources.store', $academy) }}?modal=1" class="owner-form-grid">
        @csrf
        <input type="hidden" name="form_modal" value="resource">
        <section class="owner-form-section">
            <h2>فایل جدید</h2>
            <label><span>عنوان</span><input name="title" value="{{ old('title') }}" maxlength="255" required><small class="owner-field-help">عنوانی مشخص انتخاب کن تا عضو آموزشگاه فایل را از فهرست منابع تشخیص بدهد.</small></label>
            <label><span>توضیح کوتاه</span><textarea name="description" rows="3" maxlength="2000">{{ old('description') }}</textarea><small class="owner-field-help">خلاصهٔ کاربرد فایل، درس مرتبط و نکتهٔ مهم استفاده را بنویس.</small></label>
            <label>
                <span>فایل</span>
                <input type="file" name="file" required accept=".pdf,.mp4,.webm,.mov,.mp3,.m4a,.jpg,.jpeg,.png,.webp,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip" data-owner-resource-input aria-describedby="owner-resource-file-help owner-resource-file-feedback">
                <small id="owner-resource-file-help">حداکثر ۲۰۰ مگابایت. PDF، ویدئو، صوت، تصویر و فایل‌های اداری پذیرفته می‌شوند.</small>
            </label>
            <div class="owner-resource-preview" data-owner-resource-preview hidden>
                <div class="owner-resource-preview-media">
                    <img data-owner-resource-image alt="" hidden>
                    <video data-owner-resource-video controls preload="metadata" hidden></video>
                    <audio data-owner-resource-audio controls preload="metadata" hidden></audio>
                    <div data-owner-resource-generic>پیش‌نمایش برای این نوع فایل در دسترس نیست.</div>
                </div>
                <div class="owner-resource-preview-copy">
                    <strong data-owner-resource-name></strong>
                    <span data-owner-resource-size></span>
                    <span data-owner-resource-dimensions></span>
                </div>
            </div>
            <p id="owner-resource-file-feedback" class="owner-image-feedback" data-owner-resource-feedback role="status" aria-live="polite">این بخش فایل خصوصی برای اعضای مجاز آموزشگاه می‌سازد؛ تصویر اینجا به بنر یا کاور عمومی سایت تبدیل نمی‌شود.</p>
            <div class="owner-form-two">
                <div class="grid gap-2" data-academy-option-picker data-type="resource-courses" data-endpoint="{{ route('owner.form-options', $academy) }}">
                    <label><span>دوره (اختیاری)</span><input type="search" data-academy-option-search minlength="2" maxlength="80" placeholder="نام دوره را جست‌وجو کن…" class="w-full"><select name="course_id" data-academy-option-select><option value="" data-option-placeholder>همهٔ دانش‌آموزان مجاز آموزشگاه</option>@if(old('course_id'))<option value="{{ old('course_id') }}" selected>دورهٔ انتخاب‌شده از فرم قبلی</option>@endif</select></label>
                    <p data-academy-option-status class="owner-field-help">برای انتخاب، دست‌کم دو حرف از نام دوره را جست‌وجو کن.</p>
                    <button type="button" data-academy-option-more hidden class="text-xs font-bold">نمایش ۲۰ گزینهٔ بعدی</button>
                    <small class="owner-field-help">با انتخاب دوره، دسترسی به دانش‌آموزان مجاز همان دوره محدود می‌شود؛ برای دسترسی در سطح آموزشگاه خالی بگذار.</small>
                </div>
                <div class="grid gap-2" data-academy-option-picker data-type="resource-classrooms" data-depends-on-course="true" data-endpoint="{{ route('owner.form-options', $academy) }}">
                    <label><span>کلاس (اختیاری)</span><input type="search" data-academy-option-search minlength="2" maxlength="80" placeholder="نام کلاس را جست‌وجو کن…" class="w-full"><select name="classroom_id" data-academy-option-select><option value="" data-option-placeholder>بدون محدودیت کلاس</option></select></label>
                    <p data-academy-option-status class="owner-field-help">ابتدا دوره را انتخاب کن؛ سپس نام کلاس را جست‌وجو کن.</p>
                    <button type="button" data-academy-option-more hidden class="text-xs font-bold">نمایش ۲۰ گزینهٔ بعدی</button>
                    <small class="owner-field-help">در صورت انتخاب کلاس، دسترسی به همان گروه محدودتر می‌شود.</small>
                </div>
            </div>
            <div class="grid gap-2" data-academy-option-picker data-type="resource-lessons" data-depends-on-course="true" data-endpoint="{{ route('owner.form-options', $academy) }}">
                <label><span>درس مشخص (اختیاری)</span><input type="search" data-academy-option-search minlength="2" maxlength="80" placeholder="نام درس را جست‌وجو کن…" class="w-full"><select name="lesson_id" data-academy-option-select><option value="" data-option-placeholder>بدون محدودیت درس</option></select></label>
                <p data-academy-option-status class="owner-field-help">ابتدا دوره را انتخاب کن؛ سپس نام درس را جست‌وجو کن.</p>
                <button type="button" data-academy-option-more hidden class="text-xs font-bold">نمایش ۲۰ گزینهٔ بعدی</button>
                <small class="owner-field-help">این فایل را به درس مشخص وصل کن تا در زمینهٔ همان درس نمایش داده شود.</small>
            </div>
        </section>
        <aside class="owner-form-side">
            <section class="owner-form-section">
                <h2>دسترسی</h2>
                <label><span>زمان انتشار (اختیاری)</span><input type="datetime-local" name="release_at" value="{{ old('release_at') }}"><small>قبل از این زمان دانش‌آموز فایل را نمی‌بیند.</small><small class="owner-field-help">تا پیش از زمان تعیین‌شده فایل به دانش‌آموز نمایش داده نمی‌شود؛ برای انتشار فوری خالی بگذار.</small></label>
                <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-6 text-amber-900">فایل‌های آموزشی دانش‌آموز همیشه از مسیر مشاهدهٔ احرازشده ارائه می‌شوند؛ دانلود مستقیم این بخش عمداً غیرفعال است. PDFهای فروشی را از مدیریت محصولات ثبت کن.</p>
            </section>
            <button type="submit" class="owner-primary-btn w-full">ثبت فایل خصوصی</button>
        </aside>
    </form>
        </div>
    </dialog>

    <section class="owner-form-section">
        <h2>فایل‌های ثبت‌شده</h2>
        <div class="space-y-3">
            @forelse($resources as $resource)
                <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                    <div class="min-w-0">
                        <strong class="block">{{ $resource->title }}</strong>
                        <p class="mt-1 text-xs text-slate-500">{{ $resource->course?->title ?? 'آموزشگاه' }} @if($resource->classroom) · {{ $resource->classroom->title }} @endif @if($resource->lesson) · {{ $resource->lesson->title }} @endif</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $resource->media?->original_name }} · فقط مشاهدهٔ احرازشده · {{ $resource->release_at?->format('Y-m-d H:i') ?? 'بدون تأخیر انتشار' }}</p>
                        <div class="mt-2 flex flex-wrap gap-3">
                            <a class="text-xs font-bold text-[var(--color-primary-600)]" href="{{ route('owner.resources.edit', ['academy' => $academy, 'resource' => $resource, 'modal' => 1]) }}" data-owner-modal-url="{{ route('owner.resources.edit', ['academy' => $academy, 'resource' => $resource, 'modal' => 1]) }}" data-owner-modal-title="ویرایش منبع آموزشی">ویرایش منبع</a>
                            @if($resource->status === 'active')
                                <form method="POST" action="{{ route('owner.resources.archive', [$academy, $resource]) }}" data-confirm-submit="true" data-confirm="این منبع از فهرست فعال دانش‌آموزان خارج می‌شود؛ فایل و سوابق آن حذف نمی‌شوند.">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs font-bold text-rose-700">بایگانی</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('owner.resources.restore', [$academy, $resource]) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs font-bold text-emerald-700">فعال‌سازی دوباره</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">{{ $resource->status }}</span>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">هنوز فایلی ثبت نشده است.</p>
            @endforelse
        </div>
        @if($resources->hasPages())<div class="mt-5">{{ $resources->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
