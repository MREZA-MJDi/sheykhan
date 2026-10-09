@extends('layouts.app')
@section('title', 'تکمیل سفارش | شیخان')
@section('description', 'ثبت سفارش امن محصولات آموزشی شیخان')
@section('content')
<section class="public-store-page">
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <form method="POST" action="{{ route('checkout.store', $product) }}" class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-sm)] sm:p-8">
                    @csrf
                    <span class="ui-eyebrow">مرحله ثبت سفارش</span>
                    <h1 class="mt-2 text-2xl font-black text-[var(--color-text)] sm:text-3xl">تکمیل خرید امن</h1>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-text-secondary)]">سفارش ابتدا در وضعیت انتظار قرار می‌گیرد. ارسال رسید به‌تنهایی به معنی پرداخت نیست و فایل خریداری‌شده فقط بعد از تأیید واقعی وجه فعال می‌شود.</p>

                    @if(session('success'))<div role="status" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
                    @if($errors->any())<div role="alert" class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                    <div class="mt-7 grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-bold text-[var(--color-text)]"><span>نام و نام خانوادگی خریدار</span><input required name="billing_name" maxlength="160" value="{{ old('billing_name', auth()->user()->name) }}" class="min-h-12 rounded-xl border border-[var(--color-border)] bg-white px-4"></label>
                        <label class="grid gap-2 text-sm font-bold text-[var(--color-text)]"><span>شماره تماس</span><input required name="billing_mobile" maxlength="32" value="{{ old('billing_mobile', auth()->user()->mobile) }}" class="min-h-12 rounded-xl border border-[var(--color-border)] bg-white px-4"></label>
                    </div>

                    <section class="mt-8 rounded-2xl border border-[var(--color-border)] p-4 sm:p-5">
                        <h2 class="text-lg font-black text-[var(--color-text)]">شرایط خرید و کپی‌رایت</h2>
                        @if($legalReady)
                            <p class="mt-2 text-xs leading-7 text-[var(--color-text-muted)]">متن دقیق هر نسخه در سفارش ثبت و با SHA-256 نگه‌داری می‌شود؛ تغییر نسخهٔ سند در آینده رضایت قبلی را بازنویسی نمی‌کند.</p>
                            <div class="mt-4 space-y-4">
                                @foreach($documents as $document)
                                    <article class="rounded-xl bg-[var(--color-background-soft)] p-4">
                                        <h3 class="font-bold text-[var(--color-text)]">{{ $document->title }} <span class="text-xs text-[var(--color-text-muted)]">نسخه {{ $document->version }}</span></h3>
                                        <div class="mt-3 max-h-48 overflow-y-auto whitespace-pre-line rounded-lg bg-white p-3 text-xs leading-7 text-[var(--color-text-secondary)]">{{ $document->content }}</div>
                                        <label class="mt-4 flex items-start gap-3 text-sm leading-7 text-[var(--color-text)]">
                                            <input type="checkbox" name="consents[{{ $document->id }}]" value="1" required @checked(old('consents.'.$document->id)) class="mt-2">
                                            <span>متن «{{ $document->title }}» نسخه {{ $document->version }} را خواندم و می‌پذیرم.</span>
                                        </label>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm leading-7 text-amber-900">فروش تا زمان انتشار متن نهایی شرایط خرید و کپی‌رایت توسط مدیر متوقف است. متن‌های نمونه یا پیش‌نویس برای دریافت رضایت حقوقی پذیرفته نمی‌شوند.</div>
                        @endif
                    </section>

                    <section class="mt-6 rounded-2xl border border-[var(--color-border)] p-4 sm:p-5">
                        <h2 class="text-lg font-black text-[var(--color-text)]">پرداخت انتقال وجه</h2>
                        @if($transferReady)
                            <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">بعد از ثبت سفارش، اطلاعات بانکی نمایش داده می‌شود. شماره سفارش را در شرح واریز بنویس و رسید را در صفحه سفارش بارگذاری کن.</p>
                        @else
                            <p class="mt-2 text-sm leading-7 text-amber-800">اطلاعات بانکی فروشگاه هنوز در محیط سرور تنظیم نشده است؛ تا آن زمان هیچ سفارش تازه‌ای ثبت نمی‌شود.</p>
                        @endif
                    </section>

                    <button type="submit" @disabled(!$canSubmit) class="mt-7 inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-[var(--color-primary-600)] px-5 text-sm font-black text-white disabled:cursor-not-allowed disabled:opacity-50">ثبت سفارش و نمایش اطلاعات واریز</button>
                    <p class="mt-3 text-xs leading-6 text-[var(--color-text-muted)]">هیچ فایل خریداری‌شده‌ای با ثبت سفارش یا بارگذاری رسید به‌تنهایی قابل دریافت نخواهد شد.</p>
                </form>

                <aside class="h-fit rounded-3xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-5 sm:p-6">
                    @if($product->media->first()?->visibility === 'public' && $product->media->first()?->url())
                        <img src="{{ $product->media->first()->url() }}" alt="{{ $product->title }}" class="aspect-[4/3] w-full rounded-2xl object-cover" loading="lazy">
                    @endif
                    <h2 class="mt-5 text-lg font-black text-[var(--color-text)]">{{ $product->title }}</h2>
                    <span class="mt-2 inline-flex rounded-full bg-white px-3 py-1 text-xs font-bold text-[var(--color-text-muted)]">{{ $product->category?->name }}</span>
                    <div class="mt-6 border-t border-[var(--color-border)] pt-5">
                        <span class="text-xs font-bold text-[var(--color-text-muted)]">مبلغ سفارش</span>
                        <strong class="mt-2 block text-2xl font-black text-[var(--color-primary-600)]">{{ \App\Support\PersianUi::money($price) }}</strong>
                    </div>
                </aside>
            </div>
        </x-layout.container>
    </x-layout.section>
</section>
@endsection
