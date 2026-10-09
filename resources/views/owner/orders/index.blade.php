@extends('layouts.owner')
@section('title', 'بازبینی سفارش‌ها | شیخان')
@section('header-title', 'بازبینی سفارش‌ها')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div><span>فروشگاه آموزشی</span><h1>سفارش‌ها و رسیدهای واریز</h1><p>رسید فقط پس از تطبیق با صورتحساب واقعی بانک تأیید شود. ثبت رسید به تنهایی مجوز دانلود نیست.</p></div>
        <a href="{{ route('owner.dashboard') }}">داشبورد</a>
    </header>
    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="owner-form-section space-y-4">
        <h2>سفارش‌های آموزشگاه {{ $academy->name }}</h2>
        @forelse($orders as $order)
            @php($payment = $order->payments->sortByDesc('id')->first())
            <article class="space-y-4 rounded-2xl border border-slate-200 p-4 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div><strong class="break-all">{{ $order->order_number }}</strong><p class="mt-1 text-xs text-slate-500">{{ $order->buyer?->name }} · {{ $order->buyer?->mobile }}</p><p class="mt-1 text-xs text-slate-500">{{ $order->items->pluck('product_title_snapshot')->join('، ') }}</p></div>
                    <div class="text-left"><strong class="block">{{ \App\Support\PersianUi::money($order->total) }}</strong><span class="mt-1 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{ match($order->status) {'pending' => 'در انتظار', 'paid' => 'پرداخت‌شده', 'payment_failed' => 'رد پرداخت', 'cancelled' => 'لغو', default => $order->status} }}</span></div>
                </div>
                @if($payment)
                    <div class="grid gap-2 text-xs text-slate-600 sm:grid-cols-3">
                        <div class="rounded-lg bg-slate-50 p-3">وضعیت رسید: <strong>{{ $payment->proof_uploaded_at ? 'ارسال شده' : 'ارسال نشده' }}</strong></div>
                        <div class="rounded-lg bg-slate-50 p-3">کد رهگیری: <strong>{{ $payment->tracking_code ?: 'ثبت نشده' }}</strong></div>
                        <div class="rounded-lg bg-slate-50 p-3">زمان رسید: <strong>{{ $payment->proof_uploaded_at?->format('Y-m-d H:i') ?? '—' }}</strong></div>
                    </div>
                    @if($order->status === 'pending' && $payment->status === 'pending' && $payment->proof_media_id)
                        <div class="grid gap-4 border-t border-slate-200 pt-4 lg:grid-cols-2">
                            <div class="space-y-2">
                                <h3 class="font-bold">تطبیق با حساب بانکی</h3>
                                <a target="_blank" rel="noopener noreferrer" href="{{ route('owner.orders.proof', [$academy, $order, $payment]) }}" class="inline-flex min-h-10 items-center rounded-xl border border-slate-300 px-4 text-xs font-bold">باز کردن رسید خصوصی</a>
                                <p class="text-xs leading-6 text-slate-500">قبل از تأیید، شماره سفارش، مبلغ، تاریخ واریز و کد پیگیری را با گردش حساب رسمی مطابقت بده.</p>
                            </div>
                            <form method="POST" action="{{ route('owner.orders.confirm', [$academy, $order, $payment]) }}" class="space-y-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                                @csrf
                                <h3 class="font-bold text-emerald-900">تأیید دریافت وجه</h3>
                                <label class="grid gap-1 text-xs font-bold"><span>کد رهگیری واقعی بانک</span><input name="tracking_code" maxlength="128" required class="min-h-10 rounded-lg border border-slate-200 bg-white px-3"></label>
                                <label class="grid gap-1 text-xs font-bold"><span>یادداشت بررسی (اختیاری)</span><textarea name="review_note" rows="2" maxlength="2000" class="rounded-lg border border-slate-200 bg-white p-3"></textarea></label>
                                <label class="flex items-start gap-2 text-xs leading-6"><input type="checkbox" name="bank_statement_checked" value="1" required class="mt-1"><span>تأیید می‌کنم واریز را در گردش حساب بانک دیده‌ام و مبلغ دقیق سفارش دریافت شده است.</span></label>
                                <button class="min-h-10 w-full rounded-xl bg-emerald-700 px-4 text-xs font-black text-white">تأیید وجه و فعال‌سازی محصول</button>
                            </form>
                            <form method="POST" action="{{ route('owner.orders.reject', [$academy, $order, $payment]) }}" class="space-y-3 rounded-xl border border-rose-200 p-4">
                                @csrf
                                <h3 class="font-bold text-rose-900">رد رسید</h3>
                                <label class="grid gap-1 text-xs font-bold"><span>دلیل رد</span><textarea name="review_note" rows="2" required maxlength="2000" class="rounded-lg border border-slate-200 p-3"></textarea></label>
                                <label class="flex items-start gap-2 text-xs leading-6"><input type="checkbox" name="bank_statement_checked" value="1" required class="mt-1"><span>رسید یا تراکنش را بررسی کرده‌ام و دلیل رد را ثبت می‌کنم.</span></label>
                                <button class="min-h-10 w-full rounded-xl border border-rose-300 px-4 text-xs font-black text-rose-800">رد رسید، بدون دسترسی محصول</button>
                            </form>
                        </div>
                    @elseif($payment->review_note)
                        <p class="rounded-lg bg-slate-50 p-3 text-xs leading-6 text-slate-600">یادداشت بررسی: {{ $payment->review_note }}</p>
                    @endif
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">هنوز سفارشی ثبت نشده است.</div>
        @endforelse
        @if($orders->hasPages())<div>{{ $orders->links() }}</div>@endif
    </section>
</div>
@endsection
