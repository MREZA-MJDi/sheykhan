@extends('layouts.app')

@section('title', 'فروشگاه آموزشی | شیخان')
@section('description', 'کتاب، جزوه و آزمون‌های آموزشی شیخان.')

@section('content')
<section class="home-section">
    <x-layout.container size="wide">
        <div class="home-heading">
            <div>
                <span>فروشگاه آموزشی</span>
                <h1>منابع یادگیری شیخان</h1>
                <p>محصولات منتشرشده را بر اساس دسته‌بندی انتخاب کنید.</p>
            </div>
        </div>
        <div class="home-store-grid">
            @foreach($categories as $category)
                <a href="{{ url('/store?category='.$category['slug']) }}" class="home-store-card">
                    <strong>{{ $category['name'] }}</strong>
                    <span>{{ $category['description'] ?: 'منابع آموزشی منتخب شیخان' }}</span>
                </a>
            @endforeach
        </div>
        <div class="home-product-strip">
            @forelse($products as $product)
                <article class="home-product-card">
                    @if($product->media->first())
                        <img src="{{ $product->media->first()->url() }}" alt="{{ $product->title }}" loading="lazy">
                    @endif
                    <span>{{ $product->category?->name }}</span>
                    <h3>{{ $product->title }}</h3>
                    <strong>{{ number_format((float) ($product->sale_price ?? $product->price), 0, '.', ',') }} تومان</strong>
                </article>
            @empty
                <div class="home-empty-state"><strong>محصولی برای نمایش وجود ندارد.</strong></div>
            @endforelse
        </div>
        {{ $products->links() }}
    </x-layout.container>
</section>
@endsection
