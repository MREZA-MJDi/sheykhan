@extends('layouts.teacher')
@section('title','کلاس‌های آنلاین | شیخان')
@section('header-title','کلاس‌های آنلاین')
@section('content')
<div class="grid gap-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-black text-[var(--panel-primary)]">جلسه آنلاین</p><h2 class="mt-1 text-2xl font-black">جلسه‌های آنلاین</h2></div><a href="{{ route('teacher.live-classes.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">+ جلسه جدید</a></div>
    <div class="grid gap-4 md:grid-cols-2">
        @forelse($liveClasses as $liveClass)
            <article class="dashboard-panel p-5"><div class="flex items-start justify-between gap-3"><div><h3 class="text-sm font-black">{{ $liveClass->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $liveClass->course?->title }} · {{ $liveClass->classroom?->title ?? 'همه دانش‌آموزان دوره' }}</p></div><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] text-indigo-700">{{ match($liveClass->status){'scheduled'=>'برنامه‌ریزی‌شده','live'=>'در حال برگزاری','ended'=>'پایان‌یافته','cancelled'=>'لغوشده',default=>$liveClass->status} }}</span></div><div class="mt-5 flex items-center justify-between"><span class="text-sm font-black">{{ \App\Support\PersianUi::date($liveClass->scheduled_at).' · '.\App\Support\PersianUi::time($liveClass->scheduled_at) }}</span>@if($liveClass->meeting_url)<a target="_blank" href="{{ $liveClass->meeting_url }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold">ورود به جلسه</a>@endif</div></article>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-500 md:col-span-2">جلسه‌ای ثبت نشده است.</div>
        @endforelse
    </div>

    @if($liveClasses->hasPages())
        <div class="dashboard-panel p-4">
            {{ $liveClasses->links('components.navigation.pagination') }}
        </div>
    @endif
</div>
@endsection