@extends('layouts.app')
@section('title', 'کتابخانه من | شیخان')
@section('description', 'دوره‌های فعال و فایل‌های آموزشی خریداری‌شده در شیخان.')
@section('content')
<section class="public-library-page">
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <header class="public-library-hero">
                <div>
                    <span class="ui-eyebrow">محتوای من</span>
                    <h1>کتابخانه یادگیری</h1>
                    <p>دوره‌ها و فایل‌هایی که دسترسی آن‌ها معتبر است از این‌جا در دسترس‌اند. سفارش در انتظار یا رسید تأییدنشده، دسترسی ایجاد نمی‌کند.</p>
                    <div class="public-library-hero__actions">
                        <a href="{{ route('courses.index') }}">کشف دوره‌ها <span aria-hidden="true">←</span></a>
                        <a href="{{ route('store.index') }}">فروشگاه منابع <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
                <div class="public-library-counts">
                    <span><strong>{{ App\Support\PersianUi::digits($myCourses->count() + $childCourses->count()) }}</strong><small>دوره فعال</small></span>
                    <span><strong>{{ App\Support\PersianUi::digits($products->count()) }}</strong><small>منبع خریداری‌شده</small></span>
                    <div class="public-library-motif" aria-hidden="true"><span></span><span></span><span></span></div>
                </div>
            </header>

            @if($pendingOrders->isNotEmpty())
                <section class="public-library-section public-library-orders" aria-labelledby="pending-orders-title">
                    <div class="public-section-heading">
                        <div>
                            <span>خریدهای در جریان</span>
                            <h2 id="pending-orders-title">سفارش‌های منتظر تأیید</h2>
                            <p>رسید یا ثبت سفارش به‌تنهایی دسترسی نمی‌سازد. از این‌جا وضعیت پرداخت را پیگیری کن.</p>
                        </div>
                    </div>
                    <div class="public-library-orders-list">
                        @foreach($pendingOrders as $order)
                            @php($pendingItem = $order->items->first())
                            @php($pendingTitle = $pendingItem?->product_title_snapshot ?: ($pendingItem?->course?->title ?: ($pendingItem?->product?->title ?: 'محتوای سفارش')))
                            @php($proofRejected = $order->payments->contains(fn ($payment) => $payment->status === 'rejected'))
                            <article class="public-library-order-row">
                                <div class="public-library-order-mark" aria-hidden="true">{{ $proofRejected ? '!' : '◷' }}</div>
                                <div class="public-library-order-copy">
                                    <span>{{ $proofRejected ? 'رسید نیاز به بررسی دوباره دارد' : 'در انتظار تطبیق پرداخت' }}</span>
                                    <h3>{{ $pendingTitle }}</h3>
                                    <p>سفارش {{ $order->order_number }} · {{ App\Support\PersianUi::money($order->total) }}</p>
                                </div>
                                <a href="{{ route('orders.show', $order) }}" class="public-library-order-action">
                                    {{ $proofRejected ? 'بررسی سفارش و رسید' : 'پیگیری سفارش' }}
                                    <span aria-hidden="true">←</span>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($isParent)
                <section class="public-library-section" aria-labelledby="family-courses-title">
                    <div class="public-section-heading"><div><span>دسترسی‌های خانوادگی</span><h2 id="family-courses-title">دوره‌های فرزندان</h2><p>پس از تأیید سفارش، هر دوره به حساب دانش‌آموز دریافت‌کننده اضافه می‌شود.</p></div><a href="{{ route('parent.dashboard') }}">پیگیری فرزندان <span aria-hidden="true">←</span></a></div>
                    @if($childCourses->isNotEmpty())
                        <div class="public-library-course-grid">
                            @foreach($childCourses as $enrollment)
                                <article class="public-library-course-card">
                                    <div class="public-library-course-media">
                                        @if($enrollment->course?->media->first()?->url())
                                            <img src="{{ $enrollment->course->media->first()->url() }}" alt="{{ $enrollment->course->title }}" loading="lazy">
                                        @else
                                            <div class="public-cover-art public-cover-art--course"><span>مسیر یادگیری</span><strong>{{ $enrollment->course?->title }}</strong><i aria-hidden="true">ش</i></div>
                                        @endif
                                    </div>
                                    <div class="public-library-course-body">
                                        <span class="public-library-kicker">{{ $enrollment->student?->name }} · دسترسی فعال</span>
                                        <h3>{{ $enrollment->course?->title }}</h3><p>{{ $enrollment->course?->academy?->name }}</p>
                                        <a href="{{ route('courses.show', $enrollment->course) }}" class="public-library-action">مشاهده اطلاعات دوره <span aria-hidden="true">←</span></a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <x-ui.empty-state title="هنوز دوره‌ی پولی فعالی برای فرزندان ثبت نشده" description="اگر سفارشی در انتظار بررسی است، وضعیت آن را از صفحه سفارش دنبال کن. پس از تأیید پرداخت، دوره در این فهرست دیده می‌شود.">
                            <a href="{{ route('courses.index') }}" class="public-library-inline-link">دیدن دوره‌ها <span aria-hidden="true">←</span></a>
                        </x-ui.empty-state>
                    @endif
                </section>
            @elseif($isStudent)
                <section class="public-library-section" aria-labelledby="my-courses-title">
                    <div class="public-section-heading"><div><span>مسیرهای آموزشی</span><h2 id="my-courses-title">دوره‌های من</h2><p>دوره‌های رایگان ثبت‌نام‌شده و دوره‌های پولیِ تأییدشده.</p></div><a href="{{ route('student.courses.index') }}">رفتن به پنل یادگیری <span aria-hidden="true">←</span></a></div>
                    @if($myCourses->isNotEmpty())
                        <div class="public-library-course-grid">
                            @foreach($myCourses as $enrollment)
                                <article class="public-library-course-card">
                                    <div class="public-library-course-media">
                                        @if($enrollment->course?->media->first()?->url())
                                            <img src="{{ $enrollment->course->media->first()->url() }}" alt="{{ $enrollment->course->title }}" loading="lazy">
                                        @else
                                            <div class="public-cover-art public-cover-art--course"><span>مسیر یادگیری</span><strong>{{ $enrollment->course?->title }}</strong><i aria-hidden="true">ش</i></div>
                                        @endif
                                    </div>
                                    <div class="public-library-course-body">
                                        <span class="public-library-kicker">دسترسی فعال</span>
                                        <h3>{{ $enrollment->course?->title }}</h3><p>{{ $enrollment->course?->academy?->name }}</p>
                                        <a href="{{ route('student.courses.show', $enrollment->course) }}" class="public-library-action">ادامه یادگیری <span aria-hidden="true">←</span></a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <x-ui.empty-state title="هنوز دوره‌ای در کتابخانه‌ی تو نیست" description="با ثبت‌نام در یک دوره رایگان یا تأیید خرید یک دوره پولی، مسیر یادگیری اینجا نمایش داده می‌شود.">
                            <a href="{{ route('courses.index') }}" class="public-library-inline-link">پیدا کردن دوره مناسب <span aria-hidden="true">←</span></a>
                        </x-ui.empty-state>
                    @endif
                </section>
            @endif

            <section class="public-library-section" aria-labelledby="my-resources-title">
                <div class="public-section-heading"><div><span>فایل‌های امن</span><h2 id="my-resources-title">منابع و محصولات خریداری‌شده</h2><p>دانلود تنها برای فایل‌های فعال و سفارش‌هایی که پرداخت آن‌ها تأیید شده مجاز است.</p></div><a href="{{ route('store.index') }}">فروشگاه <span aria-hidden="true">←</span></a></div>
                @if($products->isNotEmpty())
                    <div class="public-library-product-grid">
                        @foreach($products as $entitlement)
                            @php($order = $entitlement->orderItem?->order)
                            <article class="public-library-product-card">
                                <div class="public-library-product-media">
                                    @if($entitlement->product?->media->first()?->url())
                                        <img src="{{ $entitlement->product->media->first()->url() }}" alt="{{ $entitlement->product->title }}" loading="lazy">
                                    @else
                                        <div class="public-cover-art public-cover-art--resource"><span>منبع آموزشی</span><strong>{{ $entitlement->product?->title }}</strong><i aria-hidden="true">↗</i></div>
                                    @endif
                                </div>
                                <div class="public-library-product-body">
                                    <span class="public-library-kicker">{{ $entitlement->product?->category?->name ?: 'منبع آموزشی' }} · دسترسی فعال</span>
                                    <h3>{{ $entitlement->product?->title }}</h3>
                                    <p>سفارش {{ $order?->order_number }} · {{ $order?->paid_at ? App\Support\PersianUi::date($order->paid_at) : 'پرداخت تأییدشده' }}</p>
                                    <div class="public-library-downloads">
                                        @forelse($entitlement->product?->files ?? [] as $downloadFile)
                                            @if($order && $downloadFile->media?->status === 'active' && $downloadFile->media?->visibility === 'private')
                                                <a href="{{ route('orders.files.download', [$order, $downloadFile]) }}">دریافت نسخه محافظت‌شده <span aria-hidden="true">↓</span></a>
                                            @endif
                                        @empty
                                            <span>فایل فعالی برای دریافت ثبت نشده است.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state title="هنوز منبع دانلودی خریداری‌شده‌ای نیست" description="پس از تأیید پرداخت یک محصول دیجیتال، نسخه‌ی امن آن از همین صفحه قابل دریافت است.">
                        <a href="{{ route('store.index') }}" class="public-library-inline-link">رفتن به فروشگاه <span aria-hidden="true">←</span></a>
                    </x-ui.empty-state>
                @endif
            </section>
        </x-layout.container>
    </x-layout.section>
</section>
@endsection
