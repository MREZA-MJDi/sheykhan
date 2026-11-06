@extends('layouts.teacher')

@section('title', 'حقوق و پرداخت‌ها | شیخان')
@section('header-title', 'حقوق و پرداخت‌ها')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">سوابق مالی شخصی</span>
            <h1 class="teacher-workspace-title">حقوق و پرداخت‌های ثبت‌شده</h1>
            <p class="teacher-workspace-description">سندهای مالی ثبت‌شده توسط آموزشگاه برای حساب شما؛ وضعیت پرداخت و تاریخ تسویه را می‌توانید از این بخش بررسی کنید.</p>
        </div>
    </header>

    <section class="teacher-workspace-stats">
        <article class="teacher-workspace-stat">
            <span>کل پرداخت‌های قطعی</span>
            <strong>{{ \App\Support\PersianUi::number($summary['paid']) }} <small>تومان</small></strong>
            <small>سندهای تسویه‌شده و پرداخت‌شده</small>
        </article>
        <article class="teacher-workspace-stat">
            <span>مبلغ در انتظار پرداخت</span>
            <strong style="color:var(--or)">{{ \App\Support\PersianUi::number($summary['pending']) }} <small>تومان</small></strong>
            <small>سندهای ثبت‌شده و در انتظار تسویه</small>
        </article>
        <article class="teacher-workspace-stat">
            <span>تعداد کل اسناد</span>
            <strong>{{ \App\Support\PersianUi::digits($summary['total'] ?? $payments->total()) }} <small>سند</small></strong>
            <small>کل تراکنش‌های مالی حساب شما</small>
        </article>
    </section>

    <form method="GET" action="{{ route('teacher.payments.index') }}" class="teacher-workspace-filters">
        <label>
            <span>وضعیت پرداخت</span>
            <select name="status">
                <option value="">همه وضعیت‌ها</option>
                <option value="paid" @selected(request('status') === 'paid')>پرداخت‌شده (قطعی)</option>
                <option value="pending" @selected(request('status') === 'pending')>در انتظار پرداخت</option>
            </select>
        </label>
        <label style="flex:1;min-width:220px">
            <span>جستجو در شرح یا شناسه رسید</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="مثلاً: حق‌التدریس مهر، شماره پیگیری…">
        </label>
        <div style="display:flex;gap:8px;align-items:flex-end">
            <button type="submit" class="teacher-workspace-btn primary">اعمال فیلتر</button>
            @if(request()->hasAny(['status', 'search']))
                <a href="{{ route('teacher.payments.index') }}" class="teacher-workspace-btn secondary">پاک کردن</a>
            @endif
        </div>
    </form>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div>
                <h2>ریز اسناد پرداخت</h2>
                <p>فهرست کامل اسناد پرداختی آموزشگاه به همراه جزئیات دوره مالی، مبلغ و وضعیت تسویه</p>
            </div>
            <span class="teacher-workspace-chip">{{ \App\Support\PersianUi::digits($payments->total()) }} ردیف</span>
        </div>

        <div class="teacher-workspace-table-wrap">
            <table class="teacher-workspace-table teacher-payments-table">
                <thead>
                    <tr>
                        <th scope="col">شرح و شناسه پیگیری</th>
                        <th scope="col">آموزشگاه</th>
                        <th scope="col">دوره مالی</th>
                        <th scope="col">مبلغ تسویه</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">تاریخ پرداخت</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            <strong class="teacher-payment-title">{{ $payment->title }}</strong>
                            @if($payment->reference)
                                <span class="teacher-payment-ref">
                                    <i>شناسه رسید:</i>
                                    <code>{{ $payment->reference }}</code>
                                </span>
                            @endif
                            @if($payment->notes)
                                <p class="teacher-payment-notes">{{ $payment->notes }}</p>
                            @endif
                        </td>
                        <td>
                            <span class="teacher-payment-academy">{{ $payment->academy?->name ?: '—' }}</span>
                        </td>
                        <td>
                            <span class="teacher-payment-period">
                                @if($payment->period_start || $payment->period_end)
                                    {{ $payment->period_start ? \App\Support\PersianUi::date($payment->period_start) : '—' }}
                                    تا
                                    {{ $payment->period_end ? \App\Support\PersianUi::date($payment->period_end) : '—' }}
                                @else
                                    —
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="teacher-payment-amount">
                                <strong>{{ \App\Support\PersianUi::number($payment->amount) }}</strong>
                                <span>تومان</span>
                            </div>
                        </td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="teacher-workspace-chip success">پرداخت‌شده</span>
                            @else
                                <span class="teacher-workspace-chip warning">در انتظار پرداخت</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->paid_at)
                                <span class="teacher-payment-date">{{ \App\Support\PersianUi::date($payment->paid_at) }}</span>
                            @else
                                <span class="teacher-payment-pending">در انتظار تسویه</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="teacher-workspace-empty">
                                <strong>سند پرداختی یافت نشد.</strong>
                                <p>پس از ثبت سند پرداختی توسط آموزشگاه، جزئیات مالی در این جدول نمایش داده می‌شود.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="teacher-workspace-pagination">
                {{ $payments->links('components.navigation.pagination') }}
            </div>
        @endif
    </section>
</div>
@endsection
