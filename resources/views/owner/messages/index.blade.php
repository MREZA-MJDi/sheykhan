@extends('layouts.owner')

@section('title', 'پیام‌های اولیا | شیخان')
@section('header-title', 'پیام‌های اولیا')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-black text-[var(--panel-primary)]">پیگیری ارتباط خانواده</p><h1 class="mt-1 text-2xl font-black">پیام‌ها، انتقادها و پیشنهادها</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">هر پیام متعلق به همین آموزشگاه است. پاسخ‌ها در پنل والد دیده می‌شوند و وضعیت برای پیگیری شفاف می‌ماند.</p></div>
        <a href="{{ route('owner.dashboard') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold">بازگشت به داشبورد</a>
    </header>

    <section class="grid gap-3 sm:grid-cols-3">
        <article class="owner-stat-card"><span>در انتظار بررسی</span><strong>{{ \App\Support\PersianUi::digits($stats['open']) }}</strong><small>پیام باز</small></article>
        <article class="owner-stat-card"><span>در حال پیگیری</span><strong>{{ \App\Support\PersianUi::digits($stats['in_review']) }}</strong><small>نیازمند اقدام</small></article>
        <article class="owner-stat-card"><span>پاسخ‌داده‌شده</span><strong>{{ \App\Support\PersianUi::digits($stats['responded']) }}</strong><small>دارای پاسخ</small></article>
    </section>

    <form method="GET" class="dashboard-panel grid gap-3 p-4 sm:grid-cols-[1fr_180px_180px_auto] sm:items-end">
        <label class="grid gap-1 text-xs font-bold">جست‌وجو<input name="q" value="{{ $filters['q'] ?? '' }}" class="min-h-10 rounded-xl border border-slate-200 px-3 text-sm" placeholder="عنوان، متن یا نام"></label>
        <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-10 rounded-xl border border-slate-200 px-3 text-sm"><option value="">همه وضعیت‌ها</option><option value="open" @selected(($filters['status'] ?? '') === 'open')>در انتظار بررسی</option><option value="in_review" @selected(($filters['status'] ?? '') === 'in_review')>در حال پیگیری</option><option value="responded" @selected(($filters['status'] ?? '') === 'responded')>پاسخ‌داده‌شده</option><option value="closed" @selected(($filters['status'] ?? '') === 'closed')>بسته‌شده</option></select></label>
        <label class="grid gap-1 text-xs font-bold">نوع پیام<select name="category" class="min-h-10 rounded-xl border border-slate-200 px-3 text-sm"><option value="">همه انواع</option><option value="question" @selected(($filters['category'] ?? '') === 'question')>پرسش</option><option value="suggestion" @selected(($filters['category'] ?? '') === 'suggestion')>پیشنهاد</option><option value="complaint" @selected(($filters['category'] ?? '') === 'complaint')>انتقاد یا شکایت</option><option value="other" @selected(($filters['category'] ?? '') === 'other')>سایر</option></select></label>
        <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">اعمال فیلتر</button>
    </form>

    <section class="space-y-4">
        @php($statusLabels = ['open' => 'در انتظار بررسی', 'in_review' => 'در حال پیگیری', 'responded' => 'پاسخ‌داده‌شده', 'closed' => 'بسته‌شده'])
        @php($categoryLabels = ['question' => 'پرسش', 'suggestion' => 'پیشنهاد', 'complaint' => 'انتقاد یا شکایت', 'other' => 'سایر'])
        @forelse($messages as $message)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0"><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $message->parent?->name }} · دانش‌آموز: {{ $message->student?->name }}</p><h2 class="mt-1 break-words text-lg font-black">{{ $message->subject }}</h2></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $statusLabels[$message->status] ?? $message->status }}</span>
                </div>
                <p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-700">{{ $message->body }}</p>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500"><span>{{ $categoryLabels[$message->category] ?? 'پیام' }}</span><span>{{ \App\Support\PersianUi::date($message->created_at) }}</span><span>{{ $message->parent?->mobile ?: $message->parent?->email }}</span></div>
                <form method="POST" action="{{ route('owner.messages.reply', [$academy, $message->id]) }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-4 md:grid-cols-[1fr_190px_auto] md:items-end">
                    @csrf @method('PATCH')
                    <label class="grid gap-1 text-xs font-bold">پاسخ برای ولی<textarea name="response" rows="3" maxlength="5000" class="rounded-xl border border-slate-200 px-3 py-2 text-sm leading-6" placeholder="پاسخ روشن و محترمانه بنویسید...">{{ $message->response }}</textarea></label>
                    <label class="grid gap-1 text-xs font-bold">وضعیت پیگیری<select name="status" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm"><option value="in_review" @selected($message->status === 'in_review')>در حال پیگیری</option><option value="responded" @selected($message->status === 'responded')>ثبت پاسخ و ارسال به والد</option><option value="closed" @selected($message->status === 'closed')>بستن پیام</option></select></label>
                    <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">ذخیره</button>
                </form>
                @if($message->responder)<p class="mt-3 text-xs text-slate-500">آخرین پاسخ توسط {{ $message->responder->name }} ثبت شده است.</p>@endif
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center"><h2 class="font-black">پیامی برای این فیلتر پیدا نشد.</h2><p class="mt-2 text-sm text-slate-500">فیلترها را تغییر دهید یا بعداً دوباره بررسی کنید.</p></div>
        @endforelse
        <div>{{ $messages->links() }}</div>
    </section>
</div>
@endsection
