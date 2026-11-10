@extends('layouts.owner')
@section('title','مالی آموزشگاه | شیخان')
@section('header-title','مالی')
@section('content')
<div class="space-y-6 panel-page-enter">
    @include('owner.partials.finance-workspace-tabs', ['academy' => $academy, 'activeSheet' => $sheet])
    <div><span class="text-xs font-black text-[var(--panel-primary)]">وضعیت مالی آموزشگاه</span><h1 class="mt-1 text-2xl font-black">دریافت‌ها و بدهی‌ها</h1><p class="mt-2 text-sm leading-7 text-slate-500">در این صفحه می‌توانی پرداخت‌های ثبت‌شده و شهریه‌های باقی‌مانده را ببینی.</p></div>
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <article class="owner-stat-card"><span>دریافت</span><strong>{{ \App\Support\PersianUi::number($stats['income']) }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>بازپرداخت</span><strong>{{ \App\Support\PersianUi::number($stats['refunds']) }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>خالص</span><strong>{{ \App\Support\PersianUi::number($stats['net']) }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>پرداخت موفق</span><strong>{{ \App\Support\PersianUi::digits($stats['completed']) }}</strong><small>ثبت‌شده</small></article>
        <article class="owner-stat-card"><span>بدهی شهریه</span><strong>{{ \App\Support\PersianUi::number($debtSummary['amount'] ?? 0) }}</strong><small>{{ \App\Support\PersianUi::digits($debtSummary['count'] ?? 0) }} ثبت‌نام با مانده · تومان</small></article>
    </section>
    <form method="GET" action="{{ route('owner.finance.index') }}" class="dashboard-panel flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <input type="hidden" name="sheet" value="{{ $sheet }}">
        <label class="grid flex-1 gap-1 text-xs font-bold"><span>{{ $sheet === 'debts' ? 'جست‌وجوی هنرجو یا دوره' : 'جست‌وجوی نام، دوره یا شماره پرداخت' }}</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="min-h-10 rounded-xl border border-slate-200 px-3 text-sm"></label>
        @if($sheet === 'transactions')
            <select name="status" aria-label="وضعیت تراکنش" class="min-h-10 rounded-xl border border-slate-200 px-3 text-xs"><option value="">همه وضعیت‌های پرداخت</option><option value="completed" @selected(($filters['status'] ?? '') === 'completed')>قطعی</option><option value="pending" @selected(($filters['status'] ?? '') === 'pending')>در انتظار</option><option value="failed" @selected(($filters['status'] ?? '') === 'failed')>ناموفق</option><option value="refunded" @selected(($filters['status'] ?? '') === 'refunded')>بازپرداخت‌شده</option></select>
            <select name="type" aria-label="نوع پرداخت تراکنش" class="min-h-10 rounded-xl border border-slate-200 px-3 text-xs"><option value="">همه نوع پرداخت پرداخت</option><option value="enrollment_payment" @selected(($filters['type'] ?? '') === 'enrollment_payment')>دریافت شهریه</option><option value="refund" @selected(($filters['type'] ?? '') === 'refund')>بازپرداخت</option></select>
        @endif
        <select name="sort" aria-label="مرتب‌سازی" class="min-h-10 rounded-xl border border-slate-200 px-3 text-xs">
            @if($sheet === 'debts')
                <option value="recent">جدیدترین</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>قدیمی‌ترین</option><option value="balance_desc" @selected(($filters['sort'] ?? '') === 'balance_desc')>بیشترین بدهی</option><option value="balance_asc" @selected(($filters['sort'] ?? '') === 'balance_asc')>کمترین بدهی</option><option value="name" @selected(($filters['sort'] ?? '') === 'name')>نام هنرجو</option>
            @else
                <option value="recent">جدیدترین</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>قدیمی‌ترین</option><option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>بیشترین مبلغ</option><option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>کمترین مبلغ</option>
            @endif
        </select>
        @if($sheet === 'transactions')
            <label class="grid gap-1 text-[10px] font-bold">از تاریخ<input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="min-h-10 rounded-xl border border-slate-200 px-2 text-xs"></label>
            <label class="grid gap-1 text-[10px] font-bold">تا تاریخ<input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="min-h-10 rounded-xl border border-slate-200 px-2 text-xs"></label>
        @endif
        <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-xs font-black text-white">نمایش نتیجه‌ها</button>
        <a href="{{ route('owner.finance.index', ['sheet' => $sheet]) }}" class="min-h-10 rounded-xl border border-slate-200 px-4 py-3 text-center text-xs font-bold">پاک‌کردن</a>
    </form>
    <section class="dashboard-panel owner-panel">
        <div class="owner-panel-head"><div><h2>{{ $sheet === 'debts' ? 'شهریه‌های دارای مانده' : 'پرداخت‌ها' }}</h2><p>{{ $sheet === 'debts' ? 'شهریه‌های باقی‌مانده هنرجویان این آموزشگاه' : 'پرداخت‌های آموزشگاه‌های شما' }}</p></div></div>
        @if($sheet === 'transactions')
        <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[720px] text-right"><thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">تاریخ پرداخت</th><th class="px-4 py-3">آموزشگاه</th><th class="px-4 py-3">نام پرداخت‌کننده</th><th class="px-4 py-3">دوره</th><th class="px-4 py-3">نوع پرداخت</th><th class="px-4 py-3">شماره پیگیری</th><th class="px-4 py-3">مبلغ</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($transactions as $tx)
            <tr><td class="px-4 py-3 text-xs">{{ \App\Support\PersianUi::dateTime($tx->occurred_at) }}</td><td class="px-4 py-3 text-xs font-bold">{{ $tx->academy?->name }}</td><td class="px-4 py-3 text-xs">{{ $tx->user?->name ?: '—' }}</td><td class="px-4 py-3 text-xs">{{ $tx->enrollment?->course?->title ?: '—' }}</td><td class="px-4 py-3 text-xs">{{ $tx->type === 'refund' ? 'بازپرداخت' : 'دریافت' }}</td><td class="px-4 py-3 text-xs" dir="ltr">{{ $tx->reference ?: '—' }}</td><td class="px-4 py-3 text-xs font-black">{{ \App\Support\PersianUi::number($tx->amount) }} تومان</td></tr>
        @empty
            <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">هنوز پرداختی برای نمایش وجود ندارد.</td></tr>
        @endforelse
        </tbody></table></div>
        <div class="mt-4">{{ $transactions->links() }}</div>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[760px] text-right">
                    <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">هنرجو</th><th class="px-4 py-3">دوره / کلاس</th><th class="px-4 py-3">شهریه</th><th class="px-4 py-3">پرداخت‌شده</th><th class="px-4 py-3">مانده</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($debts as $debt)
                        @php($remaining = max(0, (float) $debt->price_amount - (float) $debt->paid_amount))
                        <tr>
                            <td class="px-4 py-3 text-xs font-bold">{{ $debt->student?->name ?: 'هنرجوی نامشخص' }}<span class="mt-1 block text-slate-500">{{ $debt->student?->mobile }}</span></td>
                            <td class="px-4 py-3 text-xs">{{ $debt->course?->title ?: '—' }}<span class="mt-1 block text-slate-500">{{ $debt->classroom?->title ?: 'بدون کلاس مشخص' }}</span></td>
                            <td class="px-4 py-3 text-xs">{{ \App\Support\PersianUi::number($debt->price_amount) }} تومان</td>
                            <td class="px-4 py-3 text-xs">{{ \App\Support\PersianUi::number($debt->paid_amount) }} تومان</td>
                            <td class="px-4 py-3 text-xs font-black text-amber-700">{{ \App\Support\PersianUi::number($remaining) }} تومان</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">ثبت‌نام فعالی با مانده شهریه پیدا نشد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $debts->links() }}</div>
        @endif
    </section>
</div>
@endsection
