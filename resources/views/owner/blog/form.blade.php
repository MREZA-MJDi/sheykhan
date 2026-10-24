@extends('layouts.owner')
@section('title','مدیریت مقاله | شیخان')
@section('header-title','مدیریت مقاله')
@section('content')
<div class="owner-form-shell"><div class="owner-form-head"><div><span>مجله شیخان</span><h1>{{ $post->exists?'ویرایش مقاله':'ساخت مقاله' }}</h1><p>بعد از انتشار، صفحه عمومی مقاله مستقیماً از دیتابیس خوانده می‌شود.</p></div><a href="{{ route('owner.blog.index') }}">بازگشت</a></div>
<form enctype="multipart/form-data" method="POST" action="{{ $post->exists?route('owner.blog.update',$post):route('owner.blog.store') }}" class="owner-form-grid">@csrf @if($post->exists)@method('PATCH')@endif
<section class="owner-form-section"><h2>محتوا</h2><label><span>عنوان</span><input name="title" value="{{ old('title',$post->title) }}" required><small class="owner-field-help">عنوانی کوتاه و روشن انتخاب کن؛ همین متن در تیتر صفحه و کارت مقاله استفاده می‌شود.</small></label><label><span>شناسه URL</span><input name="slug" value="{{ old('slug',$post->slug) }}"><small class="owner-field-help">شناسهٔ نشانی عمومی مقاله است؛ کوتاه، خوانا و بدون فاصله واردش کن.</small></label><label><span>دسته</span><select name="category_id"><option value="">بدون دسته</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$post->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select><small class="owner-field-help">دسته کمک می‌کند مقاله در بخش درست مجله پیدا شود؛ در صورت نیاز «بدون دسته» را انتخاب کن.</small></label><label><span>خلاصه</span><textarea name="excerpt" rows="3" maxlength="500">{{ old('excerpt',$post->excerpt) }}</textarea><small class="owner-field-help">خلاصهٔ مقاله برای معرفی در فهرست‌هاست؛ نکتهٔ اصلی را در حداکثر ۵۰۰ نویسه بگو.</small></label><label><span>متن مقاله</span><textarea name="content" rows="18" required>{{ old('content',$post->content) }}</textarea><small class="owner-field-help">مقاله را با تیترهای مشخص و پاراگراف‌های کوتاه بنویس تا روی موبایل هم خوانا بماند.</small></label>
<section class="owner-cover-editor" data-owner-cover-editor data-preview-ratio="1.6" style="--owner-cover-ratio: 16 / 10;">
    <div class="owner-cover-preview">
        @php($cover = $post->media->first(fn($media) => $media->pivot?->collection === 'cover')?->url())
        @if($cover)
            <img src="{{ $cover }}" alt="{{ $post->title }}" data-owner-cover-preview>
        @else
            <div data-owner-cover-empty>تصویر شاخص انتخاب نشده است.</div>
        @endif
    </div>
    <label><span>تصویر شاخص</span><input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-owner-cover-input data-max-size-mb="10" data-allowed-extensions="jpg,jpeg,png,webp" data-recommended-width="1200" data-recommended-height="750" aria-describedby="owner-blog-cover-help owner-blog-cover-feedback"><small id="owner-blog-cover-help">حداکثر ۱۰ مگابایت · کادر مقاله ۱۶:۱۰ · پیشنهاد ۱۲۰۰×۷۵۰ پیکسل.</small><span id="owner-blog-cover-feedback" class="owner-image-feedback" data-owner-cover-feedback role="status" aria-live="polite">این تصویر در کارت مقاله با نسبت ۱۶:۱۰ و به‌صورت cover نمایش داده می‌شود؛ فایل اصلی تغییر نمی‌کند.</span></label>
</section></section>
<aside class="owner-form-side"><section class="owner-form-section"><h2>انتشار</h2><label><span>وضعیت</span><select name="status"><option value="draft" @selected(old('status',$post->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$post->status)==='published')>منتشرشده</option></select><small class="owner-field-help">پیش‌نویس عمومی نیست؛ فقط وضعیت منتشرشده مقاله را در سایت نمایش می‌دهد.</small></label><label><span>زمان انتشار</span><input type="datetime-local" name="published_at" value="{{ old('published_at',$post->published_at?->format('Y-m-d\TH:i')) }}"><small class="owner-field-help">برای زمان‌بندی انتشار پر کن؛ تاریخ انتشار باید با برنامهٔ انتشار مقاله هماهنگ باشد.</small></label></section><button class="owner-primary-btn w-full" type="submit">{{ $post->exists?'ذخیره تغییرات':'ساخت مقاله' }}</button></aside>
</form></div>
@endsection
