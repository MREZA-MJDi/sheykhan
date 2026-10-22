@extends('layouts.teacher')

@section('title', 'جلسات با اولیا | شیخان')
@section('header-title', 'جلسات با اولیا')

@section('content')
<div class="space-y-6">
    <header><p class="text-xs font-black text-[var(--panel-primary)]">هماهنگی آموزشگاه</p><h1 class="mt-1 text-2xl font-black">جلسات برنامه‌ریزی‌شده با اولیا</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">جلسه‌های مربوط به کلاس‌ها و دوره‌هایی که به شما تخصیص دارند نمایش داده می‌شوند. بعد از جلسه، وضعیت را به‌روز کنید تا خانواده و کارتابل آموزشگاه از نتیجه مطلع باشند.</p></header>
    <section class="space-y-4">
        @php($labels = ['scheduled' => 'برنامه‌ریزی‌شده', 'completed' => 'برگزارشده', 'cancelled' => 'لغوشده'])
        @forelse($meetings as $meeting)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0"><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $meeting->academy?->name }} · {{ $meeting->classroom?->title ?: $meeting->course?->title }}</p><h2 class="mt-1 break-words text-lg font-black">{{ $meeting->title }}</h2></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $labels[$meeting->status] ?? $meeting->status }}</span>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">دانش‌آموز</p><p class="mt-1 text-sm font-bold">{{ $meeting->student?->name ?: '—' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">ولی / سرپرست</p><p class="mt-1 text-sm font-bold">{{ $meeting->guardian?->name ?: '—' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان</p><p class="mt-1 text-sm font-bold">{{ \App\Support\PersianUi::date($meeting->scheduled_at) }} · {{ \App\Support\PersianUi::time($meeting->scheduled_at) }}</p></div>
                </div>
                @if($meeting->message)<p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $meeting->message }}</p>@endif
                @if($meeting->status === 'scheduled')
                    <form method="POST" action="{{ route('teacher.meetings.status', $meeting->id) }}" class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                        @csrf @method('PATCH')
                        <button name="status" value="completed" class="min-h-10 rounded-xl bg-emerald-600 px-4 text-sm font-black text-white">ثبت برگزاری جلسه</button>
                        <button name="status" value="cancelled" class="min-h-10 rounded-xl border border-rose-200 bg-white px-4 text-sm font-bold text-rose-700">ثبت لغو جلسه</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center"><h2 class="font-black">جلسه‌ای برای کلاس‌های شما ثبت نشده است.</h2><p class="mt-2 text-sm leading-7 text-slate-500">جلسه‌هایی که توسط ادمین/منشی برای دانش‌آموزان کلاس شما ثبت شوند اینجا نمایش داده می‌شوند.</p></div>
        @endforelse
        <div>{{ $meetings->links() }}</div>
    </section>
</div>
@endsection
