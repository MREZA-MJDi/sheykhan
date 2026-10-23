@extends('layouts.student')

@section('title', 'جزوه‌ها و منابع')
@section('header-title', 'جزوه‌ها و منابع')

@section('content')
<section class="page" id="resources">
    <div class="card">
        <div class="chead"><h2>منابع منتشرشده برای تو</h2><span class="pill">{{ \App\Support\PersianUi::digits($resources->total()) }} منبع</span></div>
        <div class="g3">
            @forelse($resources as $resource)
                <article class="card cc">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-file"/></svg></div>
                    <h3>{{ $resource->title }}</h3>
                    <p>{{ $resource->course?->title ?? $resource->classroom?->title ?? $resource->academy?->name ?? 'منبع آموزشی' }}</p>
                    @if($resource->description)<p>{{ $resource->description }}</p>@endif
                    <p>{{ strtoupper($resource->media?->extension ?? $resource->resource_type ?? 'FILE') }} @if($resource->media) · {{ \App\Support\PersianUi::number(((int) $resource->media->size) / 1024) }} کیلوبایت @endif</p>
                    @if($resource->release_at)<p>انتشار: {{ \App\Support\PersianUi::date($resource->release_at) }}</p>@endif
                    <a class="btn s" href="{{ route('student.resources.view', $resource->id) }}">مشاهده منبع</a>
                </article>
            @empty
                <div class="empty" style="grid-column:1/-1">هنوز منبع منتشرشده‌ای برای دوره‌ها و کلاس‌های تو در دسترس نیست.</div>
            @endforelse
        </div>
        @if($resources->hasPages())<div class="frm">{{ $resources->links() }}</div>@endif
    </div>
</section>
@endsection
