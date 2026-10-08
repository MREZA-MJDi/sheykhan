@extends('layouts.owner')
@section('title','مدیریت مقاله | شیخان')
@section('header-title','مدیریت مقاله')
@section('content')
<div class="owner-form-shell"><div class="owner-form-head"><div><span>مجله شیخان</span><h1>{{ $post->exists?'ویرایش مقاله':'ساخت مقاله' }}</h1><p>بعد از انتشار، صفحه عمومی مقاله مستقیماً از دیتابیس خوانده می‌شود.</p></div><a href="{{ route('owner.blog.index') }}">بازگشت</a></div>
<form enctype="multipart/form-data" method="POST" action="{{ $post->exists?route('owner.blog.update',$post):route('owner.blog.store') }}" class="owner-form-grid">@csrf @if($post->exists)@method('PATCH')@endif
<section class="owner-form-section"><h2>محتوا</h2><label><span>عنوان</span><input name="title" value="{{ old('title',$post->title) }}" required></label><label><span>شناسه URL</span><input name="slug" value="{{ old('slug',$post->slug) }}"></label><label><span>دسته</span><select name="category_id"><option value="">بدون دسته</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$post->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></label><label><span>خلاصه</span><textarea name="excerpt" rows="3" maxlength="500">{{ old('excerpt',$post->excerpt) }}</textarea></label><label><span>متن مقاله</span><textarea name="content" rows="18" required>{{ old('content',$post->content) }}</textarea></label>
<section class="owner-cover-editor" data-owner-cover-editor>
    <div class="owner-cover-preview">
        @php($cover = $post->media->first(fn($media) => $media->pivot?->collection === 'cover')?->url())
        @if($cover)
            <img src="{{ $cover }}" alt="{{ $post->title }}" data-owner-cover-preview>
        @else
            <div data-owner-cover-empty>تصویر شاخص انتخاب نشده است.</div>
        @endif
    </div>
    <label><span>تصویر شاخص</span><input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-owner-cover-input><small>حداکثر ۱۰ مگابایت.</small></label>
</section></section>
<aside class="owner-form-side"><section class="owner-form-section"><h2>انتشار</h2><label><span>وضعیت</span><select name="status"><option value="draft" @selected(old('status',$post->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$post->status)==='published')>منتشرشده</option></select></label><label><span>زمان انتشار</span><input type="datetime-local" name="published_at" value="{{ old('published_at',$post->published_at?->format('Y-m-d\TH:i')) }}"></label></section><button class="owner-primary-btn w-full" type="submit">{{ $post->exists?'ذخیره تغییرات':'ساخت مقاله' }}</button></aside>
</form></div>
@endsection
