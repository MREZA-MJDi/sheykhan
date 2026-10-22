@extends('layouts.owner')

@section('title', 'حقوق مدرس‌ها | شیخان')
@section('header-title', 'حقوق مدرس‌ها')

@section('content')
<div class="space-y-6">
    <header><p class="text-xs font-black text-[var(--panel-primary)]">امور مالی مدرس‌ها</p><h1 class="mt-1 text-2xl font-black">ثبت و پیگیری پرداخت مدرس‌ها</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">این نسخه دفتر ثبت پرداخت است: ادمین/منشی مبلغ توافق‌شده را ثبت و وضعیت پرداخت را به‌روز می‌کند. سامانه فعلاً حقوق را خودکار از تعداد جلسه یا درصد فروش محاسبه نمی‌کند.</p></header>
    <section class="grid gap-3 sm:grid-cols-2">
        <article class="owner-stat-card"><span>پرداخت ثبت‌شده</span><strong>{{ number_format($summary['paid']) }}</strong><small>تومان</small></article>
        <article class="owner-stat-card"><span>در انتظار پرداخت</span><strong>{{ number_format($summary['pending']) }}</strong><small>تومان</small></article>
    </section>
    <section class="dashboard-panel p-5 sm:p-6">
        <h2 class="text-lg font-black">ثبت سند جدید</h2>
        @if($teachers->isEmpty())
            <p class="mt-3 rounded-xl bg-amber-50 p-4 text-sm leading-7 text-amber-900">مدرس فعالی در این آموزشگاه وجود ندارد.</p>
        @else
            <form method="POST" action="{{ route('owner.teacher-payments.store', $academy) }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <label class="grid gap-1 text-xs font-bold">مدرس<select name="teacher_id" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm"><option value="">انتخاب مدرس</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected((string) old('teacher_id') === (string) $teacher->id)>{{ $teacher->name }} — {{ $teacher->email }}</option>@endforeach</select>@error('teacher_id')<span class="text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 text-xs font-bold">شرح پرداخت<input name="title" value="{{ old('title', 'حق‌التدریس') }}" required maxlength="180" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm" placeholder="مثلاً حق‌التدریس مهر">@error('title')<span class="text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 text-xs font-bold">مبلغ (تومان)<input name="amount" type="number" min="1" max="1000000000000" step="1" value="{{ old('amount') }}" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">@error('amount')<span class="text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 text-xs font-bold">شروع دوره مالی<input name="period_start" type="date" value="{{ old('period_start') }}" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">@error('period_start')<span class="text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 text-xs font-bold">پایان دوره مالی<input name="period_end" type="date" value="{{ old('period_end') }}" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">@error('period_end')<span class="text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 text-xs font-bold">وضعیت اولیه<select name="status" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm"><option value="pending" @selected(old('status', 'pending') === 'pending')>در انتظار پرداخت</option><option value="paid" @selected(old('status') === 'paid')>پرداخت‌شده</option></select></label>
                <label class="grid gap-1 text-xs font-bold">شناسه رسید / پیگیری (اختیاری)<input name="reference" value="{{ old('reference') }}" maxlength="120" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm"></label>
                <label class="grid gap-1 text-xs font-bold md:col-span-2">یادداشت (اختیاری)<textarea name="notes" rows="2" maxlength="3000" class="rounded-xl border border-slate-200 px-3 py-2 text-sm leading-6">{{ old('notes') }}</textarea></label>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap items-center justify-between gap-3"><p class="text-xs leading-6 text-slate-500">پرداخت «قطعی» فقط زمانی انتخاب شود که پرداخت واقعاً انجام شده باشد.</p><button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ثبت سند پرداخت</button></div>
            </form>
        @endif
    </section>
    <section class="dashboard-panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-end sm:justify-between"><div><h2 class="font-black">تاریخچه سندهای پرداخت</h2><p class="mt-1 text-sm text-slate-500">فقط سندهای همین آموزشگاه</p></div>
            <form method="GET" class="flex gap-2"><select name="teacher_id" class="min-h-10 rounded-xl border border-slate-200 px-3 text-xs"><option value="">همه مدرس‌ها</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected((string) request('teacher_id') === (string) $teacher->id)>{{ $teacher->name }}</option>@endforeach</select><button class="min-h-10 rounded-xl border border-slate-200 px-3 text-xs font-bold">فیلتر</button></form>
        </div>
        <div class="overflow-x-auto"><table class="w-full min-w-[850px] text-right"><thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">مدرس / شرح</th><th class="px-4 py-3">دوره مالی</th><th class="px-4 py-3">مبلغ (تومان)</th><th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">ثبت‌کننده</th><th class="px-4 py-3">اقدام</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($payments as $payment)
                <tr><td class="px-4 py-4"><strong class="block text-sm">{{ $payment->teacher?->name }}</strong><span class="mt-1 block text-xs text-slate-500">{{ $payment->title }}</span>@if($payment->reference)<span class="mt-1 block text-xs text-slate-500">شناسه: {{ $payment->reference }}</span>@endif</td><td class="px-4 py-4 text-xs">{{ $payment->period_start ? \App\Support\PersianUi::date($payment->period_start) : '—' }} تا {{ $payment->period_end ? \App\Support\PersianUi::date($payment->period_end) : '—' }}</td><td class="px-4 py-4 text-sm font-black">{{ number_format($payment->amount) }}</td><td class="px-4 py-4 text-xs">{{ $payment->status === 'paid' ? 'پرداخت‌شده' : 'در انتظار پرداخت' }}</td><td class="px-4 py-4 text-xs">{{ $payment->recorder?->name ?: '—' }}</td><td class="px-4 py-4">@if($payment->status === 'pending')<form method="POST" action="{{ route('owner.teacher-payments.status', [$academy, $payment->id]) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="paid"><button class="min-h-9 rounded-lg bg-emerald-600 px-3 text-xs font-black text-white">ثبت پرداخت</button></form>@else<span class="text-xs text-emerald-700">{{ $payment->paid_at ? \App\Support\PersianUi::date($payment->paid_at) : 'قطعی' }}</span>@endif</td></tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">سندی ثبت نشده است.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="p-4">{{ $payments->links() }}</div>
    </section>
</div>
@endsection
