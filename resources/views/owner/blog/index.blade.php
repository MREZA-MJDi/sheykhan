@extends('layouts.owner')
@section('title','مقالات من | شیخان')
@section('header-title','مقالات')
@section('content')
<div class="space-y-6 panel-page-enter">
<div class="flex flex-wrap items-end justify-between gap-4"><div><span class="text-xs font-black text-[var(--panel-primary)]">مجله</span><h1 class="mt-1 text-2xl font-black">مقاله‌های متعلق به من</h1><p class="mt-2 text-sm text-slate-500">Owner فقط مقالاتی را مدیریت می‌کند که خودش تولید کرده است؛ نه کل وبلاگ پلتفرم.</p></div><a href="{{ route('owner.blog.create') }}" class="owner-primary-btn">+ مقاله جدید</a></div>
<section class="grid gap-3 sm:grid-cols-3">@foreach([['کل',$stats['total']],['منتشرشده',$stats['published']],['پیش‌نویس',$stats['drafts']]] as [$l,$v])<article class="owner-stat-card"><span>{{ $l }}</span><strong>{{ $v }}</strong><small>داده واقعی</small></article>@endforeach</section>
<section class="dashboard-panel owner-panel"><div class="grid gap-2">@forelse($posts as $post)<article class="owner-content-row"><div class="min-w-0"><span class="owner-content-type">مقاله</span><strong>{{ $post->title }}</strong><small>{{ $post->category?->name ?: 'بدون دسته' }}</small></div><span class="owner-pill {{ $post->status==='published'?'success':'' }}">{{ $post->status==='published'?'منتشرشده':'پیش‌نویس' }}</span><a class="course-action-btn primary" href="{{ route('owner.blog.edit',$post) }}">ویرایش</a></article>@empty<div class="owner-empty">مقاله‌ای ثبت نشده است.</div>@endforelse</div><div class="mt-4">{{ $posts->links() }}</div></section>
</div>
@endsection
