@extends('layouts.auth')

@section('title', 'ورود')

@section('content')

    <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-sm)] sm:p-8">

        {{-- Header --}}
        <div class="text-center">

            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-brand-50)] text-xl font-black text-[var(--color-brand-700)]">
                ف
            </div>

            <h1 class="mt-5 text-2xl font-black text-[var(--color-text-primary)]">
                خوش برگشتی 👋
            </h1>

            <p class="mt-2 text-sm leading-6 text-[var(--color-text-secondary)]">
                برای ورود به حساب فرزین، اطلاعاتت را وارد کن.
            </p>

        </div>

        {{-- Form --}}
        <form
            method="POST"
            action="#"
            class="mt-8 space-y-5"
        >
            @csrf

            <x-ui.input
                type="text"
                name="phone"
                label="شماره موبایل"
                placeholder="مثلاً ۰۹۱۲۱۲۳۴۵۶۷"
                inputmode="tel"
                autocomplete="tel"
                required
            />

            <x-ui.input
                type="password"
                name="password"
                label="رمز عبور"
                placeholder="رمز عبور خود را وارد کنید"
                autocomplete="current-password"
                required
            />

            <div class="flex items-center justify-between gap-4">

                <x-ui.checkbox
                    name="remember"
                    value="1"
                    label="مرا به خاطر بسپار"
                />

                <a
                    href="#"
                    class="text-sm font-semibold text-[var(--color-brand-600)] transition-colors hover:text-[var(--color-brand-700)]"
                >
                    فراموشی رمز عبور؟
                </a>

            </div>

            <x-ui.button
                type="submit"
                size="lg"
                full-width
            >
                ورود به حساب
            </x-ui.button>

        </form>

        {{-- Divider --}}
        <div class="my-7 flex items-center gap-4">
            <span class="h-px flex-1 bg-[var(--color-border)]"></span>

            <span class="text-xs text-[var(--color-text-muted)]">
                یا
            </span>

            <span class="h-px flex-1 bg-[var(--color-border)]"></span>
        </div>

        {{-- Register --}}
        <div class="text-center">
            <p class="text-sm text-[var(--color-text-secondary)]">
                هنوز حساب کاربری نداری؟
            </p>

            <a
                href="#"
                class="mt-2 inline-flex text-sm font-bold text-[var(--color-brand-600)] transition-colors hover:text-[var(--color-brand-700)]"
            >
                ساخت حساب جدید
            </a>
        </div>

    </div>

@endsection
