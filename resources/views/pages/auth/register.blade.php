@extends('layouts.auth')

@section('title', 'ثبت‌نام')

@section('content')

    <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-sm)] sm:p-8">

        {{-- Header --}}
        <div class="text-center">

            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-brand-50)] text-xl font-black text-[var(--color-brand-700)]">
                ف
            </div>

            <h1 class="mt-5 text-2xl font-black text-[var(--color-text-primary)]">
                ساخت حساب در فرزین
            </h1>

            <p class="mt-2 text-sm leading-6 text-[var(--color-text-secondary)]">
                اطلاعاتت را وارد کن تا مسیر یادگیریت را شروع کنیم.
            </p>

        </div>

        {{-- Form --}}
        <form
            method="POST"
            action="#"
            class="mt-8 space-y-5"
        >
            @csrf

            {{-- Full name --}}
            <x-ui.input
                type="text"
                name="name"
                label="نام و نام خانوادگی"
                placeholder="نام و نام خانوادگی"
                autocomplete="name"
                required
            />

            {{-- Mobile --}}
            <x-ui.input
                type="tel"
                name="phone"
                label="شماره موبایل"
                placeholder="۰۹۱۲۱۲۳۴۵۶۷"
                inputmode="tel"
                autocomplete="tel"
                required
            />

            {{-- Role --}}
            <fieldset>
                <legend class="ui-label">
                    نوع حساب
                </legend>

                <div class="mt-2 space-y-3">

                    <x-ui.radio
                        name="role"
                        value="student"
                        label="دانش‌آموز"
                        description="برای شرکت در دوره‌ها، کلاس‌ها و آزمون‌ها"
                        checked
                    />

                    <x-ui.radio
                        name="role"
                        value="parent"
                        label="والد"
                        description="برای مشاهده وضعیت و پیشرفت فرزند"
                    />

                </div>
            </fieldset>

            {{-- Password --}}
            <x-ui.input
                type="password"
                name="password"
                label="رمز عبور"
                placeholder="یک رمز عبور قوی انتخاب کنید"
                autocomplete="new-password"
                required
            />

            {{-- Password confirmation --}}
            <x-ui.input
                type="password"
                name="password_confirmation"
                label="تکرار رمز عبور"
                placeholder="رمز عبور را دوباره وارد کنید"
                autocomplete="new-password"
                required
            />

            {{-- Terms --}}
            <x-ui.checkbox
                name="terms"
                value="1"
                required
                label="قوانین و مقررات فرزین را می‌پذیرم."
            />

            {{-- Submit --}}
            <x-ui.button
                type="submit"
                size="lg"
                full-width
            >
                ساخت حساب
            </x-ui.button>

        </form>

        {{-- Login --}}
        <div class="mt-7 border-t border-[var(--color-border)] pt-6 text-center">

            <p class="text-sm text-[var(--color-text-secondary)]">
                قبلاً حساب ساخته‌ای؟
            </p>

            <a
                href="#"
                class="mt-2 inline-flex text-sm font-bold text-[var(--color-brand-600)] transition-colors hover:text-[var(--color-brand-700)]"
            >
                ورود به حساب
            </a>

        </div>

    </div>

@endsection
