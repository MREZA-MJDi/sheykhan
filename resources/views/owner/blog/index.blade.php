@extends('layouts.seo-specialist')
@section('title','مقالات سایت | شیخان')
@section('header-title','بلاگ و مقالات')
@section('content')
<div class="space-y-6 panel-page-enter owner-blog-workspace">
    <header class="owner-blog-hero">
        <div>
            <span class="owner-blog-kicker">مدیریت محتوای عمومی</span>
            <h1>مقالات سایت</h1>
            <p>از این بخش مقاله‌های عمومی شیخان را ایجاد، ویرایش و منتشر کن. تغییرات پس از ذخیره در وب‌سایت اعمال می‌شوند.</p>
        </div>
        <a href="{{ route('owner.blog.create') }}" class="owner-primary-btn">+ مقاله جدید</a>
    </header>

    <section class="owner-blog-stats" aria-label="آمار مقالات">
        @foreach([['کل مقاله‌ها',$stats['total'],'تمام وضعیت‌ها'],['منتشرشده',$stats['published'],'قابل مشاهده در سایت'],['پیش‌نویس',$stats['drafts'],'هنوز عمومی نیست']] as [$label,$value,$hint])
            <article class="owner-stat-card"><span>{{ $label }}</span><strong>{{ $value }}</strong><small>{{ $hint }}</small></article>
        @endforeach
    </section>

    <section class="dashboard-panel owner-blog-list-panel">
        <div class="owner-panel-head">
            <div><h2>فهرست مقاله‌ها</h2><p>برای تغییر محتوا یا تصویر شاخص، مقاله را ویرایش کن.</p></div>
            <span class="owner-blog-count">{{ $posts->total() }} مقاله</span>
        </div>

        <div class="owner-blog-list">
            @forelse($posts as $post)
                @php($cover = $post->media->first(fn($media) => $media->pivot?->collection === 'cover')?->url())
                <article class="owner-blog-row">
                    <div class="owner-blog-thumb">
                        @if($cover)<img src="{{ $cover }}" alt="" loading="lazy">@else<span aria-hidden="true">تصویر ندارد</span>@endif
                    </div>
                    <div class="owner-blog-copy">
                        <strong>{{ $post->title }}</strong>
                        <small>{{ $post->category?->name ?: 'بدون دسته' }} · آخرین تغییر {{ \App\Support\PersianUi::dateTime($post->updated_at) }}</small>
                        @if($post->excerpt)<p>{{ \Illuminate\Support\Str::limit($post->excerpt, 120) }}</p>@endif
                    </div>
                    <span class="owner-pill {{ $post->status==='published'?'success':'' }}">{{ $post->status==='published'?'منتشرشده':'پیش‌نویس' }}</span>
                    <div class="owner-blog-actions">
                        <a class="course-action-btn primary" href="{{ route('owner.blog.edit',$post) }}">ویرایش</a>
                        <form method="POST" action="{{ route('owner.blog.destroy',$post) }}" onsubmit="return confirm('این مقاله برای همیشه حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="course-action-btn danger" type="submit">حذف</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="owner-empty"><strong>هنوز مقاله‌ای ثبت نشده است.</strong><p>اولین مقاله را بساز و تصویر شاخص آن را همین‌جا بارگذاری کن.</p><a class="owner-primary-btn" href="{{ route('owner.blog.create') }}">ایجاد اولین مقاله</a></div>
            @endforelse
        </div>
        <div class="mt-4">{{ $posts->links() }}</div>
    </section>
</div>
@endsection
