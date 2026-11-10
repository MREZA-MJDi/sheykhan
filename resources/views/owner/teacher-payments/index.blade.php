@extends('layouts.owner')

@section('title', 'حقوق مدرس‌ها | شیخان')
@section('header-title', 'حقوق مدرس‌ها')

@section('content')
<div class="space-y-6 panel-page-enter owner-workspace-page owner-teacher-payments-page">
    @include('owner.partials.finance-workspace-tabs', ['academy' => $academy, 'activeSheet' => 'teacher-payments'])
    <header><p class="text-xs font-black text-[var(--panel-primary)]">امور مالی مدرس‌ها</p><h1 class="mt-1 text-2xl font-black">ثبت و پیگیری پرداخت مدرس‌ها</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">در این بخش مبلغ توافق‌شده با هر مدرس را ثبت می‌کنی و وضعیت پرداخت را پیگیری می‌کنی. مبلغ پرداختی فعلاً به‌صورت دستی وارد می‌شود و سامانه آن را از تعداد جلسه یا فروش محاسبه نمی‌کند.</p></header>
    <section class="grid gap-3 sm:grid-cols-2">
        <article class="owner-stat-card"><span>پرداخت ثبت‌شده</span><strong>{{ \App\Support\PersianUi::number($summary['paid']) }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>در انتظار پرداخت</span><strong>{{ \App\Support\PersianUi::number($summary['pending']) }}</strong><small>تومان</small></article>
    </section>
    <section class="dashboard-panel p-5 sm:p-6">
        <h2 class="text-lg font-black">ثبت سند جدید</h2>
        @if($canManagePayments)
            @if(!$hasActiveTeachers)
            <p class="mt-3 rounded-xl bg-amber-50 p-4 text-sm leading-7 text-amber-900">مدرس فعالی در این آموزشگاه وجود ندارد.</p>
        @else
            <form method="POST" action="{{ route('owner.teacher-payments.store', $academy) }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <x-academy-option-picker
                    id="owner-teacher-payment-teacher"
                    label="جست‌وجوی مدرس"
                    name="teacher_id"
                    type="teacher-payment-teachers"
                    endpoint="{{ route('owner.form-options', $academy) }}"
                    empty-label="انتخاب مدرس"
                    search-placeholder="نام یا ایمیل مدرس…"
                    status-text="برای پیدا کردن مدرس، نام یا ایمیل او را جست‌وجو کن."
                    more-label="مدرس‌های بیشتر"
                    select-label="انتخاب مدرس برای ثبت پرداخت"
                    help-text="فقط مدرس‌های فعال همین آموزشگاه نمایش داده می‌شوند."
                    error-key="teacher_id"
                    required
                    wrapper-class="grid gap-2">
                    @foreach($formTeachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) old('teacher_id') === (string) $teacher->id)>{{ $teacher->name }} — {{ $teacher->email }}</option>
                    @endforeach
                </x-academy-option-picker>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200"><span>شرح پرداخت</span><input name="title" value="{{ old('title', 'حق‌التدریس') }}" required maxlength="180" class="min-h-11 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 text-sm focus:border-[var(--panel-primary)] focus:ring-2 focus:ring-[var(--panel-primary)]/20 outline-none transition-all" placeholder="مثلاً حق‌التدریس مهر">@error('title')<span class="text-rose-600 font-normal">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200"><span>مبلغ (تومان)</span><input name="amount" type="number" data-money-input data-money-unit="تومان" min="1" max="1000000000000" step="1" value="{{ old('amount') }}" required class="min-h-11 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 text-sm focus:border-[var(--panel-primary)] focus:ring-2 focus:ring-[var(--panel-primary)]/20 outline-none transition-all">@error('amount')<span class="text-rose-600 font-normal">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200"><span>شروع دوره مالی</span><input name="period_start" type="date" value="{{ old('period_start') }}" class="min-h-11 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 text-sm focus:border-[var(--panel-primary)] focus:ring-2 focus:ring-[var(--panel-primary)]/20 outline-none transition-all">@error('period_start')<span class="text-rose-600 font-normal">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200"><span>پایان دوره مالی</span><input name="period_end" type="date" value="{{ old('period_end') }}" class="min-h-11 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 text-sm focus:border-[var(--panel-primary)] focus:ring-2 focus:ring-[var(--panel-primary)]/20 outline-none transition-all">@error('period_end')<span class="text-rose-600 font-normal">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200"><span>وضعیت اولیه</span><select name="status" class="min-h-11 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 text-sm focus:border-[var(--panel-primary)] focus:ring-2 focus:ring-[var(--panel-primary)]/20 outline-none transition-all"><option value="pending" @selected(old('status', 'pending') === 'pending')>در انتظار پرداخت</option><option value="paid" @selected(old('status') === 'paid')>پرداخت‌شده</option></select></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200"><span>شناسه رسید / پیگیری (اختیاری)</span><input name="reference" value="{{ old('reference') }}" maxlength="120" class="min-h-11 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 text-sm focus:border-[var(--panel-primary)] focus:ring-2 focus:ring-[var(--panel-primary)]/20 outline-none transition-all" placeholder="مثلاً: شماره تراکنش یا رسید بانک"></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 md:col-span-2"><span>یادداشت (اختیاری)</span><textarea name="notes" rows="2" maxlength="3000" class="rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm leading-6 focus:border-[var(--panel-primary)] focus:ring-2 focus:ring-[var(--panel-primary)]/20 outline-none transition-all" placeholder="توضیحات و توافقات پرداخت…">{{ old('notes') }}</textarea></label>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap items-center justify-between gap-3 pt-2"><p class="text-xs leading-6 text-slate-500">پرداخت «قطعی» فقط زمانی انتخاب شود که پرداخت واقعاً انجام شده باشد.</p><button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white hover:opacity-95 shadow-md shadow-[var(--panel-primary)]/20 transition-all">ثبت سند پرداخت</button></div>
            </form>
            @endif
        @else
            <p class="mt-3 rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:bg-slate-800 dark:text-slate-300">برای ثبت سند پرداخت یا تغییر وضعیت پرداخت‌ها، دسترسی مدیریت پرداخت مدرس لازم است.</p>
        @endif
    </section>
    <section class="dashboard-panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 dark:border-slate-700/60 p-5 sm:flex-row sm:items-end sm:justify-between"><div><h2 class="font-black">تاریخچه سندهای پرداخت</h2><p class="mt-1 text-sm text-slate-500">فقط سندهای همین آموزشگاه</p></div>
            <form method="GET" class="flex flex-wrap items-end gap-2">
                <x-academy-option-picker
                    id="owner-teacher-payment-filter"
                    label="جست‌وجوی مدرس"
                    name="teacher_id"
                    type="teacher-payment-teachers"
                    endpoint="{{ route('owner.form-options', $academy) }}"
                    empty-label="همه مدرس‌ها"
                    search-placeholder="نام مدرس…"
                    status-text="برای فیلتر، نام مدرس را جست‌وجو کن."
                    more-label="مدرس‌های بیشتر"
                    select-label="فیلتر بر اساس مدرس"
                    wrapper-class="grid min-w-[220px] flex-1 gap-2"
                    label-class="text-[11px] font-bold"
                    status-class="text-[10px] text-slate-500"
                    more-class="w-fit rounded-lg border border-slate-300 px-2 py-1 text-[10px] font-bold">
                    @foreach($filterTeachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) request('teacher_id') === (string) $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </x-academy-option-picker>
                <button class="min-h-10 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 px-4 text-xs font-bold hover:bg-slate-100">فیلتر</button>
            </form>
        </div>
        <div class="overflow-x-auto"><table class="w-full min-w-[850px] text-right"><thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">مدرس / شرح</th><th class="px-4 py-3">دوره مالی</th><th class="px-4 py-3">مبلغ (تومان)</th><th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">ثبت‌کننده</th><th class="px-4 py-3">اقدام</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($payments as $payment)
                <tr><td class="px-4 py-4"><strong class="block text-sm">{{ $payment->teacher?->name }}</strong><span class="mt-1 block text-xs text-slate-500">{{ $payment->title }}</span>@if($payment->reference)<span class="mt-1 block text-xs text-slate-500">شناسه: {{ $payment->reference }}</span>@endif</td><td class="px-4 py-4 text-xs">{{ $payment->period_start ? \App\Support\PersianUi::date($payment->period_start) : '—' }} تا {{ $payment->period_end ? \App\Support\PersianUi::date($payment->period_end) : '—' }}</td><td class="px-4 py-4 text-sm font-black">{{ \App\Support\PersianUi::number($payment->amount) }}</td><td class="px-4 py-4 text-xs">{{ $payment->status === 'paid' ? 'پرداخت‌شده' : 'در انتظار پرداخت' }}</td><td class="px-4 py-4 text-xs">{{ $payment->recorder?->name ?: '—' }}</td><td class="px-4 py-4">@if($payment->status === 'pending')@if($canManagePayments)<form method="POST" action="{{ route('owner.teacher-payments.status', [$academy, $payment->id]) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="paid"><button class="min-h-9 rounded-lg bg-emerald-600 px-3 text-xs font-black text-white">ثبت پرداخت</button></form>@else<span class="text-xs text-amber-700">در انتظار پرداخت</span>@endif @else<span class="text-xs text-emerald-700">{{ $payment->paid_at ? \App\Support\PersianUi::date($payment->paid_at) : 'قطعی' }}</span>@endif</td></tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">سندی ثبت نشده است.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="p-4">{{ $payments->links() }}</div>
    </section>
</div>
@endsection
