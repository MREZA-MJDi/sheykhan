@extends('layouts.owner')

@section('title', 'گزارش مشاهدهٔ فایل‌های محافظت‌شده | شیخان')
@section('header-title', 'گزارش مشاهدهٔ فایل‌ها')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div>
            <span>حقوق محتوا و پیگیری دسترسی</span>
            <h1>گزارش مشاهدهٔ فایل‌های خریداری‌شده</h1>
            <p>برای هر بازکردن فایل و هر صفحهٔ مشاهده‌شده، شناسهٔ کاربر، سفارش، زمان، IP و اطلاعات مرورگر ثبت می‌شود. این گزارش به‌تنهایی اثبات نمی‌کند چه کسی اسکرین‌شات گرفته؛ شناسهٔ همان کاربر روی صفحات نیز واترمارک شده است.</p>
        </div>
        <a href="{{ route('owner.dashboard') }}">داشبورد</a>
    </header>

    <section class="owner-form-section space-y-4">
        <form method="GET" action="{{ route('owner.protected-file-views.index', $academy) }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px_auto]">
            <label class="grid gap-2 text-sm font-bold">
                جست‌وجو
                <input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="نام، ایمیل، شماره سفارش یا عنوان محصول" class="min-h-11 rounded-xl border border-slate-300 px-3">
            </label>
            <label class="grid gap-2 text-sm font-bold">
                نوع رویداد
                <select name="event" class="min-h-11 rounded-xl border border-slate-300 px-3">
                    <option value="">همه</option>
                    <option value="open" @selected(request('event') === 'open')>بازکردن فایل</option>
                    <option value="page" @selected(request('event') === 'page')>مشاهده صفحه</option>
                </select>
            </label>
            <button class="min-h-11 self-end rounded-xl bg-indigo-700 px-5 font-black text-white">جست‌وجو</button>
        </form>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-black">آخرین رویدادها</h2>
            <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">{{ \App\Support\PersianUi::digits($views->total()) }} رویداد</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[1000px] w-full border-collapse text-sm">
                <thead class="bg-slate-50 text-right text-xs text-slate-600">
                    <tr>
                        <th class="p-3">زمان</th><th class="p-3">کاربر / شناسه</th><th class="p-3">محتوا</th>
                        <th class="p-3">سفارش</th><th class="p-3">رویداد / صفحه</th><th class="p-3">IP</th><th class="p-3">مرورگر</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($views as $event)
                        <tr class="align-top">
                            <td class="whitespace-nowrap p-3 text-xs text-slate-600">{{ \App\Support\PersianUi::date($event->viewed_at) }}<span class="mt-1 block" dir="ltr">{{ $event->viewed_at?->format('H:i:s') }}</span></td>
                            <td class="p-3"><strong>{{ $event->user?->name ?: 'حساب حذف‌شده' }}</strong><span class="mt-1 block text-xs text-slate-500">USER-{{ $event->user_id }}</span><span class="mt-1 block text-xs font-bold text-indigo-700" dir="ltr">VIEW-{{ $event->id }}</span><span class="mt-1 block text-xs text-slate-500">{{ $event->user?->email }}</span></td>
                            <td class="max-w-xs p-3"><strong>{{ $event->productFile?->product?->title ?: 'محصول' }}</strong><span class="mt-1 block break-words text-xs text-slate-500">{{ $event->productFile?->media?->original_name }}</span></td>
                            <td class="p-3 text-xs" dir="ltr">{{ $event->order?->order_number }}</td>
                            <td class="p-3 text-xs">{{ $event->event_type === 'page' ? 'مشاهده صفحه' : 'بازکردن فایل' }}<span class="mt-1 block">{{ $event->page_number ? 'صفحه ' . \App\Support\PersianUi::digits($event->page_number) : '—' }}</span></td>
                            <td class="p-3 text-xs" dir="ltr">{{ $event->ip_address ?: '—' }}</td>
                            <td class="max-w-xs break-words p-3 text-xs leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit((string) $event->user_agent, 150) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-10 text-center text-sm text-slate-500">هنوز رویدادی برای فایل‌های خریداری‌شدهٔ این آموزشگاه ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($views->hasPages())<div>{{ $views->links() }}</div>@endif

        <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-black">مشاهدهٔ منابع آموزشی در پنل دانش‌آموز/والد</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">هر بازشدن فایل و درخواست صفحه با شناسهٔ حساب، شناسهٔ منبع، فرزند انتخاب‌شده، IP و مرورگر ثبت می‌شود.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">{{ \App\Support\PersianUi::digits($learningViews->total()) }} رویداد آموزشی</span>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[900px] w-full border-collapse text-sm">
                <thead class="bg-slate-50 text-right text-xs text-slate-600">
                    <tr>
                        <th class="p-3">زمان</th><th class="p-3">کاربر / شناسه</th><th class="p-3">نام فایل</th>
                        <th class="p-3">شناسهٔ منبع</th><th class="p-3">فرزند</th><th class="p-3">رویداد / صفحه</th><th class="p-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($learningViews as $event)
                        <tr class="align-top">
                            <td class="whitespace-nowrap p-3 text-xs text-slate-600">{{ \App\Support\PersianUi::date($event->viewed_at) }}<span class="mt-1 block" dir="ltr">{{ $event->viewed_at?->format('H:i:s') }}</span></td>
                            <td class="p-3"><strong>{{ $event->user?->name ?: 'حساب حذف‌شده' }}</strong><span class="mt-1 block text-xs text-slate-500">USER-{{ $event->user_id }}</span><span class="mt-1 block text-xs font-bold text-indigo-700" dir="ltr">VIEW-{{ $event->id }}</span><span class="mt-1 block text-xs text-slate-500">{{ $event->user?->email }}</span></td>
                            <td class="max-w-xs break-words p-3 text-xs">{{ $event->media?->original_name ?: 'فایل آموزشی' }}</td>
                            <td class="p-3 text-xs">{{ $event->learning_resource_id ?: '—' }}</td>
                            <td class="p-3 text-xs">{{ $event->child_id ?: '—' }}</td>
                            <td class="p-3 text-xs">{{ $event->event_type === 'page' ? 'مشاهده صفحه' : 'بازکردن فایل' }}<span class="mt-1 block">{{ $event->page_number ? 'صفحه ' . \App\Support\PersianUi::digits($event->page_number) : '—' }}</span></td>
                            <td class="p-3 text-xs" dir="ltr">{{ $event->ip_address ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-10 text-center text-sm text-slate-500">هنوز رویدادی برای منابع آموزشی ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($learningViews->hasPages())<div class="mt-4">{{ $learningViews->links() }}</div>@endif
    </section>
</div>
@endsection
