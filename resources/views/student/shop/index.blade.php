@extends('layouts.student')

@section('title', 'فروشگاه منابع')
@section('heading', 'فروشگاه منابع')

@section('content')
<div class="page-head">
  <p class="muted small">کتاب، جزوه و منابع آموزشی منتشرشده در فروشگاه شیخان</p>
  <x-tag tone="b">{{ \App\Support\PersianUi::digits($products->total()) }} محصول</x-tag>
</div>

@if(count($categories))
<section class="card">
  <div class="chead"><h2>دسته‌بندی منابع</h2></div>
  <div class="chips">
    <a class="pill" href="{{ route('store.index') }}">همه</a>
    @foreach($categories as $category)
      <a class="pill" href="{{ route('store.index', ['category' => $category['slug']]) }}">{{ $category['name'] }}</a>
    @endforeach
  </div>
</section>
@endif

<div class="g3">
  @forelse($products as $product)
    @php($price = $product->sale_price ?? $product->price)
    <article class="card cc">
      <a href="{{ route('store.product.show', $product) }}" aria-label="مشاهده {{ $product->title }}">
        <div class="th blue">
          @if($product->media->first()?->url())
            <img src="{{ $product->media->first()->url() }}" alt="{{ $product->title }}" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover">
          @else
            <svg class="i" style="width:40px;height:40px"><use href="#i-book"/></svg>
          @endif
        </div>
        <h3>{{ $product->title }}</h3>
      </a>
      <p>{{ $product->category?->name ?? 'منبع آموزشی' }}</p>
      @if($product->description)<p>{{ IlluminateSupportStr::limit(strip_tags((string) $product->description), 100) }}</p>@endif
      <b>{{ \App\Support\PersianUi::moneyInCurrency($price, $product->currency) }}</b>
      <a class="btn s" href="{{ route('store.product.show', $product) }}">مشاهده محصول</a>
    </article>
  @empty
    <x-empty icon="i-cart" text="در این دسته‌بندی محصول منتشرشده‌ای وجود ندارد." />
  @endforelse
</div>
@if($products->hasPages())<div class="frm">{{ $products->links() }}</div>@endif
@endsection
