@extends('layouts.owner')

@section('title', 'حقوق نشر محتوا | شیخان')
@section('header-title', 'حقوق نشر و مجوز محتوا')

@section('content')
@php
    $editing = $editingRight->exists;
    $selectedMediaId = old('media_id', $editingRight->media_id);
    $statusLabels = [
        'draft' => 'پیش‌نویس',
        'active' => 'فعال',
        'revoked' => 'لغوشده',
        'superseded' => 'جایگزین‌شده',
    ];
@endphp

<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div>
            <span>مدیریت حقوق محتوا</span>
            <h1>حقوق نشر و مجوز فایل‌ها</h1>
            <p>برای هر ویدئو، فایل ضبط‌شده، جزوه، کتاب یا فایل آموزشی یک پروندهٔ قابل پیگیری بسازید. فعال‌سازی مجوز فقط پس از بررسی مدرک انجام می‌شود.</p>
        </div>
        <a href="{{ route('owner.dashboard') }}">بازگشت به داشبورد</a>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-7 text-rose-800">
            <strong>اطلاعات نیازمند اصلاح است.</strong>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <article class="owner-form-section">
            <span class="text-sm text-slate-500">مجوزهای معتبر در حال حاضر</span>
            <strong class="mt-2 block text-2xl">{{ \App\Support\PersianUi::number($activeCount) }}</strong>
        </article>
        <article class="owner-form-section">
            <span class="text-sm text-slate-500">پیش‌نویس‌های منتظر بررسی</span>
            <strong class="mt-2 block text-2xl">{{ \App\Support\PersianUi::number($draftCount) }}</strong>
        </article>
        <article class="owner-form-section">
            <span class="text-sm text-slate-500">مجوزهای منقضی‌شده</span>
            <strong class="mt-2 block text-2xl">{{ \App\Support\PersianUi::number($expiredCount) }}</strong>
        </article>
    </div>

    <section class="owner-form-section space-y-4">
        <div>
            <h2>{{ $editing ? 'ویرایش پیش‌نویس حقوق نشر' : 'ثبت پرونده حقوق نشر' }}</h2>
            <p class="mt-1 text-sm leading-7 text-slate-500">ثبت پرونده به‌تنهایی مجوز استفاده ایجاد نمی‌کند. منبع، مالک، شرایط و مدرک را بررسی کنید؛ سپس آن را فعال کنید.</p>
        </div>

        <form method="GET" action="{{ route('owner.content-rights.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_auto]">
            <label>
                <span>یافتن فایل برای اتصال مجوز</span>
                <input type="search" name="media_q" value="{{ $mediaSearch }}" maxlength="100" placeholder="نام فایل یا دسته، مثل recordings">
            </label>
            <button type="submit" class="owner-secondary-btn self-end">جست‌وجوی فایل</button>
        </form>

        <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('owner.content-rights.update', $editingRight) : route('owner.content-rights.store') }}" class="space-y-4">
            @csrf
            @if($editing) @method('PATCH') @endif

            <label class="block">
                <span>فایل موجود در کتابخانه</span>
                <select name="media_id" required>
                    <option value="">انتخاب فایل</option>
                    @foreach($mediaOptions as $media)
                        <option value="{{ $media->id }}" @selected((string) $selectedMediaId === (string) $media->id)>
                            #{{ $media->id }} · {{ $media->original_name }} · {{ $media->collection }} · {{ number_format((int) $media->size) }} بایت
                        </option>
                    @endforeach
                </select>
                @if($mediaOptions->isEmpty())
                    <small class="block text-amber-700">فایلی در این جست‌وجو پیدا نشد. نام دقیق فایل را در کادر جست‌وجوی بالا وارد کنید.</small>
                @else
                    <small class="block text-slate-500">برای فهرست کامل‌تر، نام فایل را جست‌وجو کنید؛ این فهرست به ۱۰۰ نتیجه محدود است.</small>
                @endif
            </label>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label><span>عنوان پرونده</span><input name="title" value="{{ old('title', $editingRight->title) }}" maxlength="255" required placeholder="مثلاً حق پخش ویدئوی جلسه اول"></label>
                <label><span>نام مالک اثر یا صاحب مجوز</span><input name="rights_holder" value="{{ old('rights_holder', $editingRight->rights_holder) }}" maxlength="255" required placeholder="نام شخص یا سازمان صاحب حق"></label>
                <label>
                    <span>منشأ حق</span>
                    <select name="source_type" required>
                        <option value="">انتخاب منشأ</option>
                        @foreach($sourceLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('source_type', $editingRight->source_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span>نام مجوز یا مبنای مالکیت</span><input name="license_name" value="{{ old('license_name', $editingRight->license_name) }}" maxlength="150" required placeholder="نام قرارداد، مجوز یا مالکیت"></label>
                <label><span>شماره قرارداد / شناسه مجوز</span><input name="license_reference" value="{{ old('license_reference', $editingRight->license_reference) }}" maxlength="190"></label>
                <label><span>قلمرو جغرافیایی</span><input name="territory" value="{{ old('territory', $editingRight->territory ?: 'جهانی') }}" maxlength="120"></label>
                <label><span>شروع اعتبار (اختیاری)</span><input type="datetime-local" name="valid_from" value="{{ old('valid_from', $editingRight->valid_from?->format('Y-m-d\TH:i')) }}"></label>
                <label><span>پایان اعتبار (اختیاری)</span><input type="datetime-local" name="valid_until" value="{{ old('valid_until', $editingRight->valid_until?->format('Y-m-d\TH:i')) }}"></label>
            </div>

            <label class="block"><span>نشانی منبع یا قرارداد (اختیاری)</span><input type="url" name="source_url" value="{{ old('source_url', $editingRight->source_url) }}" maxlength="2048" placeholder="https://..."></label>

            <fieldset class="rounded-xl border border-slate-200 p-4">
                <legend class="px-2 font-bold">استفاده‌های مجاز طبق مدرک</legend>
                <p class="mb-3 text-sm leading-6 text-slate-500">فقط گزینه‌هایی را انتخاب کنید که در قرارداد یا مجوز صریحاً اجازه داده شده‌اند.</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($useLabels as $value => $label)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="permitted_uses[]" value="{{ $value }}" @checked(in_array($value, old('permitted_uses', $editingRight->permitted_uses ?? []), true))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <label class="block"><span>الزام درج نام صاحب اثر / شیوه انتساب</span><textarea name="attribution" rows="3" maxlength="5000" placeholder="متن دقیق انتساب یا محدودیت قراردادی">{{ old('attribution', $editingRight->attribution) }}</textarea></label>
            <label class="block">
                <span>مدرک پشتیبان (PDF یا تصویر، حداکثر ۱۰ مگابایت)</span>
                <input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                @if($editingRight->evidence_path)
                    <small class="block text-slate-500">مدرک قبلی تا زمان جایگزینی حفظ می‌شود؛ از سوابق می‌توانید آن را دانلود کنید.</small>
                @endif
            </label>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="owner-primary-btn">{{ $editing ? 'ذخیره پیش‌نویس' : 'ثبت پیش‌نویس حقوق نشر' }}</button>
                @if($editing)<a class="owner-secondary-btn" href="{{ route('owner.content-rights.index') }}">لغو و بازگشت</a>@endif
            </div>
        </form>
    </section>

    <section class="owner-form-section space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div><h2>فهرست پرونده‌ها و سابقه مجوزها</h2><p class="mt-1 text-sm leading-7 text-slate-500">نسخه‌های جایگزین یا لغوشده حذف نمی‌شوند تا سابقه حقوق هر فایل باقی بماند.</p></div>
            <form method="GET" action="{{ route('owner.content-rights.index') }}" class="flex flex-wrap items-end gap-2">
                <label><span>جست‌وجو</span><input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="عنوان، مالک یا نام فایل"></label>
                <label><span>وضعیت</span><select name="status"><option value="all" @selected($status === 'all')>همه</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></label>
                <button type="submit" class="owner-secondary-btn">اعمال فیلتر</button>
            </form>
        </div>

        @forelse($rights as $right)
            @php
                $expired = $right->status === 'active' && $right->valid_until?->isPast();
                $notStarted = $right->status === 'active' && $right->valid_from?->isFuture();
                $displayStatus = $expired ? 'منقضی‌شده' : ($notStarted ? 'در انتظار شروع' : ($statusLabels[$right->status] ?? $right->status));
            @endphp
            <article class="rounded-2xl border border-slate-200 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold">{{ $right->title }}</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">فایل: {{ $right->media?->original_name ?? 'فایل حذف‌شده' }} · شناسه فایل {{ $right->media_id }}</p>
                        <p class="text-sm leading-6 text-slate-600">صاحب حق: {{ $right->rights_holder }} · {{ $sourceLabels[$right->source_type] ?? $right->source_type }}</p>
                        <p class="text-sm leading-6 text-slate-600">مبنای حق: {{ $right->license_name }}{{ $right->license_reference ? ' · ' . $right->license_reference : '' }}</p>
                        <p class="text-xs leading-6 text-slate-500">
                            شروع: {{ $right->valid_from?->format('Y/m/d H:i') ?? 'ثبت نشده' }} · پایان: {{ $right->valid_until?->format('Y/m/d H:i') ?? 'بدون تاریخ پایان ثبت‌شده' }}
                        </p>
                        <p class="text-sm leading-6 text-slate-600">موارد مجاز: {{ collect($right->permitted_uses ?? [])->map(fn ($use) => $useLabels[$use] ?? $use)->join('، ') }}</p>
                        @if($right->attribution)<p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $right->attribution }}</p>@endif
                        <p class="mt-1 text-xs text-slate-500">ثبت‌کننده: {{ $right->creator?->name ?? 'حساب حذف‌شده' }} · ثبت: {{ $right->created_at?->format('Y/m/d H:i') }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $right->status === 'active' && !$expired && !$notStarted ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">{{ $displayStatus }}</span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if($right->evidence_path)
                        <a class="owner-secondary-btn" href="{{ route('owner.content-rights.evidence', $right) }}">مشاهده مدرک</a>
                    @endif
                    @if($right->status === 'draft')
                        <a class="owner-secondary-btn" href="{{ route('owner.content-rights.edit', $right) }}">ویرایش پیش‌نویس</a>
                        <form method="POST" action="{{ route('owner.content-rights.activate', $right) }}" onsubmit="return confirm('فعال‌سازی فقط پس از بررسی اعتبار مدرک و محدوده استفاده انجام شود. ادامه می‌دهید؟')">
                            @csrf @method('PATCH')
                            <button type="submit" class="owner-primary-btn">فعال‌سازی مجوز</button>
                        </form>
                    @elseif($right->status === 'active')
                        <form method="POST" action="{{ route('owner.content-rights.revoke', $right) }}" onsubmit="return confirm('مجوز لغو شود؟ سابقه آن حفظ می‌شود.')">
                            @csrf @method('PATCH')
                            <button type="submit" class="owner-danger-btn">لغو مجوز</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 p-6 text-sm leading-7 text-slate-500">پرونده‌ای ثبت نشده است. از فرم بالا برای اتصال یک فایل موجود به اطلاعات مالکیت یا مجوز استفاده کنید.</div>
        @endforelse

        @if($rights->hasPages())<div>{{ $rights->links() }}</div>@endif
    </section>

    <aside class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-900">
        این صفحه رجیستری حقوق و مدارک است؛ به‌تنهایی مالکیت قانونی را اثبات نمی‌کند و مجوز منقضی‌شده یا لغوشده را به‌صورت خودکار از SpotPlayer یا فروشگاه ابطال نمی‌کند. قبل از انتشار یا فروش، قرارداد را بررسی کنید و برای لغو خودکار دسترسی محتوای محافظت‌شده، اتصال جداگانه به چرخهٔ عمر مجوز SpotPlayer لازم است.
    </aside>
</div>
@endsection
