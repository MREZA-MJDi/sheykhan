@php
    $financeUser = auth()->user();
    $financeAcademy = $academy ?? null;
    $financeSheets = [
        ['key' => 'transactions', 'label' => 'تراکنش‌ها و گردش مالی', 'permission' => 'finance.view', 'route' => 'owner.finance.index', 'scope' => 'global'],
        ['key' => 'debts', 'label' => 'شهریه و بدهی‌ها', 'permission' => 'finance.view', 'route' => 'owner.finance.index', 'scope' => 'global', 'query' => ['sheet' => 'debts']],
        ['key' => 'orders', 'label' => 'پرداخت سفارش‌ها و رسیدها', 'permission' => 'orders.view', 'route' => 'owner.orders.index', 'scope' => 'academy'],
        ['key' => 'teacher-payments', 'label' => 'تسویه مدرس‌ها', 'permission' => 'teacher_payments.view', 'route' => 'owner.teacher-payments.index', 'scope' => 'academy'],
    ];
@endphp
<section class="mb-5 overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-l from-emerald-50 via-white to-teal-50 p-4 sm:p-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-[11px] font-black text-emerald-800">بخش مالی</p><h2 class="mt-1 text-lg font-black text-slate-900">دریافت‌ها، رسیدها و تسویه‌ها</h2><p class="mt-1 text-xs leading-6 text-slate-600">در این بخش می‌توانی پرداخت‌ها، بدهی‌ها و تسویه‌های آموزشگاه را پیگیری کنی.</p></div>
        @if($financeAcademy)<span class="inline-flex min-h-9 items-center rounded-xl border border-emerald-100 bg-white/80 px-3 text-xs font-bold text-emerald-900">{{ $financeAcademy->name }}</span>@endif
    </div>
    <nav class="mt-4 flex flex-wrap gap-2" aria-label="بخش‌های مالی">
        @foreach($financeSheets as $sheet)
            @if($financeUser?->hasPermission($sheet['permission']) && ($sheet['scope'] === 'global' || $financeAcademy))
                <a href="{{ $sheet['scope'] === 'global' ? route($sheet['route'], $sheet['query'] ?? []) : route($sheet['route'], $financeAcademy) }}"
                   @if(($activeSheet ?? null) === $sheet['key']) aria-current="page" @endif
                   class="inline-flex min-h-10 items-center rounded-xl px-4 text-xs font-black transition {{ ($activeSheet ?? null) === $sheet['key'] ? 'bg-emerald-700 text-white shadow-sm' : 'border border-emerald-100 bg-white text-slate-700 hover:border-emerald-300 hover:text-emerald-800' }}">{{ $sheet['label'] }}</a>
            @endif
        @endforeach
    </nav>
</section>
