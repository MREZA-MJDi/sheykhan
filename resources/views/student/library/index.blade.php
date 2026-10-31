@extends('layouts.student')

@section('title', 'کتابخانه من')
@section('heading', 'کتابخانه من')

@section('content')
<div class="page-head">
  <p class="muted small">دوره‌ها و فایل‌هایی که دسترسی آن‌ها برای حساب تو تأیید شده است.</p>
  <a class="btn s" href="{{ route('store.index') }}">رفتن به فروشگاه</a>
</div>

@if($pendingOrders->isNotEmpty())
<section class="card">
  <div class="chead"><h2>سفارش‌های در انتظار تأیید</h2><x-tag tone="a">{{ AppSupportPersianUi::digits($pendingOrders->count()) }} سفارش</x-tag></div>
  @foreach($pendingOrders as $order)
    @php($pendingItem = $order->items->first())
    @php($pendingTitle = $pendingItem?->product_title_snapshot ?: ($pendingItem?->course?->title ?: ($pendingItem?->product?->title ?: 'محتوای سفارش')))
    <a class="trk" href="{{ route('orders.show', $order) }}">
      <div class="th amber"><svg class="i"><use href="#i-clock"/></svg></div>
      <div><b>{{ $pendingTitle }}</b><span>سفارش {{ $order->order_number }} · {{ AppSupportPersianUi::money($order->total) }}</span></div>
      <x-tag tone="a">پیگیری پرداخت</x-tag>
    </a>
  @endforeach
</section>
@endif

<section class="card">
  <div class="chead"><h2>دوره‌های من</h2><x-tag tone="b">{{ AppSupportPersianUi::digits($myCourses->count()) }} دوره</x-tag></div>
  <div class="g3">
    @forelse($myCourses as $enrollment)
      <article class="card cc">
        <div class="th blue"><svg class="i"><use href="#i-book"/></svg></div>
        <h3>{{ $enrollment->course?->title }}</h3>
        <p>{{ $enrollment->course?->academy?->name ?? 'آموزشگاه شیخان' }} · دسترسی فعال</p>
        <a class="btn s" href="{{ route('student.courses.show', $enrollment->course) }}">ادامه یادگیری</a>
      </article>
    @empty
      <x-empty icon="i-book" text="هنوز دورهٔ فعالی در کتابخانهٔ تو وجود ندارد." :href="route('store.index')" action="مشاهده فروشگاه" />
    @endforelse
  </div>
</section>

<section class="card">
  <div class="chead"><h2>منابع و فایل‌های خریداری‌شده</h2><x-tag tone="b">{{ AppSupportPersianUi::digits($products->count()) }} منبع</x-tag></div>
  <div class="g3">
    @forelse($products as $entitlement)
      @php($order = $entitlement->orderItem?->order)
      <article class="card cc">
        <div class="th violet"><svg class="i"><use href="#i-file"/></svg></div>
        <h3>{{ $entitlement->product?->title ?? 'منبع آموزشی' }}</h3>
        <p>{{ $entitlement->product?->category?->name ?: 'منبع آموزشی' }} · دسترسی فعال</p>
        @forelse($entitlement->product?->files ?? [] as $downloadFile)
          @if($order && $downloadFile->media?->status === 'active' && $downloadFile->media?->visibility === 'private')
            <a class="btn s ghost" href="{{ route('orders.files.view', [$order, $downloadFile]) }}">مشاهده امن فایل</a>
          @endif
        @empty
          <span class="muted small">فایل فعالی برای دریافت ثبت نشده است.</span>
        @endforelse
      </article>
    @empty
      <x-empty icon="i-file" text="هنوز منبع خریداری‌شده‌ای برای دریافت نداری." :href="route('store.index')" action="رفتن به فروشگاه" />
    @endforelse
  </div>
</section>
@endsection
