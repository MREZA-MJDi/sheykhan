@extends('layouts.owner')
@section('title','تنظیمات آموزشگاه | شیخان')
@section('header-title','تنظیمات آموزشگاه')
@section('content')
<div class="mx-auto max-w-4xl"><div class="dashboard-panel p-5 sm:p-7"><div class="mb-6"><p class="text-xs font-black text-[var(--panel-primary)]">Academy</p><h2 class="mt-1 text-2xl font-black">اطلاعات آموزشگاه</h2></div>
<form method="POST" action="{{ route('owner.academy.update',$academy) }}" class="grid gap-5 sm:grid-cols-2">@csrf @method('PATCH')
@foreach([['name','نام آموزشگاه'],['slug','Slug'],['code','کد آموزشگاه'],['phone','تلفن'],['email','ایمیل'],['city','شهر'],['province','استان'],['website','وب‌سایت']] as $field)
<label class="grid gap-2"><span class="text-xs font-bold">{{ $field[1] }}</span><input name="{{ $field[0] }}" value="{{ old($field[0],$academy->{$field[0]}) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
@endforeach
<label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">آدرس</span><input name="address" value="{{ old('address',$academy->address) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
<label class="grid gap-2 sm:col-span-2"><span class="text-xs font-bold">توضیحات</span><textarea name="description" rows="6" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('description',$academy->description) }}</textarea></label>
<label class="grid gap-2"><span class="text-xs font-bold">وضعیت</span><select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="active" @selected($academy->status==='active')>فعال</option><option value="inactive" @selected($academy->status==='inactive')>غیرفعال</option><option value="suspended" @selected($academy->status==='suspended')>تعلیق</option></select></label>
<div class="flex items-end gap-2"><a href="{{ route('owner.dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold">برگشت</a><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ذخیره تغییرات</button></div>
</form></div></div>
@endsection