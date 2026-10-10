@extends('layouts.app')
@section('title', 'خرید دوره ' . $course->title . ' | شیخان')
@section('description', 'ثبت سفارش امن برای دوره آموزشی ' . $course->title)
@section('content')
<section class="public-checkout-page">
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <nav aria-label="مسیر صفحه" class="public-checkout-breadcrumb">
                <a href="{{ route('home') }}">خانه</a><span>/</span>
                <a href="{{ route('courses.index') }}">دوره‌ها</a><span>/</span>
                <a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a><span>/</span>
                <strong>ثبت سفارش</strong>
            </nav>

            <header class="public-checkout-heading">
                <span class="ui-eyebrow">مسیر خرید دوره</span>
                <h1>دوره را انتخاب کن؛ دسترسی بعد از تأیید فعال می‌شود.</h1>
                <p>رسید واریز به‌تنهایی دسترسی ایجاد نمی‌کند. آموزشگاه پس از تطبیق وجه با گردش بانکی، دسترسی آموزشی را به حساب دانش‌آموزِ انتخاب‌شده اضافه می‌کند.</p>
            </header>

            @if(session('success'))<div role="status" class="public-feedback public-feedback--success">{{ session('success') }}</div>@endif
            @if($errors->any())<div role="alert" class="public-feedback public-feedback--error"><strong>لطفاً موارد زیر را بررسی کن.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <div class="public-checkout-layout">
                @if($canPurchase)
                    <form method="POST" action="{{ route('checkout.course.store', $course) }}" class="public-checkout-form">
                        @csrf
                        @if($isParent)
                            <section class="public-checkout-block">
                                <span class="public-checkout-step">مرحله ۱</span>
                                <h2>این دوره برای کدام فرزند است؟</h2>
                                <p>پرداخت از حساب والد انجام می‌شود، اما دسترسی آموزشی به حساب دانش‌آموز انتخاب‌شده اضافه خواهد شد.</p>
                                <label class="public-form-label" for="beneficiary_id">دانش‌آموز دریافت‌کننده</label>
                                <select id="beneficiary_id" name="beneficiary_id" required class="ui-control">
                                    @foreach($beneficiaries as $beneficiary)
                                        <option value="{{ $beneficiary->id }}" @selected((string) $selectedBeneficiaryId === (string) $beneficiary->id)>{{ $beneficiary->name }}</option>
                                    @endforeach
                                </select>
                            </section>
                        @else
                            <section class="public-checkout-block">
                                <span class="public-checkout-step">مرحله ۱</span>
                                <h2>دسترسی به حساب خودت اضافه می‌شود</h2>
                                <p>پس از تأیید پرداخت، دوره در «دوره‌های من» همین حساب در دسترس خواهد بود.</p>
                            </section>
                        @endif

                        <section class="public-checkout-block">
                            <span class="public-checkout-step">مرحله ۲</span>
                            <h2>اطلاعات خریدار</h2>
                            <div class="public-form-grid">
                                <label class="public-form-label">نام و نام خانوادگی
                                    <input required name="billing_name" maxlength="160" value="{{ old('billing_name', auth()->user()->name) }}" class="ui-control">
                                </label>
                                <label class="public-form-label">شماره تماس
                                    <input required name="billing_mobile" maxlength="32" value="{{ old('billing_mobile', auth()->user()->mobile) }}" class="ui-control" inputmode="tel">
                                </label>
                            </div>
                        </section>

                        <section class="public-checkout-block">
                            <span class="public-checkout-step">مرحله ۳</span>
                            <h2>شرایط خرید و حق نشر</h2>
                            @if($legalReady)
                                <p>نسخه و اثرانگشت هر سند همراه سفارش ثبت می‌شود.</p>
                                @foreach($documents as $document)
                                    <article class="public-legal-document">
                                        <h3>{{ $document->title }} <span>نسخه {{ $document->version }}</span></h3>
                                        <div class="public-legal-document__body">{{ $document->content }}</div>
                                        <label class="public-consent">
                                            <input type="checkbox" name="consents[{{ $document->id }}]" value="1" required @checked(old('consents.'.$document->id))>
                                            <span>متن «{{ $document->title }}» نسخه {{ $document->version }} را خواندم و می‌پذیرم.</span>
                                        </label>
                                    </article>
                                @endforeach
                            @else
                                <div class="public-feedback public-feedback--warning">سفارش تا انتشار نسخه‌ی رسمی شرایط خرید و کپی‌رایت توسط مدیر متوقف است.</div>
                            @endif
                        </section>

                        <section class="public-checkout-block">
                            <span class="public-checkout-step">مرحله ۴</span>
                            <h2>پرداخت و فعال‌سازی</h2>
                            @if($transferReady)
                                <div class="public-bank-details">
                                    <div><span>بانک</span><strong>{{ $bank['bank_name'] }}</strong></div>
                                    <div><span>به نام</span><strong>{{ $bank['account_holder'] }}</strong></div>
                                    <div class="public-bank-details__wide"><span>شبا</span><strong dir="ltr">{{ $bank['iban'] }}</strong></div>
                                </div>
                                <p>پس از ثبت سفارش، در صفحه‌ی سفارش رسید واریز را ارسال کن. تا وقتی رسید با حساب بانکی تطبیق داده نشده، دوره قفل می‌ماند.</p>
                            @else
                                <div class="public-feedback public-feedback--warning">اطلاعات بانکی آموزشگاه هنوز تنظیم نشده است و فعلاً سفارشی ثبت نمی‌شود.</div>
                            @endif
                        </section>

                        <button type="submit" @disabled(!$canSubmit) class="public-checkout-submit">
                            ثبت سفارش {{ App\Support\PersianUi::money($price) }} <span aria-hidden="true">←</span>
                        </button>
                        <p class="public-checkout-note">این خرید مجوز دانلود ویدئو یا فایل خام دوره نیست؛ دسترسی آموزشی فقط از مسیر امن درس‌ها داده می‌شود.</p>
                    </form>
                @else
                    <section class="public-checkout-block public-checkout-block--unavailable">
                        <div class="public-checkout-unavailable-icon" aria-hidden="true">!</div>
                        <h2>فعلاً امکان خرید برای این حساب وجود ندارد</h2>
                        <p>{{ $accessMessage ?: 'دسترسی این دوره قبلاً فعال شده یا حساب خریدار برای این آموزشگاه واجد شرایط نیست.' }}</p>
                        @if(auth()->user()?->hasRole('student'))
                            <a class="public-checkout-submit" href="{{ route('student.courses.index') }}">رفتن به دوره‌های من</a>
                        @elseif(auth()->user()?->hasRole('parent'))
                            <a class="public-checkout-submit" href="{{ route('parent.dashboard') }}">رفتن به پنل والد</a>
                        @endif
                    </section>
                @endif

                <aside class="public-checkout-summary">
                    <div class="public-checkout-summary__media">
                        @if($course->media->first()?->url())
                            <img src="{{ $course->media->first()->url() }}" alt="{{ $course->title }}" fetchpriority="high">
                        @else
                            <div class="public-cover-art public-cover-art--course"><span>مسیر یادگیری</span><strong>{{ $course->title }}</strong><i aria-hidden="true">ش</i></div>
                        @endif
                    </div>
                    <div class="public-checkout-summary__body">
                        <span class="public-product-type">دوره آموزشی</span>
                        <h2>{{ $course->title }}</h2>
                        <p>{{ $course->academy?->name }}</p>
                        <div class="public-checkout-price"><span>مبلغ سفارش</span><strong>{{ App\Support\PersianUi::money($price) }}</strong></div>
                        <div class="public-checkout-summary__foot"><span>دسترسی پس از تأیید بانکی</span><span>مشاهده در پنل یادگیری</span></div>
                    </div>
                </aside>
            </div>
        </x-layout.container>
    </x-layout.section>
</section>
@endsection
