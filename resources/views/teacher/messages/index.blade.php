@extends('layouts.teacher')

@section('title', 'پیام‌های اولیا | شیخان')
@section('header-title', 'پیام‌های اولیا')

@section('content')
<div class="communication-page space-y-6">
    <header class="communication-page__hero">
        <p class="text-xs font-black text-[var(--panel-primary)]">ارتباط با خانواده</p>
        <h1 class="mt-1 text-2xl font-black">پیام‌های مرتبط با کلاس‌های شما</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">فقط پیام‌های والدین دانش‌آموزان کلاس‌ها و دوره‌های تخصیص‌یافته به شما نمایش داده می‌شوند. پاسخ شما در کارتابل والد و مدیر آموزشگاه ثبت می‌شود.</p>
    </header>
    <form method="GET" class="dashboard-panel grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_180px_180px_auto_auto] sm:items-end" role="search" aria-label="فیلتر پیام‌های والدین">
        <label class="grid gap-1 text-xs font-bold">جست‌وجو
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="min-h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm" placeholder="موضوع، متن، نام والد یا دانش‌آموز">
        </label>
        <label class="grid gap-1 text-xs font-bold">وضعیت
            <select name="status" class="min-h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                <option value="">همه وضعیت‌ها</option>
                <option value="open" @selected(($filters['status'] ?? '') === 'open')>در انتظار بررسی</option>
                <option value="in_review" @selected(($filters['status'] ?? '') === 'in_review')>در حال پیگیری</option>
                <option value="responded" @selected(($filters['status'] ?? '') === 'responded')>پاسخ‌داده‌شده</option>
                <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>بسته‌شده</option>
            </select>
        </label>
        <label class="grid gap-1 text-xs font-bold">نوع پیام
            <select name="category" class="min-h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                <option value="">همه انواع</option>
                <option value="question" @selected(($filters['category'] ?? '') === 'question')>پرسش</option>
                <option value="suggestion" @selected(($filters['category'] ?? '') === 'suggestion')>پیشنهاد</option>
                <option value="complaint" @selected(($filters['category'] ?? '') === 'complaint')>انتقاد یا شکایت</option>
                <option value="other" @selected(($filters['category'] ?? '') === 'other')>سایر</option>
            </select>
        </label>
        <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">اعمال فیلتر</button>
        <a href="{{ route('teacher.messages.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold">پاک‌کردن</a>
    </form>
    <section class="communication-page__message-list space-y-4">
        @php($statusLabels = ['open' => 'در انتظار بررسی', 'in_review' => 'در حال پیگیری', 'responded' => 'پاسخ داده‌شده', 'closed' => 'بسته‌شده'])
        @php($categoryLabels = ['question' => 'پرسش', 'suggestion' => 'پیشنهاد', 'complaint' => 'انتقاد یا شکایت', 'other' => 'سایر'])
        @forelse($messages as $message)
            <article class="communication-page__message dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $message->academy?->name }} · {{ $message->student?->name }}</p><h2 class="mt-1 text-lg font-black">{{ $message->subject }}</h2></div>
                    <span class="communication-page__status rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $statusLabels[$message->status] ?? $message->status }}</span>
                </div>
                <p class="communication-page__body mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-700">{{ $message->body }}</p>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500"><span>{{ $message->parent?->name }}</span><span>{{ $categoryLabels[$message->category] ?? 'پیام' }}</span><span>{{ \App\Support\PersianUi::date($message->created_at) }}</span></div>
                @if($message->response)
                    <div class="communication-page__response mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-black text-emerald-800">پاسخ ثبت‌شده @if($message->responder) · {{ $message->responder->name }} @endif</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-emerald-950">{{ $message->response }}</p>
                    </div>
                @endif
                @if(!in_array($message->status, ['closed'], true))
                    <form method="POST" action="{{ route('teacher.messages.reply', $message) }}" class="communication-page__form mt-5 grid gap-3 border-t border-slate-100 pt-4">
                        @csrf @method('PATCH')
                        <label class="grid gap-1 text-xs font-bold">پاسخ برای والد
                            <textarea name="response" rows="3" maxlength="5000" required class="rounded-xl border border-slate-200 px-3 py-2 text-sm leading-7" placeholder="پاسخ روشن و محترمانه بنویسید...">{{ old('response') }}</textarea>
                        </label>
                        @error('response')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                        <div><button class="communication-page__button min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">ثبت پاسخ برای والد</button></div>
                    </form>
                @else
                    <p class="mt-3 text-xs text-slate-500">این پیام بسته شده است.</p>
                @endif
            </article>
        @empty
            <div class="communication-page__empty dashboard-panel p-10 text-center"><h2 class="font-black">پیامی برای کلاس‌ها و دوره‌های شما ثبت نشده است.</h2><p class="mt-2 text-sm leading-7 text-slate-500">پیام‌های والدین دانش‌آموزانِ تحت پوشش شما اینجا نمایش داده می‌شوند.</p></div>
        @endforelse
        <div>{{ $messages->links() }}</div>
    </section>
</div>
@endsection
