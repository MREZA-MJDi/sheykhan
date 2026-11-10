@extends('layouts.seo-specialist')
@section('title','مدیریت مقاله | شیخان')
@section('header-title','بلاگ و مقالات')
@section('content')
<div class="owner-form-shell owner-blog-form-shell">
    <header class="owner-form-head">
        <div>
            <span>مجله شیخان / مدیریت محتوای سایت</span>
            <h1>{{ $post->exists ? 'ویرایش مقاله' : 'ساخت مقاله جدید' }}</h1>
            <p>محتوا، تصویر شاخص و وضعیت انتشار را در یک فرم یکپارچه مدیریت کن.</p>
        </div>
        <a class="course-secondary-btn" href="{{ route('owner.blog.index') }}">بازگشت به مقالات</a>
    </header>

    @if($errors->any())
        <div class="owner-blog-errors" role="alert" tabindex="-1">
            <strong>مقاله ذخیره نشد؛ این موارد را بررسی کن:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form enctype="multipart/form-data" method="POST" action="{{ $post->exists ? route('owner.blog.update',$post) : route('owner.blog.store') }}" class="owner-form-grid">
        @csrf
        @if($post->exists) @method('PATCH') @endif

        <div class="owner-blog-main-fields">
            <section class="owner-form-section">
                <div class="owner-blog-section-heading"><div><h2>محتوای مقاله</h2><p>عنوان و متن اصلی مقاله را وارد کن.</p></div><span>۱ / ۳</span></div>
                <label><span>عنوان مقاله <b>*</b></span><input name="title" value="{{ old('title',$post->title) }}" maxlength="255" required placeholder="مثلاً راهنمای انتخاب کلاس مناسب"><small class="owner-field-help">عنوان واضح و توصیفی برای مخاطب و نتایج جست‌وجو بنویس.</small>@error('title')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
                <label><span>شناسه URL</span><input name="slug" value="{{ old('slug',$post->slug) }}" maxlength="255" dir="ltr" placeholder="article-url"><small class="owner-field-help">در صورت خالی‌بودن، از عنوان ساخته می‌شود.</small>@error('slug')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
                <label><span>دسته‌بندی</span><select name="category_id"><option value="">بدون دسته</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$post->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
                <label><span>خلاصه مقاله</span><textarea name="excerpt" rows="3" maxlength="500" placeholder="چکیده‌ای کوتاه برای کارت و معرفی مقاله...">{{ old('excerpt',$post->excerpt) }}</textarea><small class="owner-field-help">حداکثر ۵۰۰ نویسه؛ نکته اصلی مقاله را خلاصه کن.</small>@error('excerpt')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
                <label><span>متن مقاله <b>*</b></span><textarea name="content" rows="18" required placeholder="متن کامل مقاله...">{{ old('content',$post->content) }}</textarea><small class="owner-field-help">از تیترهای روشن و پاراگراف‌های کوتاه استفاده کن تا خواندن مقاله در موبایل آسان باشد.</small>@error('content')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
            </section>

            <section class="owner-form-section owner-blog-cover-section">
                <div class="owner-blog-section-heading"><div><h2>تصویر شاخص مقاله</h2><p>تصویری که در کارت مقاله و ابتدای صفحه عمومی نمایش داده می‌شود.</p></div><span>۲ / ۳</span></div>
                <div class="owner-cover-editor" data-owner-cover-editor>
                    <div class="owner-cover-preview">
                        @php($cover = $post->media->first(fn($media) => $media->pivot?->collection === 'cover')?->url())
                        @if($cover)
                            <img src="{{ $cover }}" alt="{{ $post->title }}" data-owner-cover-preview>
                        @else
                            <div data-owner-cover-empty>پیش‌نمایش تصویر شاخص</div>
                        @endif
                    </div>
                    <div class="owner-cover-controls">
                        <label><span>انتخاب تصویر</span><input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-owner-cover-input data-max-size-mb="10" data-allowed-extensions="jpg,jpeg,png,webp" aria-describedby="owner-blog-cover-help owner-blog-cover-feedback"><small id="owner-blog-cover-help">JPG، PNG یا WebP · حداکثر ۱۰ مگابایت · نسبت پیشنهادی ۱۶:۱۰ و اندازه ۱۲۰۰×۷۵۰ پیکسل.</small><span id="owner-blog-cover-feedback" class="owner-image-feedback" data-owner-cover-feedback role="status" aria-live="polite">با انتخاب فایل، پیش‌نمایش آن قبل از ذخیره نمایش داده می‌شود. تصویر فعلی تا زمان ذخیره تغییر نمی‌کند.</span>@error('cover_image')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
                        @if($cover)<p class="owner-blog-current-image">تصویر فعلی مقاله نمایش داده شده است؛ برای جایگزینی، تصویر جدید انتخاب کن.</p>@endif
                    </div>
                </div>
            </section>
        </div>

        <aside class="owner-form-side">
            <section class="owner-form-section">
                <div class="owner-blog-section-heading"><div><h2>وضعیت انتشار</h2><p>زمان نمایش مقاله در سایت را مشخص کن.</p></div><span>۳ / ۳</span></div>
                <label><span>وضعیت</span><select name="status"><option value="draft" @selected(old('status',$post->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$post->status)==='published')>منتشرشده</option></select><small class="owner-field-help">پیش‌نویس در سایت عمومی نمایش داده نمی‌شود.</small>@error('status')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
                <label><span>زمان انتشار</span><input type="datetime-local" name="published_at" value="{{ old('published_at',$post->published_at?->format('Y-m-d\TH:i')) }}"><small class="owner-field-help">اگر زمان مشخص نکنی، هنگام انتشار زمان فعلی ثبت می‌شود.</small>@error('published_at')<em class="owner-field-error">{{ $message }}</em>@enderror</label>
            </section>
            <div class="owner-blog-save-panel">
                <button class="owner-primary-btn w-full" type="submit">{{ $post->exists ? 'ذخیره تغییرات' : 'ساخت مقاله' }}</button>
                <a href="{{ route('owner.blog.index') }}">انصراف و بازگشت</a>
            </div>
            @if($post->exists)
                <a class="owner-blog-public-link" href="{{ route('blog.show',$post->slug) }}" target="_blank" rel="noopener">مشاهده صفحه عمومی مقاله ↗</a>
            @endif
        </aside>
    </form>
</div>
@endsection
