@extends('layouts.owner')

@section('title', 'جلسات مشاوره | شیخان')
@section('header-title', 'جلسات مشاوره')

@section('content')
<div class="space-y-6 panel-page-enter owner-workspace-page owner-counseling-page">
    <header><p class="text-xs font-black text-[var(--panel-primary)]">پشتیبانی خانواده</p><h1 class="mt-1 text-2xl font-black">درخواست‌های جلسه مشاوره</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">زمان پیشنهادی والد، زمان نهایی جلسه و توضیح پاسخ را ثبت کنید. تغییر وضعیت فقط روی درخواست همین آموزشگاه اعمال می‌شود.</p></header>
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="owner-stat-card"><span>در انتظار بررسی</span><strong>{{ \App\Support\PersianUi::digits($stats['requested']) }}</strong><small>درخواست</small></article>
        <article class="owner-stat-card"><span>پیشنهاد مدرس</span><strong>{{ \App\Support\PersianUi::digits($stats['proposed']) }}</strong><small>در انتظار تأیید</small></article>
        <article class="owner-stat-card"><span>زمان‌بندی‌شده</span><strong>{{ \App\Support\PersianUi::digits($stats['scheduled']) }}</strong><small>جلسه</small></article>
        <article class="owner-stat-card"><span>برگزارشده</span><strong>{{ \App\Support\PersianUi::digits($stats['completed']) }}</strong><small>جلسه</small></article>
    </section>
    <section class="space-y-4">
        @php($labels = ['requested' => 'در انتظار بررسی', 'proposed' => 'پیشنهاد مدرس · در انتظار تأیید', 'scheduled' => 'زمان جلسه تعیین شده', 'completed' => 'برگزارشده', 'declined' => 'پذیرفته نشد', 'cancelled' => 'لغوشده'])
        @forelse($requests as $item)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $item->parent?->name }} · {{ $item->student?->name }}</p><h2 class="mt-1 text-lg font-black">{{ $item->subject }}</h2></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $labels[$item->status] ?? $item->status }}</span>
                </div>
                @if($item->details)<p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $item->details }}</p>@endif
                @if($item->proposed_at)
                    <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50 p-3 text-sm text-blue-900">
                        <strong>پیشنهاد مدرس{{ $item->proposer ? ' · '.$item->proposer->name : '' }}</strong>
                        <p class="mt-1">{{ \App\Support\PersianUi::date($item->proposed_at).' · '.\App\Support\PersianUi::time($item->proposed_at) }}</p>
                        @if($item->proposal_notes)<p class="mt-1 whitespace-pre-line">{{ $item->proposal_notes }}</p>@endif
                    </div>
                @endif
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان پیشنهادی والد</p><p class="mt-1 text-sm font-bold">{{ $item->preferred_at ? \App\Support\PersianUi::date($item->preferred_at) . ' · ' . \App\Support\PersianUi::time($item->preferred_at) : 'ثبت نشده' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان نهایی</p><p class="mt-1 text-sm font-bold">{{ $item->scheduled_at ? \App\Support\PersianUi::date($item->scheduled_at) . ' · ' . \App\Support\PersianUi::time($item->scheduled_at) : 'هنوز تعیین نشده' }}</p></div>
                </div>
                <form method="POST" action="{{ route('owner.counseling.update', [$academy, $item->id]) }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-4 md:grid-cols-[190px_1fr_1fr_auto] md:items-end">
                    @csrf @method('PATCH')
                    <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm" required><option value="scheduled" @selected($item->status === 'scheduled')>تعیین زمان جلسه</option><option value="completed" @selected($item->status === 'completed')>برگزارشده</option><option value="declined" @selected($item->status === 'declined')>پذیرفته نشد</option><option value="cancelled" @selected($item->status === 'cancelled')>لغوشده</option></select></label>
                    <label class="grid gap-1 text-xs font-bold">زمان قطعی جلسه<input type="datetime-local" name="scheduled_at" value="{{ ($item->scheduled_at ?? $item->proposed_at ?? $item->preferred_at)?->format('Y-m-d\TH:i') }}" min="{{ now()->format('Y-m-d\TH:i') }}" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm"><span class="font-normal text-slate-500">برای انتخاب «تعیین زمان جلسه» الزامی است.</span></label>
                    <label class="grid gap-1 text-xs font-bold">توضیح برای والد<textarea name="response_notes" rows="2" maxlength="3000" class="rounded-xl border border-slate-200 px-3 py-2 text-sm leading-6">{{ $item->response_notes }}</textarea></label>
                    <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">ذخیره</button>
                </form>
                @if($item->handler)<p class="mt-3 text-xs text-slate-500">آخرین تغییر توسط {{ $item->handler->name }} انجام شده است.</p>@endif
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center"><h2 class="font-black">درخواست مشاوره‌ای ثبت نشده است.</h2><p class="mt-2 text-sm text-slate-500">درخواست‌های جدید والدین پس از ثبت در این قسمت نمایش داده می‌شوند.</p></div>
        @endforelse
        <div>{{ $requests->links() }}</div>
    </section>
</div>
@endsection
