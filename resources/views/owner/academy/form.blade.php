@extends('layouts.owner')
@section('title','تنظیمات آموزشگاه | شیخان')
@section('header-title','تنظیمات آموزشگاه')
@section('content')
<div class="mx-auto max-w-4xl"><section class="dashboard-panel p-5 sm:p-7"><div class="mb-6"><p class="text-xs font-black text-[var(--panel-primary)]">Academy</p><h1 class="mt-1 text-2xl font-black">اطلاعات آموزشگاه</h1><p class="mt-2 text-sm text-slate-500">این آموزشگاه مستقل از سایر آموزشگاه‌های این حساب مدیریت می‌شود.</p></div>
<form method="POST" action="{{ route('owner.academy.update',$academy) }}" class="grid gap-5 sm:grid-cols-2">@csrf @method('PATCH')
@foreach([['name','نام آموزشگاه','text'],['slug','شناسه URL','text'],['code','کد آموزشگاه','text'],['phone','تلفن','text'],['email','ایمیل','email'],['city','شهر','text'],['province','استان','text'],['website','وب‌سایت','url']] as [$field,$label,$type])
<label class="grid gap-1">
    <span class="text-xs font-bold">{{ $label }}</span>
    <input name="{{ $field }}" type="{{ $type }}" value="{{ old($field,$academy->{$field}) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
    @if($field === 'name')<small class="owner-field-help">نامی را وارد کن که در سربرگ‌ها و بخش عمومی آموزشگاه نمایش داده می‌شود.</small>
    @elseif($field === 'slug')<small class="owner-field-help">شناسهٔ خوانا برای نشانی‌های عمومی است؛ از فاصله و علائم غیرضروری پرهیز کن.</small>
    @elseif($field === 'code')<small class="owner-field-help">کد داخلی آموزشگاه برای شناسایی در گزارش‌ها و عملیات مدیریتی.</small>
    @elseif($field === 'phone')<small class="owner-field-help">شمارهٔ تماس رسمی آموزشگاه؛ کد کشور را در صورت نیاز وارد کن.</small>
    @elseif($field === 'email')<small class="owner-field-help">ایمیل عمومی و قابل‌دسترسی برای تماس‌های رسمی.</small>
    @elseif($field === 'city' || $field === 'province')<small class="owner-field-help">برای اطلاعات تماس، فهرست‌کردن و شناسایی موقعیت آموزشگاه استفاده می‌شود.</small>
    @elseif($field === 'website')<small class="owner-field-help">نشانی کامل سایت را با https:// وارد کن.</small>
    @endif
    <x-owner.field-error :field="$field"/>
</label>
@endforeach
<label class="grid gap-1 sm:col-span-2"><span class="text-xs font-bold">آدرس</span><input name="address" value="{{ old('address',$academy->address) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">آدرس قابل‌استفاده برای معرفی و تماس؛ اطلاعات غیرضروری یا خصوصی را وارد نکن.</small><x-owner.field-error field="address"/></label>
<label class="grid gap-1 sm:col-span-2"><span class="text-xs font-bold">توضیحات</span><textarea name="description" rows="6" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('description',$academy->description) }}</textarea><small class="owner-field-help">معرفی کوتاه آموزشگاه و حوزهٔ فعالیت را بنویس؛ این متن ممکن است در بخش‌های عمومی نمایش داده شود.</small><x-owner.field-error field="description"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">وضعیت</span><select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="active" @selected($academy->status==='active')>فعال</option><option value="inactive" @selected($academy->status==='inactive')>غیرفعال</option><option value="suspended" @selected($academy->status==='suspended')>تعلیق</option></select><small class="owner-field-help">تغییر وضعیت می‌تواند دسترسی به عملیات آموزشگاه را تحت تأثیر قرار دهد؛ پیش از تغییر، اثر آن را بررسی کن.</small><x-owner.field-error field="status"/></label>
<div class="flex items-end gap-2"><a href="{{ route('owner.dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">برگشت</a><button type="submit" class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ذخیره تغییرات</button></div>
</form></section></div>
@endsection