@extends('layouts.app')
@section('title', 'سفارش '.$order->order_number.' | شیخان')
@section('content')
<section class="public-store-page">
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <div class="mx-auto max-w-4xl space-y-6">
                @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
                @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                <header class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-8">
                    <span class="ui-eyebrow">سفارش شیخان</span>
                    <h1 class="mt-2 break-all text-2xl font-black text-[var(--color-text)] sm:text-3xl">{{ $order->order_number }}</h1>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-secondary)]">وضعیت سفارش:
                        <strong>{{ match($order->status) { 'pending' => 'در انتظار رسید/بررسی', 'paid' => 'پرداخت تأییدشده', 'payment_failed' => 'پرداخت ردشده', 'cancelled' => 'لغوشده', default => $order->status } }}</strong>
                    </p>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-[var(--color-background-soft)] p-4"><span class="text-xs text-[var(--color-text-muted)]">مبلغ</span><strong class="mt-1 block text-xl font-black">{{ \App\Support\PersianUi::money($order->total) }}</strong></div>
                        <div class="rounded-xl bg-[var(--color-background-soft)] p-4"><span class="text-xs text-[var(--color-text-muted)]">ثبت سفارش</span><strong class="mt-1 block text-sm">{{ \App\Support\PersianUi::date($order->created_at) }}</strong></div>
                    </div>
                </header>

                <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-8">
                    <h2 class="text-lg font-black text-[var(--color-text)]">اطلاعات واریز</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-[var(--color-background-soft)] p-4"><span class="text-xs text-[var(--color-text-muted)]">بانک</span><strong class="mt-1 block">{{ $bank['bank_name'] ?? 'تنظیم نشده' }}</strong></div>
                        <div class="rounded-xl bg-[var(--color-background-soft)] p-4"><span class="text-xs text-[var(--color-text-muted)]">به نام</span><strong class="mt-1 block">{{ $bank['account_holder'] ?? 'تنظیم نشده' }}</strong></div>
                        <div class="rounded-xl bg-[var(--color-background-soft)] p-4 sm:col-span-2"><span class="text-xs text-[var(--color-text-muted)]">شبا</span><strong dir="ltr" class="mt-1 block break-all text-left">{{ $bank['iban'] ?? 'تنظیم نشده' }}</strong></div>
                    </div>
                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-secondary)]">مبلغ را دقیقاً برابر مبلغ سفارش واریز کن و شماره سفارش را در شرح واریز قرار بده. رسید فقط برای بررسی است؛ تأیید نهایی پس از تطبیق واقعی در گردش حساب بانکی انجام می‌شود.</p>
                    @if($order->status === 'pending' && $payment?->status === 'pending')
                        <form method="POST" enctype="multipart/form-data" action="{{ route('orders.proof.store', $order) }}" class="mt-6 grid gap-4 rounded-2xl border border-[var(--color-border)] p-4 sm:p-5">
                            @csrf
                            <label class="grid gap-2 text-sm font-bold"><span>رسید واریز (PDF / JPG / PNG / WebP، حداکثر ۱۲ مگابایت)</span><input type="file" name="proof" required accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"></label>
                            @if($payment->proof_uploaded_at)
                                <p class="text-xs text-[var(--color-text-muted)]">آخرین رسید در {{ \App\Support\PersianUi::date($payment->proof_uploaded_at) }} ثبت شده است.</p>
                            @endif
                            <button class="min-h-11 rounded-xl bg-[var(--color-primary-600)] px-4 text-sm font-black text-white">بارگذاری رسید خصوصی</button>
                        </form>
                    @elseif($order->status === 'paid')
                        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">پرداخت تأیید شد. دسترسی فایل‌های مجاز و/یا دوره‌ی سفارش مطابق نوع محصول فعال شده است.</div>
                    @else
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">سفارش در وضعیت فعال برای دریافت رسید نیست. برای پیگیری با پشتیبانی شیخان تماس بگیر.</div>
                    @endif
                </section>

                <section class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-8">
                    <h2 class="text-lg font-black text-[var(--color-text)]">محصولات سفارش</h2>
                    <div class="mt-4 space-y-3">
                        @foreach($order->items as $item)
                                                        <article class="order-line-card rounded-2xl border border-[var(--color-border)] p-4 sm:p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <strong class="break-words">{{ $item->product_title_snapshot }}</strong>
                                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">{{ \\App\\Support\\PersianUi::money($item->total_price) }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-bold {{ $order->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                        {{ $order->status === 'paid' ? 'دسترسی فعال' : 'قفل تا تأیید پرداخت' }}
                                    </span>
                                </div>

                                @if($item->course)
                                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-secondary)]">
                                        @if($order->status === 'paid')
                                            دوره برای حساب «{{ $item->beneficiary?->name ?: $order->buyer?->name }}» فعال شد. ویدئوها فقط در فضای امن درس‌ها قابل مشاهده‌اند و لینک دانلود عمومی ندارند.
                                        @else
                                            پس از تأیید واقعی پرداخت، دوره برای «{{ $item->beneficiary?->name ?: $order->buyer?->name }}» به کتابخانه‌ی آموزشی اضافه می‌شود.
                                        @endif
                                    </p>
                                    @if($order->status === 'paid')
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if(auth()->user()?->hasRole('student') && (int) $item->beneficiary_id === (int) auth()->id())
                                                <a href="{{ route('student.courses.show', $item->course) }}" class="order-primary-action inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-4 text-xs font-black text-white">شروع یادگیری <span class="ms-2" aria-hidden="true">←</span></a>
                                            @elseif(auth()->user()?->hasRole('parent'))
                                                <a href="{{ route('parent.dashboard') }}" class="order-primary-action inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-4 text-xs font-black text-white">پیگیری مسیر فرزند <span class="ms-2" aria-hidden="true">←</span></a>
                                            @endif
                                            <a href="{{ route('courses.show', $item->course) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--color-border)] px-4 text-xs font-bold text-[var(--color-text)]">جزئیات دوره</a>
                                        </div>
                                    @endif
                                @elseif($item->product && $order->status === 'paid')
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @forelse($item->product->files->where('is_preview', false) as $downloadFile)
                                            <a href="{{ route('orders.files.download', [$order, $downloadFile]) }}" class="order-primary-action inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-4 text-xs font-black text-white">دریافت نسخهٔ محافظت‌شده <span class="ms-2" aria-hidden="true">↓</span></a>
                                        @empty
                                            <p class="text-xs leading-6 text-amber-800">هنوز فایل قابل تحویل برای این محصول ثبت نشده است. پشتیبان را مطلع کن.</p>
                                        @endforelse
                                    </div>
                                @elseif($item->product)
                                    <p class="mt-3 text-xs leading-6 text-[var(--color-text-muted)]">فایل‌های محصول تا تأیید واقعی پرداخت قفل می‌مانند؛ بارگذاری رسید به‌تنهایی کافی نیست.</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </x-layout.container>
    </x-layout.section>
</section>
@endsection
