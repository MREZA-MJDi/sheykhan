@extends('layouts.parent')

@section('title', 'درخواست مشاوره | شیخان')
@section('header-title', 'درخواست‌های مشاوره')

@section('content')
<div class="parent-dashboard space-y-6">
    <header class="parent-panel dashboard-panel p-5 sm:p-7">
        <p class="parent-kicker" style="color:var(--panel-primary)">همراهی آموزشی</p>
        <h1 class="mt-1 text-2xl font-black">درخواست جلسهٔ مشاوره</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">زمان پیشنهادی خودت را ثبت کن. زمان قطعی و توضیحات آموزشگاه پس از بررسی اینجا نمایش داده می‌شود.</p>
    </header>

    <section class="parent-panel dashboard-panel p-5 sm:p-7">
        <h2 class="text-lg font-black">درخواست جدید</h2>
        @if($contactableChildren->isEmpty())
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-900">
                برای ثبت درخواست، ابتدا باید حساب والد به دانش‌آموز و آموزشگاه فعال متصل شود.
            </div>
        @else
            <form method="POST" action="{{ route('parent.counseling.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1 text-sm font-bold sm:col-span-2">
                    فرزند و آموزشگاه
                    <select name="target" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                        <option value="">انتخاب فرزند و آموزشگاه</option>
                        @foreach($contactableChildren as $child)
                            @foreach($child->academies as $academy)
                                <option value="{{ $child->id }}:{{ $academy->id }}" @selected(old('target') === $child->id.':'.$academy->id)>{{ $child->name }} — {{ $academy->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('target')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1 text-sm font-bold">
                    موضوع جلسه
                    <input name="subject" value="{{ old('subject') }}" minlength="3" maxlength="180" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm" placeholder="مثلاً پیگیری پیشرفت در ریاضی">
                    @error('subject')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1 text-sm font-bold">
                    زمان پیشنهادی (اختیاری)
                    <input type="datetime-local" name="preferred_at" value="{{ old('preferred_at') }}" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
                    @error('preferred_at')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                    <small class="font-normal leading-6 text-slate-500">تاریخ در فرم با تقویم شمسی انتخاب می‌شود؛ زمان قطعی را آموزشگاه تأیید می‌کند.</small>
                </label>
                <label class="grid gap-1 text-sm font-bold sm:col-span-2">
                    توضیحات (اختیاری)
                    <textarea name="details" rows="4" maxlength="5000" class="rounded-xl border border-slate-200 px-3 py-3 text-sm leading-7">{{ old('details') }}</textarea>
                    @error('details')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <div class="sm:col-span-2"><button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ثبت درخواست مشاوره</button></div>
            </form>
        @endif
    </section>

    <section class="space-y-4">
        <div><h2 class="text-xl font-black">وضعیت درخواست‌ها</h2><p class="mt-1 text-sm text-slate-500">وضعیت درخواست‌ها و زمان نهایی جلسه پس از ثبت توسط آموزشگاه به‌روز می‌شود.</p></div>
        @php($labels = ['requested' => 'در انتظار بررسی', 'proposed' => 'پیشنهاد زمان از مدرس · در انتظار تأیید آموزشگاه', 'scheduled' => 'زمان جلسه تعیین شده', 'completed' => 'برگزارشده', 'declined' => 'پذیرفته نشد', 'cancelled' => 'لغوشده'])
        @forelse($requests as $item)
            <article class="parent-panel dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $item->student?->name }} · {{ $item->academy?->name }}</p><h3 class="mt-1 text-lg font-black">{{ $item->subject }}</h3></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $labels[$item->status] ?? $item->status }}</span>
                </div>
                @if($item->details)<p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $item->details }}</p>@endif
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان پیشنهادی شما</p><p class="mt-1 text-sm font-bold">{{ $item->preferred_at ? \App\Support\PersianUi::date($item->preferred_at).' · '.\App\Support\PersianUi::time($item->preferred_at) : 'ثبت نشده' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان قطعی</p><p class="mt-1 text-sm font-bold">{{ $item->scheduled_at ? \App\Support\PersianUi::date($item->scheduled_at).' · '.\App\Support\PersianUi::time($item->scheduled_at) : 'هنوز تعیین نشده' }}</p></div>
                </div>
                @if($item->proposed_at)
                    <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                        <p class="text-xs font-black text-blue-800">{{ $item->status === 'scheduled' ? 'زمان پیشنهادی مدرس · تأییدشده توسط آموزشگاه' : 'زمان پیشنهادی مدرس · هنوز قطعی نیست' }}</p>
                        <p class="mt-2 text-sm font-bold">{{ \App\Support\PersianUi::date($item->proposed_at).' · '.\App\Support\PersianUi::time($item->proposed_at) }}</p>
                        @if($item->proposal_notes)<p class="mt-2 whitespace-pre-line text-sm leading-7 text-blue-950">{{ $item->proposal_notes }}</p>@endif
                    </div>
                @endif
                @if($item->response_notes)<div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4"><p class="text-xs font-black text-emerald-800">پاسخ آموزشگاه</p><p class="mt-2 whitespace-pre-line text-sm leading-7 text-emerald-950">{{ $item->response_notes }}</p></div>@endif
                @if($item->handler)<p class="mt-3 text-xs text-slate-500">آخرین پیگیری توسط {{ $item->handler->name }}</p>@endif
            </article>
        @empty
            <div class="parent-panel dashboard-panel p-8 text-center"><h3 class="font-black">هنوز درخواست مشاوره‌ای ثبت نشده است.</h3></div>
        @endforelse
        <div>{{ $requests->links() }}</div>
    </section>
</div>
@endsection
