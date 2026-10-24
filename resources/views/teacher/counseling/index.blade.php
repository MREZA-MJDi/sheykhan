@extends('layouts.teacher')

@section('title', 'درخواست‌های مشاوره | شیخان')
@section('header-title', 'درخواست‌های مشاوره')

@section('content')
<div class="space-y-6">
    <header>
        <p class="text-xs font-black text-[var(--panel-primary)]">پیگیری آموزشی</p>
        <h1 class="mt-1 text-2xl font-black">درخواست‌های مشاورهٔ دانش‌آموزان</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">فقط درخواست‌های مربوط به دانش‌آموزان دوره‌ها و کلاس‌های واگذارشده به تو نمایش داده می‌شوند. زمان پیشنهادی پس از ثبت، برای تأیید آموزشگاه ارسال می‌شود و تا آن زمان جلسه قطعی نیست.</p>
    </header>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <section class="space-y-4">
        @php($labels = ['requested' => 'در انتظار پیشنهاد مدرس', 'proposed' => 'در انتظار تأیید آموزشگاه', 'scheduled' => 'زمان نهایی تعیین شده', 'completed' => 'برگزارشده', 'declined' => 'پذیرفته نشد', 'cancelled' => 'لغوشده'])
        @forelse($requests as $item)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[var(--panel-primary)]">{{ $item->academy?->name }} · {{ $item->student?->name }} · ولی: {{ $item->parent?->name }}</p>
                        <h2 class="mt-1 break-words text-lg font-black">{{ $item->subject }}</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $labels[$item->status] ?? $item->status }}</span>
                </div>
                @if($item->details)
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $item->details }}</p>
                @endif
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-500">زمان پیشنهادی والد</p>
                        <p class="mt-1 text-sm font-bold">{{ $item->preferred_at ? \App\Support\PersianUi::date($item->preferred_at).' · '.\App\Support\PersianUi::time($item->preferred_at) : 'ثبت نشده' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-500">زمان پیشنهادی مدرس</p>
                        <p class="mt-1 text-sm font-bold">{{ $item->proposed_at ? \App\Support\PersianUi::date($item->proposed_at).' · '.\App\Support\PersianUi::time($item->proposed_at) : 'هنوز پیشنهادی ثبت نشده' }}</p>
                    </div>
                </div>
                @if($item->proposal_notes)
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">توضیح مدرس: {{ $item->proposal_notes }}</p>
                @endif
                @if(in_array($item->status, ['requested', 'proposed'], true))
                    <form method="POST" action="{{ route('teacher.counseling-requests.propose', $item->id) }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-4 md:grid-cols-2 md:items-end">
                        @csrf
                        <label class="grid gap-1 text-xs font-bold">
                            زمان پیشنهادی جدید
                            <input type="datetime-local" name="proposed_at" required min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ $item->proposed_at?->format('Y-m-d\TH:i') ?? $item->preferred_at?->format('Y-m-d\TH:i') }}" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
                        </label>
                        <label class="grid gap-1 text-xs font-bold">
                            توضیح برای آموزشگاه
                            <textarea name="proposal_notes" rows="2" maxlength="3000" class="rounded-xl border border-slate-200 px-3 py-2 text-sm leading-6">{{ $item->proposal_notes }}</textarea>
                        </label>
                        <p class="text-xs leading-6 text-slate-500 md:col-span-2">پیشنهاد مدرس زمان قطعی را تغییر نمی‌دهد؛ ادمین / منشی باید آن را تأیید یا اصلاح کند.</p>
                        <button type="submit" class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white md:col-span-2">ارسال پیشنهاد به آموزشگاه</button>
                    </form>
                @elseif($item->scheduled_at)
                    <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">زمان قطعی: {{ \App\Support\PersianUi::date($item->scheduled_at).' · '.\App\Support\PersianUi::time($item->scheduled_at) }}</p>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center">
                <h2 class="font-black">درخواست مرتبطی در دسترس نیست.</h2>
                <p class="mt-2 text-sm text-slate-500">درخواست‌ها فقط پس از اتصال امن والد، دانش‌آموز و کلاس / دورهٔ مربوط نمایش داده می‌شوند.</p>
            </div>
        @endforelse
        <div>{{ $requests->links() }}</div>
    </section>
</div>
@endsection
