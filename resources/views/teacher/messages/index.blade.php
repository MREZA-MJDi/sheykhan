@extends('layouts.teacher')

@section('title', 'پیام‌های اولیا | شیخان')
@section('header-title', 'پیام‌های اولیا')

@section('content')
<div class="space-y-6">
    <header><p class="text-xs font-black text-[var(--panel-primary)]">ارتباط با خانواده</p><h1 class="mt-1 text-2xl font-black">پیام‌های مرتبط با کلاس‌های شما</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">فقط پیام‌های مربوط به دانش‌آموزان کلاس‌هایی که به شما تخصیص دارند نمایش داده می‌شوند. پاسخ رسمی و بستن درخواست از کارتابل ادمین/منشی انجام می‌شود تا پیگیری یکپارچه بماند.</p></header>
    <section class="space-y-4">
        @php($statusLabels = ['open' => 'در انتظار بررسی', 'in_review' => 'در حال پیگیری', 'responded' => 'پاسخ داده‌شده', 'closed' => 'بسته‌شده'])
        @php($categoryLabels = ['question' => 'پرسش', 'suggestion' => 'پیشنهاد', 'complaint' => 'انتقاد یا شکایت', 'other' => 'سایر'])
        @forelse($messages as $message)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $message->academy?->name }} · {{ $message->student?->name }}</p><h2 class="mt-1 text-lg font-black">{{ $message->subject }}</h2></div><span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $statusLabels[$message->status] ?? $message->status }}</span></div>
                <p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-700">{{ $message->body }}</p>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500"><span>{{ $message->parent?->name }}</span><span>{{ $categoryLabels[$message->category] ?? 'پیام' }}</span><span>{{ \App\Support\PersianUi::date($message->created_at) }}</span></div>
                @if($message->response)<div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4"><p class="text-xs font-black text-emerald-800">پاسخ نهایی آموزشگاه</p><p class="mt-2 whitespace-pre-line text-sm leading-7 text-emerald-950">{{ $message->response }}</p></div>@endif
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center"><h2 class="font-black">پیامی برای کلاس‌های شما ثبت نشده است.</h2><p class="mt-2 text-sm leading-7 text-slate-500">در صورت ثبت پیام از سوی والدین دانش‌آموزان کلاس شما، پیام اینجا نمایش داده می‌شود.</p></div>
        @endforelse
        <div>{{ $messages->links() }}</div>
    </section>
</div>
@endsection
