@extends('layouts.owner')
@section('title','کلاس‌های آموزشگاه | شیخان')
@section('header-title','کلاس‌های آموزشگاه')
@section('content')
<div class="grid gap-5">
    <div><p class="text-xs font-black text-[var(--panel-primary)]">نظارت</p><h2 class="mt-1 text-2xl font-black">{{ $academy->name }}</h2><p class="mt-2 text-sm text-slate-500">کلاس‌ها، مدرس‌ها و ظرفیت را از دید مدیریت آموزشگاه ببین.</p></div>
    <div class="grid gap-4 md:grid-cols-2">
        @forelse($classrooms as $classroom)
            <article class="dashboard-panel p-5">
                <div class="flex items-start justify-between gap-3"><div><h3 class="text-sm font-black">{{ $classroom->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $classroom->course?->title }}</p></div><span class="rounded-full {{ $classroom->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-2.5 py-1 text-[10px]">{{ $classroom->status }}</span></div>
                <div class="mt-4 grid grid-cols-2 gap-2"><div class="rounded-xl bg-slate-50 p-3"><span class="text-[9px] text-slate-500">مدرس</span><strong class="mt-1 block text-xs">{{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}</strong></div><div class="rounded-xl bg-slate-50 p-3"><span class="text-[9px] text-slate-500">دانش‌آموز</span><strong class="mt-1 block text-sm">{{ $classroom->students_count }}</strong></div></div>
                <form method="POST" action="{{ route('owner.classrooms.update',[$academy,$classroom->id]) }}" class="mt-4 flex gap-2">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $classroom->status === 'active' ? 'archived' : 'active' }}"><button class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold">{{ $classroom->status === 'active' ? 'آرشیو کلاس' : 'فعال‌سازی' }}</button></form>
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-500 md:col-span-2">کلاسی در آموزشگاه ثبت نشده است.</div>
        @endforelse
    </div>
</div>
@endsection