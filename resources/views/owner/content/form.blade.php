@extends('layouts.owner')
@section('title','ویرایش محتوا | شیخان')
@section('header-title','مدیریت محتوا')
@section('content')
<div class="owner-form-shell">
    <div class="owner-form-head">
        <div>
            <span>مرکز محتوا</span>
            <h1>{{ $content->exists ? 'ویرایش محتوا' : 'ساخت محتوای جدید' }}</h1>
            <p>ساختار داده‌ای محتوا ثابت می‌ماند و با انتشار، صفحه عمومی قابل ایندکس می‌شود.</p>
        </div>
        <a href="{{ route('owner.content.index') }}">بازگشت</a>
    </div>

    <form enctype="multipart/form-data" method="POST" action="{{ $content->exists ? route('owner.content.update', $content) : route('owner.content.store') }}" class="owner-form-grid">
        @csrf
        @if($content->exists) @method('PATCH') @endif
        <section class="owner-form-section">
            <h2>اطلاعات اصلی</h2>
            <label><span>آموزشگاه</span><select name="academy_id" required>{{-- Scoped again server-side --}}@foreach($academies as $academy)<option value="{{ $academy->id }}" @selected(old('academy_id',$content->academy_id)==$academy->id)>{{ $academy->name }}</option>@endforeach</select><small class="owner-field-help">محتوا به این آموزشگاه متصل می‌شود؛ محدودهٔ انتشار را پیش از ذخیره بررسی کن.</small></label>
            <label><span>دسته</span><select name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$content->category_id)==$category->id)>{{ $category->title }}</option>@endforeach</select><small class="owner-field-help">دسته‌ای را انتخاب کن که خواننده انتظار دارد محتوا را در آن پیدا کند.</small></label>
            <div class="owner-form-two"><label><span>نوع</span><select name="type"><option value="article" @selected(old('type',$content->type)==='article')>مقاله</option><option value="video" @selected(old('type',$content->type)==='video')>ویدئو</option></select><small class="owner-field-help">برای نوع ویدئو، فایل واقعی و قابل پخش نیز باید ثبت شود.</small></label><label><span>وضعیت</span><select name="status"><option value="draft" @selected(old('status',$content->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$content->status)==='published')>منتشرشده</option><option value="archived" @selected(old('status',$content->status)==='archived')>آرشیو</option></select><small class="owner-field-help">فقط محتوای منتشرشده در بخش عمومی نمایش داده می‌شود.</small></label></div>
            <label><span>عنوان</span><input name="title" value="{{ old('title',$content->title) }}" required maxlength="255"><small class="owner-field-help">عنوان دقیق و کوتاه انتخاب کن؛ این متن تیتر اصلی صفحهٔ محتواست.</small></label>
            <label><span>شناسه URL</span><input name="slug" value="{{ old('slug',$content->slug) }}" maxlength="255"><small class="owner-field-help">برای نشانی صفحه استفاده می‌شود؛ عبارت کوتاه و خوانا، بدون فاصله وارد کن.</small></label>
            <label><span>خلاصه</span><textarea name="excerpt" rows="3" maxlength="500">{{ old('excerpt',$content->excerpt) }}</textarea><small class="owner-field-help">در چند جمله ارزش اصلی محتوا را توضیح بده؛ حداکثر ۵۰۰ نویسه.</small></label>
            <label><span>متن محتوا</span><textarea name="body" rows="14">{{ old('body',$content->body) }}</textarea><small class="owner-field-help">متن کامل را با تیترهای منظم و پاراگراف‌های کوتاه بنویس تا خواندن آن در موبایل هم راحت باشد.</small></label>
            <label>
                <span>فایل واقعی ویدئو (برای نوع «ویدئو» الزامی)</span>
                <input type="file" name="video_file" accept=".mp4,.webm,.mov,video/mp4,video/webm,video/quicktime">
                <small>حداکثر ۲۰۰ مگابایت. برای انتشار ویدئو، فایل واقعی لازم است؛ متن seed به‌جای ویدئو پذیرفته نیست.</small>
            </label>
            @php($existingVideo = $content->media->first(fn($media) => $media->pivot?->collection === 'video' && $media->collection === 'video' && $media->visibility === 'public' && $media->status === 'active'))
            @if($existingVideo)
                <video controls preload="metadata" class="mt-3 w-full rounded-xl border border-slate-200" src="{{ $existingVideo->url() }}"></video>
            @endif
            <div class="owner-cover-editor" data-owner-cover-editor data-preview-ratio="1.6" style="--owner-cover-ratio: 16 / 10;">
                <div class="owner-cover-preview">
                    @php($cover = $content->media->first(fn($media) => $media->pivot?->collection === 'cover')?->url())
                    @if($cover)
                        <img src="{{ $cover }}" alt="{{ $content->title }}" data-owner-cover-preview>
                    @else
                        <div data-owner-cover-empty>تصویر شاخص انتخاب نشده است.</div>
                    @endif
                </div>
                <label>
                    <span>تصویر شاخص</span>
                    <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-owner-cover-input data-max-size-mb="10" data-allowed-extensions="jpg,jpeg,png,webp" data-recommended-width="1200" data-recommended-height="750" aria-describedby="owner-content-cover-help owner-content-cover-feedback">
                    <small id="owner-content-cover-help">حداکثر ۱۰ مگابایت · کادر تصویر عمومی ۱۶:۱۰ · پیشنهاد ۱۲۰۰×۷۵۰ پیکسل.</small>
                    <span id="owner-content-cover-feedback" class="owner-image-feedback" data-owner-cover-feedback role="status" aria-live="polite">تصویر در قاب عمومی به‌صورت cover دیده می‌شود؛ فایل اصلی تغییر نمی‌کند.</span>
                </label>
            </div>
        </section>
        <aside class="owner-form-side">
            <section class="owner-form-section">
                <h2>انتشار</h2>
                <label><span>تاریخ انتشار</span><input type="datetime-local" name="published_at" value="{{ old('published_at',$content->published_at?->format('Y-m-d\TH:i')) }}"><small class="owner-field-help">زمان انتشار برای محتوای زمان‌بندی‌شده است؛ در غیر این صورت می‌توانی خالی بگذاری.</small></label>
                <label><span>مدت ویدئو (ثانیه)</span><input type="number" name="video_duration_seconds" value="{{ old('video_duration_seconds',$content->video_duration_seconds) }}" min="1"><small class="owner-field-help">مدت کل فایل ویدئو را به ثانیه وارد کن.</small></label>
                <label><span>اولویت نمایش</span><input type="number" name="sort_order" value="{{ old('sort_order',$content->sort_order ?? 0) }}" min="0"><small class="owner-field-help">عدد کمتر برای نمایش زودتر استفاده می‌شود؛ از صفر شروع کن.</small></label>
                <label class="owner-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$content->is_featured))><span>محتوای منتخب</span></label>
            </section>
            <button type="submit" class="owner-primary-btn w-full">{{ $content->exists ? 'ذخیره تغییرات' : 'ساخت محتوا' }}</button>
        </aside>
    </form>
</div>
@endsection
