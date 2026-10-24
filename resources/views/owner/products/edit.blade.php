@extends('layouts.owner')
@section('title', 'ویرایش محصول | شیخان')
@section('header-title', 'ویرایش محصول')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div><span>کاتالوگ آموزشگاه</span><h1>ویرایش: {{ $product->title }}</h1><p>ذخیره تغییرات، فایل قبلی را بدون نیاز حذف نمی‌کند؛ PDF اصلی خصوصی می‌ماند و نسخه جدید جای نسخه اصلی را می‌گیرد.</p></div>
        <a href="{{ route('owner.products.index', $academy) }}">بازگشت به محصولات</a>
    </header>
    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('owner.products.update', [$academy, $product]) }}" class="owner-form-grid">
        @csrf @method('PATCH')
        <section class="owner-form-section space-y-4">
            <h2>اطلاعات محصول</h2>
            <label><span>عنوان</span><input name="title" value="{{ old('title', $product->title) }}" maxlength="255" required></label>
            <label><span>نامک</span><input name="slug" value="{{ old('slug', $product->slug) }}" maxlength="255" required><small class="owner-field-help">برای تغییر نشانی عمومی محصول استفاده می‌شود.</small></label>
            <label><span>زیرعنوان</span><input name="subtitle" value="{{ old('subtitle', $product->subtitle) }}" maxlength="255"></label>
            <label><span>شرح</span><textarea name="description" rows="5" maxlength="12000">{{ old('description', $product->description) }}</textarea></label>
            <div class="owner-form-two">
                <label><span>دسته</span><select name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id', $product->category_id)===(string)$category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (غیرفعال؛ دسته فعلی)' }}</option>@endforeach</select></label>
                <label><span>نوع محصول</span><select name="product_type" required>
                    @foreach(['book'=>'کتاب','booklet'=>'جزوه','exam'=>'آزمون','digital'=>'محصول دیجیتال','file'=>'فایل آموزشی'] as $value=>$label)
                        <option value="{{ $value }}" @selected(old('product_type', $product->product_type)===$value)>{{ $label }}</option>
                    @endforeach
                </select></label>
            </div>
            <div class="owner-form-two">
                <label><span>قیمت پایه (ریال)</span><input type="number" name="price" min="0" max="1000000000000" step="1" value="{{ old('price', $product->price) }}" required></label>
                <label><span>قیمت تخفیف (ریال)</span><input type="number" name="sale_price" min="0" max="1000000000000" step="1" value="{{ old('sale_price', $product->sale_price) }}"></label>
            </div>
            @if($product->status === 'published')
                <label class="owner-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))><span>نمایش ویژه در سایت</span></label>
            @endif
        </section>
        <aside class="owner-form-side space-y-4">
            <section class="owner-form-section space-y-4">
                <h2>تصویر و فایل فروش</h2>
                @if($cover)
                    <figure class="space-y-2">
                        <img src="{{ $cover->visibility === 'public' ? $cover->url() : route('media.view', $cover) }}" alt="جلد فعلی {{ $product->title }}" class="w-full rounded-xl border border-slate-200 object-cover" style="aspect-ratio:4/3" loading="lazy">
                        <figcaption class="text-xs text-slate-500">جلد فعلی. انتخاب تصویر تازه آن را جایگزین می‌کند.</figcaption>
                    </figure>
                @else
                    <p class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">برای این محصول جلدی ثبت نشده است.</p>
                @endif
                <label><span>جایگزینی تصویر جلد (اختیاری)</span><input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-owner-cover-input data-max-size-mb="10" data-allowed-extensions="jpg,jpeg,png,webp"><small class="owner-field-help">حداکثر ۱۰ مگابایت؛ تصویر قبلی پس از ذخیره موفق جایگزین می‌شود.</small></label>
                <label><span>جایگزینی PDF اصلی (اختیاری)</span><input type="file" name="pdf_file" accept=".pdf,application/pdf"><small class="owner-field-help">حداکثر ۵۰ مگابایت؛ فایل جدید خصوصی نگهداری می‌شود.</small></label>
                <label><span>نسخهٔ فایل جدید</span><input name="version" maxlength="32" value="{{ old('version', '1.1') }}"></label>
                <div class="rounded-xl border border-slate-200 p-3 text-sm">
                    <strong>فایل اصلی فعلی</strong>
                    <p class="mt-1 text-slate-600">{{ $primaryFile?->media?->original_name ?? 'ثبت نشده' }}</p>
                    <p class="mt-1 text-xs text-slate-500">نسخه: {{ $primaryFile?->version ?? '—' }} · دسترسی: خصوصی</p>
                    @if($product->files->isNotEmpty())
                        <hr class="my-3">
                        <strong>تاریخچه نسخه‌ها</strong>
                        <ul class="mt-2 space-y-1 text-xs text-slate-600">
                        @foreach($product->files->sortByDesc('id') as $fileVersion)
                            <li>{{ $fileVersion->version }} · {{ $fileVersion->media?->original_name ?? 'فایل حذف‌شده' }} · {{ $fileVersion->is_primary ? 'نسخه اصلی' : 'نسخه قبلی' }}</li>
                        @endforeach
                        </ul>
                    @endif
                </div>
                <p class="text-xs leading-6 text-slate-500">انتشار محصول از صفحه فهرست انجام می‌شود و بدون PDF واقعی که در فضای خصوصی موجود باشد مجاز نیست.</p>
            </section>
            <button type="submit" class="owner-primary-btn w-full">ذخیره تغییرات</button>
        </aside>
    </form>
</div>
@endsection