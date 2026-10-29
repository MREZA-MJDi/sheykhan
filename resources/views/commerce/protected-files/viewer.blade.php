@extends('layouts.app')

@section('title', ($file->product?->title ?: 'فایل آموزشی') . ' | نمایشگر امن شیخان')
@section('description', 'نمایش امن فایل خریداری‌شده در فضای شیخان.')
@section('content')
<section class="mx-auto w-full max-w-6xl px-3 py-5 sm:px-6 sm:py-8">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <header class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="min-w-0">
                <p class="text-xs font-black tracking-wide text-indigo-700">SHEIKHAN · SECURE VIEWER</p>
                <h1 class="mt-2 break-words text-xl font-black text-slate-900 sm:text-2xl">{{ $file->product?->title ?: 'فایل خریداری‌شده' }}</h1>
                <p class="mt-2 break-words text-sm leading-6 text-slate-600">{{ $file->media?->original_name ?: 'محتوای محافظت‌شده' }}</p>
            </div>
            <a href="{{ route('orders.show', $order) }}" class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-800 hover:bg-slate-100">بازگشت به سفارش</a>
        </header>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 text-xs leading-6 text-slate-600 sm:px-6">
            <span>شناسهٔ ردیابی: <strong dir="ltr" class="font-black text-slate-900">USER-{{ $user->id }} · ORDER-{{ $order->order_number }}</strong></span>
            @if($pageCount > 0)
                <span>{{ \App\Support\PersianUi::digits($pageCount) }} صفحه · نمایش امن، بدون لینک دانلود</span>
            @else
                <span>نمایش متنی محافظت‌شده · بدون لینک دانلود</span>
            @endif
        </div>

        <div class="secure-viewer relative isolate space-y-5 bg-slate-100 p-3 sm:p-6" data-secure-viewer>
            <div class="secure-viewer__watermark" aria-hidden="true">
                <span>شیخان · USER-{{ $user->id }} · ORDER-{{ $order->order_number }}</span>
                <span>شیخان · USER-{{ $user->id }} · ORDER-{{ $order->order_number }}</span>
                <span>شیخان · USER-{{ $user->id }} · ORDER-{{ $order->order_number }}</span>
                <span>شیخان · USER-{{ $user->id }} · ORDER-{{ $order->order_number }}</span>
                <span>شیخان · USER-{{ $user->id }} · ORDER-{{ $order->order_number }}</span>
                <span>شیخان · USER-{{ $user->id }} · ORDER-{{ $order->order_number }}</span>
            </div>

            @if($textContent !== null)
                <article class="relative z-10 mx-auto max-w-4xl overflow-auto rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-8">
                    <pre class="select-none whitespace-pre-wrap break-words font-mono text-sm leading-8 text-slate-800">{{ $textContent }}</pre>
                </article>
            @else
                @foreach($pageUrls as $index => $pageUrl)
                    <figure class="relative z-10 mx-auto w-full max-w-5xl overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
                        <img
                            src="{{ $pageUrl }}"
                            alt="صفحه {{ \App\Support\PersianUi::digits($index + 1) }} از فایل محافظت‌شده"
                            class="block h-auto w-full select-none"
                            loading="{{ $index < 2 ? 'eager' : 'lazy' }}"
                            decoding="async"
                            draggable="false"
                            referrerpolicy="no-referrer"
                        >
                        <figcaption class="border-t border-slate-100 px-3 py-2 text-center text-xs text-slate-500">
                            صفحه {{ \App\Support\PersianUi::digits($index + 1) }} از {{ \App\Support\PersianUi::digits($pageCount) }}
                        </figcaption>
                    </figure>
                @endforeach
            @endif
        </div>

        <footer class="border-t border-slate-200 bg-white p-4 text-xs leading-7 text-slate-600 sm:p-6">
            هر صفحه با شناسهٔ حساب و شمارهٔ سفارش واترمارک شده و بازشدن فایل/صفحات در لاگ امنیتی ثبت می‌شود. برای محافظت از حقوق محتوا، فایل اصلی و PDF قابل دانلود به مرورگر تحویل داده نمی‌شود.
            <span class="mt-1 block">واترمارک امکان ردیابی نسخهٔ منتشرشده را فراهم می‌کند؛ هیچ مرورگر عمومی نمی‌تواند اسکرین‌شات، عکس‌گرفتن با دستگاه دیگر یا دورزدن ابزارهای توسعه را به‌طور مطلق ناممکن کند.</span>
        </footer>
    </div>
</section>
<style>
    .secure-viewer { isolation:isolate; }
    .secure-viewer img { -webkit-user-drag:none; user-select:none; -webkit-user-select:none; }
    .secure-viewer__watermark {
        position:absolute; inset:0; z-index:20; pointer-events:none; overflow:hidden;
        display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); grid-auto-rows:minmax(90px,1fr);
        align-items:center; justify-items:center; opacity:.18;
    }
    .secure-viewer__watermark span {
        color:#4338ca; font-size:clamp(10px,1.5vw,16px); font-weight:900; letter-spacing:.02em;
        white-space:nowrap; transform:rotate(-24deg); text-shadow:0 1px 1px rgba(255,255,255,.85);
    }
    @media print { body * { visibility:hidden !important; } }
</style>
<script>
    (() => {
        const viewer = document.querySelector('[data-secure-viewer]');
        if (!viewer) return;
        viewer.addEventListener('contextmenu', event => event.preventDefault());
        viewer.addEventListener('copy', event => event.preventDefault());
        viewer.addEventListener('cut', event => event.preventDefault());
        viewer.addEventListener('dragstart', event => event.preventDefault());
        viewer.addEventListener('selectstart', event => event.preventDefault());
        document.addEventListener('keydown', event => {
            const key = String(event.key || '').toLowerCase();
            if ((event.ctrlKey || event.metaKey) && ['a','c','s','p','u'].includes(key)) {
                event.preventDefault();
            }
        }, true);
    })();
</script>
@endsection
