@extends('layouts.parent')

@section('title', 'جلسات با آموزشگاه | شیخان')
@section('header-title', 'جلسات برنامه‌ریزی‌شده')

@section('content')
<div class="parent-dashboard space-y-6">
    <header class="parent-panel dashboard-panel p-5 sm:p-7">
        <p class="parent-kicker" style="color:var(--panel-primary)">همراهی آموزشی</p>
        <h1 class="mt-1 text-2xl font-black">جلسات با آموزشگاه</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">زمان جلسات نهایی‌شده با مدرس یا آموزشگاه، همراه با وضعیت و توضیحات آن‌ها در این صفحه نمایش داده می‌شود.</p>
    </header>

    @php($labels = ['proposed' => 'پیشنهاد مدرس · در انتظار تأیید', 'scheduled' => 'زمان تأیید و نهایی شده', 'completed' => 'برگزار شده', 'declined' => 'پذیرفته نشده', 'cancelled' => 'لغو شده'])
    @forelse($meetings as $meeting)
        <article class="parent-panel dashboard-panel p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-[var(--panel-primary)]">{{ $meeting->student?->name }} · {{ $meeting->academy?->name }}</p>
                    <h2 class="mt-1 text-lg font-black">{{ $meeting->title }}</h2>
                    @if($meeting->course || $meeting->classroom)
                        <p class="mt-1 text-sm text-slate-500">{{ $meeting->course?->title }}{{ $meeting->classroom ? ' · ' . $meeting->classroom->title : '' }}</p>
                    @endif
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $labels[$meeting->status] ?? $meeting->status }}</span>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">زمان جلسه</p>
                    <p class="mt-1 font-bold">{{ \App\Support\PersianUi::dateTime($meeting->scheduled_at) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">پایان جلسه</p>
                    <p class="mt-1 font-bold">{{ \App\Support\PersianUi::dateTime($meeting->scheduled_end_at) }}</p>
                </div>
            </div>

            @if($meeting->message)
                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $meeting->message }}</p>
            @endif
            @if($meeting->creator)
                <p class="mt-3 text-xs text-slate-500">ثبت‌کننده: {{ $meeting->creator->name }}</p>
            @endif
        </article>
    @empty
        <section class="parent-panel dashboard-panel p-6">
            <h2 class="text-lg font-black">هنوز جلسه‌ای ثبت نشده است</h2>
            <p class="mt-2 text-sm leading-7 text-slate-500">وقتی زمان جلسه‌ای برای شما و فرزندتان نهایی شود، جزئیات آن در اینجا نمایش داده خواهد شد.</p>
            <a href="{{ route('parent.counseling.index') }}" class="mt-4 inline-flex min-h-10 items-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">درخواست مشاوره</a>
        </section>
    @endforelse

    @if($meetings->hasPages())
        <div class="parent-panel dashboard-panel p-4">{{ $meetings->links() }}</div>
    @endif
</div>
@endsection
