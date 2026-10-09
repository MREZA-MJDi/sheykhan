@extends('layouts.owner')

@section('title','ویرایش SEO | شیخان')
@section('header-title','ویرایش SEO')

@section('content')
<div class="owner-form-shell">
    <div class="owner-form-head">
        <div>
            <span>کنترل دیده‌شدن</span>
            <h1>{{ $item->getAttribute('title') ?: 'صفحه' }}</h1>
            <p>این تنظیمات در HTML عمومی صفحه قرار می‌گیرند. مقدارهای خالی از metadata پیش‌فرض صفحه استفاده می‌کنند.</p>
        </div>
        <a href="{{ route('owner.seo.index') }}">بازگشت به SEO</a>
    </div>

    <form method="POST" action="{{ route('owner.seo.update', [$type, $item->getKey()]) }}" class="owner-form-grid">
        @csrf
        @method('PATCH')

        <section class="owner-form-section">
            <h2>هویت جستجو</h2>

            <label>
                <span>عنوان SEO</span>
                <input name="title" value="{{ old('title', $seoMeta?->title) }}" maxlength="255">
                <small>عنوانی که در عنوان نتایج جستجو و تب مرورگر استفاده می‌شود.</small>
            </label>

            <label>
                <span>توضیحات SEO</span>
                <textarea name="description" rows="4" maxlength="500">{{ old('description', $seoMeta?->description) }}</textarea>
            </label>

            <label>
                <span>کلمات کلیدی</span>
                <textarea name="keywords" rows="3" maxlength="1500">{{ old('keywords', $seoMeta?->keywords) }}</textarea>
            </label>

            <label>
                <span>Canonical</span>
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $seoMeta?->canonical_url) }}" maxlength="2048">
            </label>

            <label>
                <span>Robots</span>
                <select name="robots">
                    @foreach(['index,follow','noindex,follow','index,nofollow','noindex,nofollow'] as $robots)
                        <option value="{{ $robots }}" @selected(old('robots', $seoMeta?->robots ?: 'index,follow') === $robots)>{{ $robots }}</option>
                    @endforeach
                </select>
            </label>
        </section>

        <aside class="owner-form-side">
            <section class="owner-form-section">
                <h2>اشتراک‌گذاری</h2>

                <label>
                    <span>OG Title</span>
                    <input name="og_title" value="{{ old('og_title', $seoMeta?->og_title) }}" maxlength="255">
                </label>

                <label>
                    <span>OG Description</span>
                    <textarea name="og_description" rows="4" maxlength="500">{{ old('og_description', $seoMeta?->og_description) }}</textarea>
                </label>

                <label>
                    <span>OG Image URL</span>
                    <input type="url" name="og_image_url" value="{{ old('og_image_url', $seoMeta?->og_image_url) }}" maxlength="2048">
                </label>

                <label>
                    <span>Schema JSON</span>
                    <textarea name="schema_json" rows="11" maxlength="20000" dir="ltr" spellcheck="false">{{ old('schema_json', $seoMeta?->schema_json ? json_encode($seoMeta->schema_json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) : '') }}</textarea>
                    <small>اختیاری؛ JSON معتبر برای داده ساختاریافته.</small>
                </label>
            </section>

            <button type="submit" class="owner-primary-btn w-full">ذخیره تنظیمات SEO</button>
        </aside>
    </form>
</div>
@endsection
