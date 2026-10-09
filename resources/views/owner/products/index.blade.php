@extends('layouts.owner')
@section('title', 'محصولات فروشگاه | شیخان')
@section('header-title', 'محصولات فروشگاه')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div><span>فروشگاه آموزشی آکادمی</span><h1>مدیریت کتاب، جزوه و آزمون</h1><p>محصولات منتشرشده در صفحه اصلی و فروشگاه نمایش داده می‌شوند. PDF اصلی همیشه خصوصی است و برای خریدار با واترمارک اختصاصی تحویل می‌شود.</p></div>
        <a href="{{ route('owner.dashboard') }}">داشبورد</a>
    </header>
    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('owner.products.store', $academy) }}" class="owner-form-grid">
        @csrf
        <section class="owner-form-section space-y-4">
            <h2>محصول جدید</h2>
            <label><span>عنوان محصول</span><input name="title" value="{{ old('title') }}" required maxlength="255"></label>
            <label><span>نامک (اختیاری)</span><input name="slug" value="{{ old('slug') }}" maxlength="255" placeholder="از عنوان ساخته می‌شود"></label>
            <label><span>زیرعنوان</span><input name="subtitle" value="{{ old('subtitle') }}" maxlength="255"></label>
            <label><span>شرح محصول</span><textarea name="description" rows="5" maxlength="12000">{{ old('description') }}</textarea></label>
            <div class="owner-form-two">
                <label><span>گروه فروش</span><select name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id')===(string)$category->id)>{{ $category->name }}</option>@endforeach</select></label>
                <label><span>نوع محصول</span><select name="product_type" required><option value="book" @selected(old('product_type')==='book')>کتاب / تألیف و ترجمه</option><option value="booklet" @selected(old('product_type')==='booklet')>جزوه</option><option value="exam" @selected(old('product_type')==='exam')>آزمون</option><option value="digital" @selected(old('product_type')==='digital')>محصول دیجیتال</option><option value="file" @selected(old('product_type')==='file')>فایل آموزشی</option></select></label>
            </div>
            <div class="owner-form-two">
                <label><span>قیمت (ریال)</span><input type="number" name="price" min="0" max="1000000000000" step="1" value="{{ old('price', 0) }}" required></label>
                <label><span>قیمت تخفیف (ریال، اختیاری)</span><input type="number" name="sale_price" min="0" max="1000000000000" step="1" value="{{ old('sale_price') }}"></label>
            </div>
            <label><span>نسخه فایل (مثلاً 1.0)</span><input name="version" value="{{ old('version','1.0') }}" maxlength="32"></label>
        </section>
        <aside class="owner-form-side space-y-4">
            <section class="owner-form-section space-y-4">
                <h2>فایل‌های واقعی محصول</h2>
                <label><span>تصویر روی جلد (اختیاری)</span><input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"><small>حداکثر ۱۰ مگابایت</small></label>
                <label><span>PDF اصلی محصول</span><input type="file" name="pdf_file" accept="application/pdf,.pdf"><small>حداکثر ۵۰ مگابایت. در انتشار عمومی ضروری است.</small></label>
                <label class="owner-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))><span>نمایش ویژه در صفحه اصلی</span></label>
                <label class="owner-check"><input type="checkbox" name="publish" value="1" @checked(old('publish'))><span>انتشار عمومی (نیازمند PDF واقعی)</span></label>
            </section>
            <button type="submit" class="owner-primary-btn w-full">ذخیره محصول</button>
            <p class="text-xs leading-6 text-slate-500">قیمت از فرم خریدار گرفته نمی‌شود؛ سفارش مبلغ محصول را دوباره از دیتابیس محاسبه می‌کند. فایل اصلی با URL عمومی منتشر نمی‌شود.</p>
        </aside>
    </form>

    <section class="owner-form-section space-y-3">
        <h2>محصول‌های ثبت‌شده</h2>
        @forelse($products as $product)
            <article class="flex flex-wrap items-start justify-between gap-4 rounded-xl border border-slate-200 p-4">
                <div class="min-w-0">
                    <strong>{{ $product->title }}</strong>
                    <p class="mt-1 text-xs text-slate-500">{{ $product->category?->name }} · {{ \App\Support\PersianUi::money($product->sale_price ?? $product->price) }} · {{ $product->status }}</p>
                    <p class="mt-1 text-xs text-slate-500">PDF: {{ $product->files->where('is_preview', false)->count() ? 'ثبت شده' : 'ثبت نشده' }} · فایل اصلی خصوصی</p>
                    <a class="mt-2 inline-flex text-xs font-bold text-[var(--color-primary-600)]" href="{{ route('store.product.show', $product) }}" target="_blank" rel="noopener noreferrer">مشاهده صفحه محصول ←</a>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($product->status !== 'published')
                        <form method="POST" action="{{ route('owner.products.publish', [$academy, $product]) }}">@csrf @method('PATCH')<button class="rounded-lg border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-800">انتشار</button></form>
                    @else
                        <form method="POST" action="{{ route('owner.products.archive', [$academy, $product]) }}">@csrf @method('PATCH')<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-800">خروج از فروش</button></form>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">هنوز محصول واقعی ثبت نشده است. داده‌های seed جایگزین فایل‌های واقعی تولید محتوا نیستند.</p>
        @endforelse
        @if($products->hasPages())<div>{{ $products->links() }}</div>@endif
    </section>
</div>
@endsection
