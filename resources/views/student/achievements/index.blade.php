@extends('layouts.student')

@section('title', 'دستاوردهای من')
@section('header-title', 'دستاوردهای من')

@section('content')
<section class="page" id="achievements">
    <div class="card">
        <div class="chead"><h2>دستاوردهای منتشرشده</h2><span class="pill">{{ \App\Support\PersianUi::digits($achievements->total()) }} دستاورد</span></div>
        <div class="g3">
            @forelse($achievements as $achievement)
                <article class="card medal">
                    <div class="pl"><svg class="i" aria-hidden="true"><use href="#i-trophy"/></svg></div>
                    <b>{{ $achievement->display_name ?: $achievement->title }}</b>
                    <p>{{ $achievement->school_name ?: 'دستاورد ثبت‌شده در شیخان' }}</p>
                    <span class="tag">{{ $achievement->achievement_type ?: 'دستاورد' }}</span>
                    @if($achievement->published_at)
                        <p>{{ \App\Support\PersianUi::date($achievement->published_at) }}</p>
                    @endif
                    @foreach($achievement->media as $media)
                        @if($media->url())
                            <a class="btn s" href="{{ $media->url() }}" target="_blank" rel="noopener">مشاهده گواهی</a>
                        @endif
                    @endforeach
                </article>
            @empty
                <div class="empty" style="grid-column:1/-1">هنوز دستاورد منتشرشده‌ای برای حساب تو ثبت نشده است.</div>
            @endforelse
        </div>
        @if($achievements->hasPages())<div class="frm">{{ $achievements->links() }}</div>@endif
    </div>
</section>
@endsection
