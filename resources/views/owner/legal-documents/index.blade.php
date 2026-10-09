@extends('layouts.owner')
@section('title', 'اسناد حقوقی | شیخان')
@section('header-title', 'اسناد حقوقی و کپی‌رایت')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div><span>قوانین فروشگاه</span><h1>مدیریت نسخه‌های حقوقی</h1><p>نسخهٔ منتشرشده immutable است؛ برای هر اصلاح، نسخهٔ جدید منتشر کن تا سابقهٔ رضایت سفارش‌های قبلی تغییر نکند.</p></div>
        <a href="{{ route('owner.dashboard') }}">داشبورد</a>
    </header>
    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="rounded-2xl border p-5 {{ $purchaseReady ? 'border-emerald-300 bg-emerald-50' : 'border-amber-300 bg-amber-50' }}">
        <strong>{{ $purchaseReady ? 'شرایط خرید آماده است' : 'فروش هنوز از نظر اسناد حقوقی قفل است' }}</strong>
        <p class="mt-1 text-sm leading-7">{{ $purchaseReady ? 'هر دو سند الزامی با hash معتبر منتشر شده‌اند. قبل از انتشار، متن را با مشاور حقوقی بررسی کرده باشید.' : 'برای فعال شدن checkout باید شرایط خرید و کپی‌رایت با نسخهٔ رسمی، hash معتبر و وضعیت منتشرشده ثبت شوند.' }}</p>
    </div>

    <form method="POST" action="{{ route('owner.legal-documents.store') }}" class="owner-form-section space-y-4">
        @csrf
        <h2>ثبت نسخهٔ جدید سند</h2>
        <div class="owner-form-two">
            <label><span>نوع سند</span><select name="code" required><option value="purchase-terms" @selected(old('code')==='purchase-terms')>شرایط خرید محصولات</option><option value="copyright" @selected(old('code')==='copyright')>کپی‌رایت محصولات</option><option value="media-release" @selected(old('code')==='media-release')>رضایت انتشار رسانه</option></select></label>
            <label><span>شماره نسخه (مثلاً 1.1)</span><input name="version" value="{{ old('version') }}" pattern="[A-Za-z0-9._-]+" maxlength="32" required></label>
        </div>
        <label><span>عنوان نمایشی سند</span><input name="title" value="{{ old('title') }}" maxlength="255" required></label>
        <label><span>متن کامل سند — دست‌کم ۱۰۰ نویسه</span><textarea name="content" rows="14" minlength="100" maxlength="100000" required class="w-full rounded-xl border border-slate-200 p-4 leading-8">{{ old('content') }}</textarea><small>پیش از انتشار، متن دقیق را با مشاور حقوقی بررسی کنید. hash SHA-256 روی متن ذخیره می‌شود.</small></label>
        <label class="owner-check"><input type="checkbox" name="publish" value="1" @checked(old('publish'))><span>انتشار عمومی نسخهٔ جدید و غیرفعال‌کردن نسخهٔ فعال قبلی</span></label>
        <button class="owner-primary-btn w-full">ذخیره نسخه</button>
    </form>

    <section class="owner-form-section space-y-3">
        <h2>سوابق نسخه‌ها</h2>
        @forelse($documents as $document)
            <article class="rounded-xl border border-slate-200 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><strong>{{ $document->title }}</strong><p class="mt-1 text-xs text-slate-500">{{ $document->code }} · نسخه {{ $document->version }} · hash: {{ \Illuminate\Support\Str::limit($document->content_hash, 24, '…') }}</p><p class="mt-1 text-xs text-slate-500">{{ $document->published_at?->format('Y-m-d H:i') ?? 'منتشر نشده' }}</p></div>
                    <div class="flex items-center gap-2"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $document->is_active && $document->published_at ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $document->is_active && $document->published_at ? 'منتشرشده' : 'غیرفعال/پیش‌نویس' }}</span>
                    @if($document->is_active)<form method="POST" action="{{ route('owner.legal-documents.deactivate', $document) }}">@csrf @method('PATCH')<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">غیرفعال‌سازی</button></form>@endif</div>
                </div>
                <details class="mt-3"><summary class="cursor-pointer text-xs font-bold">مشاهدهٔ متن ذخیره‌شده</summary><div class="mt-3 whitespace-pre-line text-xs leading-7 text-slate-600">{{ $document->content }}</div></details>
            </article>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">سندی ثبت نشده است.</p>
        @endforelse
        @if($documents->hasPages())<div>{{ $documents->links() }}</div>@endif
    </section>
</div>
@endsection
