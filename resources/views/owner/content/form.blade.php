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

    <form method="POST" action="{{ $content->exists ? route('owner.content.update', $content) : route('owner.content.store') }}" class="owner-form-grid">
        @csrf
        @if($content->exists) @method('PATCH') @endif
        <section class="owner-form-section">
            <h2>اطلاعات اصلی</h2>
            <label><span>آموزشگاه</span><select name="academy_id" required>{{-- Scoped again server-side --}}@foreach($academies as $academy)<option value="{{ $academy->id }}" @selected(old('academy_id',$content->academy_id)==$academy->id)>{{ $academy->name }}</option>@endforeach</select></label>
            <label><span>دسته</span><select name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$content->category_id)==$category->id)>{{ $category->title }}</option>@endforeach</select></label>
            <div class="owner-form-two"><label><span>نوع</span><select name="type"><option value="article" @selected(old('type',$content->type)==='article')>مقاله</option><option value="video" @selected(old('type',$content->type)==='video')>ویدئو</option></select></label><label><span>وضعیت</span><select name="status"><option value="draft" @selected(old('status',$content->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$content->status)==='published')>منتشرشده</option><option value="archived" @selected(old('status',$content->status)==='archived')>آرشیو</option></select></label></div>
            <label><span>عنوان</span><input name="title" value="{{ old('title',$content->title) }}" required maxlength="255"></label>
            <label><span>شناسه URL</span><input name="slug" value="{{ old('slug',$content->slug) }}" maxlength="255"></label>
            <label><span>خلاصه</span><textarea name="excerpt" rows="3" maxlength="500">{{ old('excerpt',$content->excerpt) }}</textarea></label>
            <label><span>متن محتوا</span><textarea name="body" rows="14">{{ old('body',$content->body) }}</textarea></label>
        </section>
        <aside class="owner-form-side">
            <section class="owner-form-section">
                <h2>انتشار</h2>
                <label><span>تاریخ انتشار</span><input type="datetime-local" name="published_at" value="{{ old('published_at',$content->published_at?->format('Y-m-d\TH:i')) }}"></label>
                <label><span>مدت ویدئو (ثانیه)</span><input type="number" name="video_duration_seconds" value="{{ old('video_duration_seconds',$content->video_duration_seconds) }}" min="1"></label>
                <label><span>اولویت نمایش</span><input type="number" name="sort_order" value="{{ old('sort_order',$content->sort_order ?? 0) }}" min="0"></label>
                <label class="owner-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$content->is_featured))><span>محتوای منتخب</span></label>
            </section>
            <button type="submit" class="owner-primary-btn w-full">{{ $content->exists ? 'ذخیره تغییرات' : 'ساخت محتوا' }}</button>
        </aside>
    </form>
</div>
@endsection
