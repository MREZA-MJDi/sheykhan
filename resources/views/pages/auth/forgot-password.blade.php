@extends('layouts.auth')

@section('title', 'بازیابی رمز عبور')

@section('content')

    <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-sm)] sm:p-8">

        {{-- Header --}}
        <div class="text-center">

            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">
                <svg
                    class="h-7 w-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <path d="M7.5 10V8a4.5 4.5 0 0 1 9 0v2" />
                    <rect x="4.5" y="10" width="15" height="10.5" rx="2" />
                    <path d="M12 14v2.5" />
                </svg>
            </div>

            <h1 class="mt-5 text-2xl font-black text-[var(--color-text-primary)]">
                رمز عبورت رو فراموش کردی؟
            </h1>

            <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                شماره موبایل حسابت را وارد کن تا مراحل بازیابی رمز عبور را شروع کنیم.
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
                type="tel"
                name="phone"
                label="شماره موبایل"
                placeholder="۰۹۱۲۱۲۳۴۵۶۷"
                inputmode="tel"
                autocomplete="tel"
                required
            />

            <x-ui.button
                type="submit"
                size="lg"
                full-width
            >
                ارسال کد بازیابی

                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </x-ui.button>

        </form>

        {{-- Back to login --}}
        <div class="mt-7 border-t border-[var(--color-border)] pt-6 text-center">

            <a
                href="#"
                class="inline-flex items-center gap-2 text-sm font-bold text-[var(--color-brand-600)] transition-colors hover:text-[var(--color-brand-700)]"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="m15 18-6-6 6-6" />
                </svg>

                بازگشت به صفحه ورود
            </a>

        </div>

    </div>

@endsection
