@extends('layouts.owner')

@section('title', 'اتصال SpotPlayer | شیخان')
@section('header-title', 'اتصال SpotPlayer')
@section('content')
<div class="owner-form-shell space-y-6" dir="rtl">
    <header class="owner-form-head">
        <div>
            <span>پخش امن محتوای آموزشی</span>
            <h1>تنظیم اتصال SpotPlayer</h1>
            <p>کلید API فقط سمت سرور استفاده می‌شود. برای هر دوره نیز باید شناسهٔ همان دوره از پنل ناشر SpotPlayer ثبت شود.</p>
        </div>
        <a href="{{ route('owner.dashboard') }}">بازگشت به داشبورد</a>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-7 text-rose-800">
            <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="owner-form-section space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2>وضعیت اتصال</h2>
                <p class="mt-1 text-sm leading-7 text-slate-500">این وضعیت از روی تنظیمات واقعی برنامه محاسبه می‌شود؛ ذخیره‌شدن یک کلید به‌تنهایی پخش واقعی را اثبات نمی‌کند.</p>
            </div>
            <span class="rounded-full px-3 py-2 text-xs font-black {{ $isConfigured ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                {{ $isConfigured ? 'کلید و حالت اتصال تنظیم شده' : 'اتصال هنوز کامل تنظیم نشده' }}
            </span>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs text-slate-500">منبع کلید API</p>
                <strong class="mt-1 block text-sm">{{ $environmentKeyConfigured ? 'متغیر امن سرور (.env / environment)' : ($storedKeyConfigured ? 'تنظیم رمزگذاری‌شدهٔ پنل' : 'کلیدی ثبت نشده') }}</strong>
                <p class="mt-2 text-xs leading-6 text-slate-500">کلید ذخیره‌شده دوباره در صفحه نمایش داده نمی‌شود. کلید Environment همیشه بر مقدار ذخیره‌شده در پنل اولویت دارد.</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs text-slate-500">حالت مؤثر اتصال</p>
                <strong class="mt-1 block text-sm">{{ $mode === 'production' ? 'Production (لایسنس واقعی)' : ($mode === 'test' ? 'Test (آزمایشی)' : $mode) }}</strong>
                <p class="mt-2 text-xs leading-6 text-slate-500">حالت فعلی: {{ $mode }} · دستگاه‌های مجاز: {{ $maxActiveDevices }} · دستگاه وب: {{ $webAppDevices }}</p>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('owner.spotplayer.update') }}" class="owner-form-section space-y-4">
        @csrf
        @method('PUT')
        <h2>اعتبارنامه و حالت اجرا</h2>

        <label class="block space-y-2">
            <span class="text-sm font-bold text-slate-800">کلید API ناشر SpotPlayer</span>
            <input
                type="password"
                name="api_key"
                value=""
                autocomplete="new-password"
                autocapitalize="none"
                spellcheck="false"
                maxlength="2048"
                dir="ltr"
                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-left"
                placeholder="{{ $isConfigured ? 'برای حفظ کلید فعلی خالی بگذار' : 'کلید API را از پنل ناشر وارد کن' }}"
            >
            <small class="block leading-6 text-slate-500">مسیر دریافت: ورود به پنل ناشر SpotPlayer، بخش حساب/API. این کلید با شناسهٔ دوره و License Key فرق دارد. کلید در دیتابیس رمزگذاری می‌شود و هرگز در HTML یا log چاپ نمی‌شود.</small>
        </label>

        @if($storedKeyConfigured && ! $environmentKeyConfigured)
            <label class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-7 text-rose-900">
                <input type="checkbox" name="clear_api_key" value="1" class="mt-1">
                <span>حذف کلید ذخیره‌شده از پنل. اگر کلید در Environment سرور تعریف شده باشد، آن کلید فعال می‌ماند.</span>
            </label>
        @endif

        <label class="block space-y-2">
            <span class="text-sm font-bold text-slate-800">حالت اجرا</span>
            <select name="mode" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                <option value="test" @selected($mode === 'test')>Test — صدور لایسنس آزمایشی</option>
                <option value="production" @selected($mode === 'production')>Production — لایسنس واقعی</option>
            </select>
            @if($environmentModeOverride)
                <small class="block leading-6 text-amber-800">متغیر SPOTPLAYER_MODE در سرور تنظیم شده و انتخاب Environment بر حالت ذخیره‌شده در این فرم اولویت دارد.</small>
            @endif
        </label>

        <label class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-950">
            <input type="checkbox" name="confirm_production" value="1" class="mt-1">
            <span>اگر حالت Production انتخاب شد، تأیید می‌کنم حساب ناشر و دورهٔ آزمایشی مجاز بررسی شده و قصد صدور لایسنس واقعی را دارم.</span>
        </label>

        <button type="submit" class="owner-primary-btn w-full">ذخیره تنظیمات SpotPlayer</button>
    </form>

    <section class="owner-form-section space-y-5">
        <div>
            <h2>راهنمای اتصال از پنل ناشر تا پخش در شیخان</h2>
            <p class="mt-1 text-sm leading-7 text-slate-500">این راهنما در همین صفحه است تا هنگام تحویل، مدیر بدون جست‌وجوی فایل جداگانه بتواند اتصال را تنظیم کند.</p>
        </div>
        <ol class="space-y-4">
            <li class="flex gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-black text-indigo-800">۱</span>
                <div><strong>در پنل ناشر SpotPlayer وارد شو</strong><p class="mt-1 text-sm leading-7 text-slate-600">پنل مدیریت/ناشر را باز کن؛ دسترسی به خود وب‌پلیر به‌تنهایی برای صدور لایسنس کافی نیست. از قسمت حساب/API، کلید API دارای مجوز صدور لایسنس را بردار و در فرم بالا وارد کن.</p><a class="text-sm font-bold text-indigo-700 underline" href="https://panel.spotplayer.ir/" target="_blank" rel="noopener noreferrer">بازکردن پنل ناشر SpotPlayer</a></div>
            </li>
            <li class="flex gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-black text-indigo-800">۲</span>
                <div><strong>یک دورهٔ مجزا و آزمایشی بساز</strong><p class="mt-1 text-sm leading-7 text-slate-600">یک ویدئوی مجاز و غیرحساس بارگذاری کن؛ شناسهٔ خارجی همان دوره را از پنل بردار. ID داخلی شیخان، slug دوره یا License Key را جای آن قرار نده.</p></div>
            </li>
            <li class="flex gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-black text-indigo-800">۳</span>
                <div><strong>شناسهٔ دوره را در شیخان ثبت کن</strong><p class="mt-1 text-sm leading-7 text-slate-600">به بخش «دوره‌ها» برو، دورهٔ متناظر را ویرایش کن و فیلد «شناسهٔ دوره در SpotPlayer» را پر کن. هر دوره باید به شناسهٔ واقعی خودش متصل شود.</p><a class="text-sm font-bold text-indigo-700 underline" href="{{ route('owner.courses.index') }}">رفتن به مدیریت دوره‌ها</a></div>
            </li>
            <li class="flex gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-black text-indigo-800">۴</span>
                <div><strong>ابتدا Test را آزمایش کن</strong><p class="mt-1 text-sm leading-7 text-slate-600">یک دانش‌آموز تست با دسترسی معتبر را وارد کن؛ از صفحهٔ دوره «فعال‌سازی پخش امن ویدئو» را بزن. صدور لایسنس، بازشدن پلیر، پخش واقعی، واترمارک و محدودیت دستگاه را بررسی کن. تست‌های خودکار پروژه از API شبیه‌سازی‌شده استفاده می‌کنند و جای تست زنده را نمی‌گیرند.</p></div>
            </li>
            <li class="flex gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-black text-indigo-800">۵</span>
                <div><strong>فقط پس از تست، Production را فعال کن</strong><p class="mt-1 text-sm leading-7 text-slate-600">اگر متغیر SPOTPLAYER_MODE در سرور تعریف شده باشد، همان متغیر حالت را کنترل می‌کند. پس از اتصال واقعی و تأیید هزینه/مجوزهای حساب، حالت Production را فعال کن و لاگ‌ها را بدون ثبت API Key یا License Key بررسی کن.</p></div>
            </li>
        </ol>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-950">
            <strong>نکتهٔ امنیتی مهم</strong>
            <p class="mt-1">SpotPlayer برای پخش محافظت‌شدهٔ ویدئو است؛ PDF و سندهای خریداری‌شده باید از نمایشگر داخلی واترمارک‌دار شیخان نمایش داده شوند. واترمارک، محدودیت دسترسی و ثبت لاگ امکان ردیابی را بهتر می‌کنند، اما هیچ صفحهٔ وبی نمی‌تواند اسکرین‌شات یا ضبط با دستگاه دیگر را صددرصد مسدود کند.</p>
        </div>
    </section>
</div>
@endsection
