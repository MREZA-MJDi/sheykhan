@extends('layouts.owner')
@section('title','محتوای آموزشگاه | شیخان')
@section('header-title','محتوای آموزشگاه')
@section('content')
<div class="space-y-6 panel-page-enter">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="text-xs font-black text-[var(--panel-primary)]">تولید محتوا</span>
            <h1 class="mt-1 text-2xl font-black">محتوای ارزشمند آموزشگاه</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">مقاله و ویدئو را از همین‌جا منتشر کن؛ همان رکورد در سایت عمومی و SEO استفاده می‌شود.</p>
        </div>
        <a href="{{ route('owner.content.create') }}" class="owner-primary-btn">+ محتوای جدید</a>
    </div>

    <section class="grid gap-3 sm:grid-cols-3">
        <article class="owner-stat-card"><span>کل محتوا</span><strong>{{ $stats['total'] }}</strong><small>قابل جستجو در پنل</small></article>
        <article class="owner-stat-card"><span>منتشرشده</span><strong>{{ $stats['published'] }}</strong><small>قابل نمایش عمومی</small></article>
        <article class="owner-stat-card"><span>پیش‌نویس</span><strong>{{ $stats['drafts'] }}</strong><small>نیازمند تکمیل</small></article>
    </section>

    <section class="dashboard-panel owner-panel">
        <div class="owner-content-list">
            @forelse($contents as $item)
                <article class="owner-content-row">
                    <div class="min-w-0">
                        <span class="owner-content-type">{{ $item->type === 'video' ? 'ویدئو' : 'مقاله' }}</span>
                        <strong>{{ $item->title }}</strong>
                        <small>{{ $item->academy?->name }} · {{ $item->category?->title }}</small>
                    </div>
                    <span class="owner-pill {{ $item->status === 'published' ? 'success' : '' }}">{{ $item->status === 'published' ? 'منتشرشده' : ($item->status === 'draft' ? 'پیش‌نویس' : 'آرشیو') }}</span>
                    <a class="course-action-btn primary" href="{{ route('owner.content.edit', $item) }}">ویرایش</a>
                </article>
            @empty
                <div class="owner-empty">هنوز محتوایی ساخته نشده است.</div>
            @endforelse
        </div>
        <div class="mt-4">{{ $contents->withQueryString()->links() }}</div>
    </section>
</div>
@endsection
