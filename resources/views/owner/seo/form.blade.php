@extends('layouts.seo-specialist')

@section('title', 'تنظیم ایندکس | شیخان')
@section('header-title', 'تنظیم ایندکس')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <section class="dashboard-panel p-5 sm:p-7">
        <a href="{{ route('owner.seo.index') }}" class="text-xs font-bold text-indigo-600">← بازگشت به فهرست محتوا</a>
        <div class="mt-4">
            <span class="text-xs font-black text-slate-500">{{ $type === 'blog' ? 'بلاگ' : 'مقاله آموزشگاه' }}</span>
            <h1 class="mt-2 text-xl font-black text-slate-900">{{ $item->getAttribute('title') ?: 'محتوای منتشرشده' }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">عنوان و توضیحات مخصوص موتورهای جستجو را در صورت نیاز تنظیم کن و وضعیت ایندکس را مشخص کن.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('owner.seo.update', [$type, $item->getKey()]) }}" class="dashboard-panel space-y-5 p-5 sm:p-7">
        @csrf
        @method('PATCH')
        <label class="block space-y-2"><span class="text-sm font-bold text-slate-700">عنوان SEO</span><input class="ui-control px-3" name="title" maxlength="255" value="{{ old('title', $seoMeta?->title) }}" placeholder="{{ $item->getAttribute('title') }}"></label>
        <label class="block space-y-2"><span class="text-sm font-bold text-slate-700">توضیحات SEO</span><textarea class="ui-control px-3 py-2" name="description" rows="4" maxlength="500" placeholder="توضیح کوتاه و دقیق درباره محتوای این صفحه">{{ old('description', $seoMeta?->description) }}</textarea></label>
        <label class="block space-y-2"><span class="text-sm font-bold text-slate-700">وضعیت نمایش در موتور جستجو</span><select class="ui-control px-3" name="robots" required><option value="index,follow" @selected(old('robots', $seoMeta?->robots ?: 'index,follow')==='index,follow')>اجازه ایندکس (index,follow)</option><option value="noindex,follow" @selected(old('robots', $seoMeta?->robots)==='noindex,follow')>عدم ایندکس (noindex,follow)</option></select><small class="text-xs leading-6 text-slate-500">با انتخاب noindex، صفحه از sitemap عمومی نیز حذف می‌شود.</small></label>
        @if($errors->any())<div role="alert" class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
        <div class="flex justify-end"><button type="submit" class="course-primary-btn">ذخیره تنظیمات</button></div>
    </form>
</div>
@endsection
