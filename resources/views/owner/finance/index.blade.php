@extends('layouts.owner')
@section('title','مالی آموزشگاه | شیخان')
@section('header-title','مالی')
@section('content')
<div class="space-y-6 panel-page-enter">
    <div><span class="text-xs font-black text-[var(--panel-primary)]">گزارش مالی</span><h1 class="mt-1 text-2xl font-black">جریان مالی آموزشگاه</h1><p class="mt-2 text-sm leading-7 text-slate-500">این صفحه فقط گزارش می‌دهد؛ ledger اصلی فقط از جریان پرداخت معتبر تغذیه می‌شود.</p></div>
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="owner-stat-card"><span>دریافت</span><strong>{{ number_format($stats['income'],0,'.',',') }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>بازپرداخت</span><strong>{{ number_format($stats['refunds'],0,'.',',') }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>خالص</span><strong>{{ number_format($stats['net'],0,'.',',') }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>تراکنش قطعی</span><strong>{{ number_format($stats['completed']) }}</strong><small>ثبت‌شده</small></article>
    </section>
    <section class="dashboard-panel owner-panel">
        <div class="owner-panel-head"><div><h2>تراکنش‌ها</h2><p>فقط آموزشگاه‌های متعلق به این Owner</p></div></div>
        <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[720px] text-right"><thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">زمان</th><th class="px-4 py-3">آموزشگاه</th><th class="px-4 py-3">کاربر</th><th class="px-4 py-3">دوره</th><th class="px-4 py-3">نوع</th><th class="px-4 py-3">مبلغ</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($transactions as $tx)
            <tr><td class="px-4 py-3 text-xs">{{ $tx->occurred_at?->format('Y/m/d H:i') }}</td><td class="px-4 py-3 text-xs font-bold">{{ $tx->academy?->name }}</td><td class="px-4 py-3 text-xs">{{ $tx->user?->name ?: '—' }}</td><td class="px-4 py-3 text-xs">{{ $tx->enrollment?->course?->title ?: '—' }}</td><td class="px-4 py-3 text-xs">{{ $tx->type === 'refund' ? 'بازپرداخت' : 'دریافت' }}</td><td class="px-4 py-3 text-xs font-black">{{ number_format((float)$tx->amount,0,'.',',') }}</td></tr>
        @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">تراکنشی ثبت نشده است.</td></tr>
        @endforelse
        </tbody></table></div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </section>
</div>
@endsection
