@extends('layouts.teacher')

@section('title', 'حقوق و پرداخت‌ها | شیخان')
@section('header-title', 'حقوق و پرداخت‌ها')

@section('content')
<div class="space-y-6">
    <header><p class="text-xs font-black text-[var(--panel-primary)]">سوابق مالی شخصی</p><h1 class="mt-1 text-2xl font-black">حقوق و پرداخت‌های ثبت‌شده</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">این صفحه سندهایی را نشان می‌دهد که آموزشگاه برای حساب شما ثبت کرده است. مبلغ، محاسبه‌ی خودکار حقوق بر اساس تعداد جلسات نیست؛ شیوه‌ی محاسبه باید طبق توافق آموزشگاه جداگانه تعیین شود.</p></header>
    <section class="grid gap-3 sm:grid-cols-2">
        <article class="owner-stat-card"><span>جمع پرداخت‌های قطعی این صفحه</span><strong>{{ number_format($summary['paid']) }}</strong><small>تومان · سندهای پرداخت‌شده در این صفحه</small></article>
        <article class="owner-stat-card"><span>مبلغ در انتظار پرداخت</span><strong>{{ number_format($summary['pending']) }}</strong><small>بر اساس سندهای ثبت‌شده برای حساب شما</small></article>
    </section>
    <section class="dashboard-panel overflow-hidden">
        <div class="border-b border-slate-100 p-5"><h2 class="font-black">ریز پرداخت‌ها</h2><p class="mt-1 text-sm text-slate-500">فقط پرداخت‌های متعلق به حساب خودتان در این فهرست می‌آیند.</p></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-right"><thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">شرح</th><th class="px-4 py-3">آموزشگاه</th><th class="px-4 py-3">دوره مالی</th><th class="px-4 py-3">مبلغ (تومان)</th><th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">تاریخ پرداخت</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($payments as $payment)
                <tr><td class="px-4 py-4"><strong class="block text-sm">{{ $payment->title }}</strong>@if($payment->reference)<span class="mt-1 block text-xs text-slate-500">شناسه: {{ $payment->reference }}</span>@endif @if($payment->notes)<span class="mt-1 block max-w-sm text-xs leading-6 text-slate-500">{{ $payment->notes }}</span>@endif</td><td class="px-4 py-4 text-sm">{{ $payment->academy?->name }}</td><td class="px-4 py-4 text-xs">{{ $payment->period_start ? \App\Support\PersianUi::date($payment->period_start) : '—' }} تا {{ $payment->period_end ? \App\Support\PersianUi::date($payment->period_end) : '—' }}</td><td class="px-4 py-4 text-sm font-black">{{ number_format($payment->amount) }}</td><td class="px-4 py-4 text-xs"><span class="rounded-full px-2.5 py-1.5 {{ $payment->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">{{ $payment->status === 'paid' ? 'پرداخت‌شده' : 'در انتظار پرداخت' }}</span></td><td class="px-4 py-4 text-xs">{{ $payment->paid_at ? \App\Support\PersianUi::date($payment->paid_at) : '—' }}</td></tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center"><p class="font-bold">سند پرداختی ثبت نشده است.</p><p class="mt-2 text-sm text-slate-500">پس از ثبت سند از سوی ادمین/منشی، جزئیات اینجا نمایش داده می‌شود.</p></td></tr>
            @endforelse
        </tbody></table></div>
        <div class="p-4">{{ $payments->links() }}</div>
    </section>
</div>
@endsection
