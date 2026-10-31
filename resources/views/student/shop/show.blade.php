@extends('layouts.student')

@section('title', $product->title)
@section('heading', 'جزئیات محصول')

@section('content')
<div class="page-head">
  <a class="back" href="{{ route('store.index') }}"><svg class="i"><use href="#i-back"/></svg>بازگشت به فروشگاه</a>
  @if($product->category)<x-tag tone="b">{{ $product->category->name }}</x-tag>@endif
</div>

<div class="row">
  <section class="card">
    <div class="th blue" style="height:auto;aspect-ratio:16/10">
      @if($product->media->first()?->url())
        <img src="{{ $product->media->first()->url() }}" alt="{{ $product->title }}" style="width:100%;height:100%;object-fit:cover">
      @else
        <svg class="i" style="width:56px;height:56px"><use href="#i-book"/></svg>
      @endif
    </div>
  </section>
  <section class="card sec">
    <h2 style="font-size:22px">{{ $product->title }}</h2>
    @if($product->subtitle)<p class="muted">{{ $product->subtitle }}</p>@endif
    <x-tag tone="b">{{ match($product->product_type) {'digital' => 'محصول دیجیتال','book' => 'کتاب','exam' => 'آزمون','file' => 'فایل آموزشی',default => 'منبع آموزشی'} }}</x-tag>
    <b style="font-size:26px">{{ AppSupportPersianUi::moneyInCurrency($product->sale_price ?? $product->price, $product->currency) }}</b>
    <a class="btn" href="{{ route('checkout.show', $product) }}">خرید و دریافت امن</a>
    @if($product->description)<p style="white-space:pre-line;line-height:2">{{ $product->description }}</p>@endif
  </section>
</div>
@endsection
