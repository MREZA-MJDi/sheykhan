@extends('layouts.auth')

@section('title', 'ورود | شیخان')

@section('content')
@php
    $testAccounts = config('role_access.test_accounts', []);
    $testPassword = config('role_access.test_password');
    $dashboardRoutes = config('role_access.dashboard_routes', []);
@endphp

<div class="rounded-[2rem] border border-[var(--color-border)] bg-white p-6 shadow-[var(--shadow-xl)] sm:p-8">
    <div class="mb-8">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-lg font-black text-[var(--color-primary-700)]">ش</div>

        <h1 class="mt-6 text-2xl font-black text-[var(--color-text)] sm:text-3xl">
            خوش برگشتی.
        </h1>

        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
            وارد حساب شیخان شو تا مستقیم به فضای مخصوص نقش خودت بروی.
        </p>
    </div>

    @if($errors->any())
        <div role="alert" aria-live="assertive" class="mb-5 rounded-2xl border border-[var(--color-danger-100)] bg-[var(--color-danger-50)] p-4 text-sm text-[var(--color-danger-700)]">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="identifier" class="mb-2 block text-sm font-bold text-[var(--color-text)]">
                ایمیل، شماره موبایل یا شناسه قدیمی دانش‌آموز
            </label>

            <input
                id="identifier"
                name="identifier"
                type="text"
                value="{{ old('identifier') }}"
                autocomplete="username"
                autofocus
                required
                class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                placeholder="ایمیل، ۰۹۱۲... یا شناسه قدیمی"
            >
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-bold text-[var(--color-text)]">
                رمز عبور
            </label>

            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                placeholder="رمز عبور"
            >
        </div>

        <label class="flex items-center gap-3 text-sm text-[var(--color-text-secondary)]">
            <input
                type="checkbox"
                name="remember"
                value="1"
                @checked(old('remember'))
                class="h-4 w-4 rounded border-[var(--color-border-strong)] text-[var(--color-primary-600)] focus:ring-[var(--color-primary-100)]"
            >
            <span>مرا به خاطر بسپار</span>
        </label>

        <button
            type="submit"
            class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-5 text-sm font-black text-white shadow-[0_10px_24px_rgba(83,98,223,.18)] transition hover:-translate-y-0.5 hover:bg-[var(--color-primary-700)] hover:shadow-[0_14px_30px_rgba(83,98,223,.22)]"
        >
            ورود به حساب
            <span class="ms-2" aria-hidden="true">←</span>
        </button>
    </form>

    <div class="mt-5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-4">
        <strong class="block text-sm font-black text-[var(--color-text)]">دانش‌آموز سامانه قبلی شیخان هستی؟</strong>
        <p class="mt-2 text-xs leading-7 text-[var(--color-text-secondary)]">
            اگر از سامانه قبلی شیخان وارد می‌شوی، شناسهٔ عددی کاربر قدیمی را وارد کن و از رمز موقتی که آموزشگاه در اختیارت گذاشته استفاده کن. برای امنیت حساب، بعد از اولین ورود باید رمز اختصاصی جدید تعیین کنی و تا آن زمان پنل باز نمی‌شود. اگر شناسه یا رمز موقت را نداری، با آموزشگاه تماس بگیر.
        </p>
    </div>

    <div class="my-6 flex items-center gap-3 text-xs text-[var(--color-text-muted)]">
        <span class="h-px flex-1 bg-[var(--color-border)]"></span>
        <span>یا</span>
        <span class="h-px flex-1 bg-[var(--color-border)]"></span>
    </div>

    <p class="text-center text-sm text-[var(--color-text-secondary)]">
        هنوز حساب نداری؟
        <a href="{{ route('register') }}" class="font-black text-[var(--color-primary-600)] hover:text-[var(--color-primary-700)]">
            ثبت‌نام کن
        </a>
    </p>
</div>

@if(app()->environment('local', 'testing'))
<div class="mt-5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="text-sm font-black text-[var(--color-text)]">
                حساب‌های تست همکار
            </div>

            <p class="mt-1 text-xs leading-6 text-[var(--color-text-muted)]">
                همه حساب‌ها از Seeder واقعی دیتابیس ساخته شده‌اند و مسیر صحیح داشبورد هر نقش نیز نمایش داده می‌شود.
            </p>
        </div>

        <span class="rounded-full bg-[var(--color-primary-50)] px-2.5 py-1 text-[10px] font-bold text-[var(--color-primary-700)]">
            TEST
        </span>
    </div>

    @forelse($testAccounts as $account)
        @php
            $routeName = $dashboardRoutes[$account['role_key']] ?? null;
            $dashboardUrl = $routeName ? route($routeName) : null;
        @endphp

        <div class="mt-3 rounded-xl border border-[var(--color-border)] bg-white p-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <div class="text-xs font-black text-[var(--color-text)]">
                        {{ $account['role'] }}
                    </div>

                    <div class="mt-0.5 text-[11px] text-[var(--color-text-secondary)]">
                        {{ $account['name'] }}
                    </div>
                </div>

                @if($dashboardUrl)
                    <a
                        href="{{ $dashboardUrl }}"
                        class="rounded-lg bg-[var(--color-primary-50)] px-2.5 py-1.5 text-[10px] font-bold text-[var(--color-primary-700)]"
                    >
                        داشبورد
                    </a>
                @endif
            </div>

            <div class="mt-2 grid gap-1 text-xs text-[var(--color-text-secondary)]">
                <div class="overflow-x-auto">
                    <span class="font-bold">Username:</span>
                    <code dir="ltr">{{ $account['username'] }}</code>
                </div>

                @if($testPassword)
                    <div class="overflow-x-auto">
                        <span class="font-bold">Password:</span>
                        <code dir="ltr">{{ $testPassword }}</code>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            data-test-login-fill
                            data-username="{{ $account['username'] }}"
                            data-password="{{ $testPassword }}"
                            aria-label="پر کردن فرم ورود با حساب {{ $account['role'] }}"
                            class="inline-flex min-h-9 items-center justify-center rounded-lg bg-[var(--color-primary-600)] px-3 py-2 text-[11px] font-bold text-white transition hover:bg-[var(--color-primary-700)] focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-100)]"
                        >
                            پر کردن فرم ورود
                        </button>

                        <button
                            type="button"
                            data-test-login-copy
                            data-username="{{ $account['username'] }}"
                            data-password="{{ $testPassword }}"
                            aria-label="کپی یوزرنیم و پسورد حساب {{ $account['role'] }}"
                            class="inline-flex min-h-9 items-center justify-center rounded-lg border border-[var(--color-border)] bg-white px-3 py-2 text-[11px] font-bold text-[var(--color-text)] transition hover:border-[var(--color-primary-400)] hover:bg-[var(--color-primary-50)] focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-100)]"
                        >
                            کپی یوزرنیم و پسورد
                        </button>
                    </div>

                    <p data-test-login-feedback role="status" aria-live="polite" class="mt-2 min-h-4 text-[11px] text-[var(--color-success-700)]"></p>
                @endif

                @if($dashboardUrl)
                    <div class="overflow-x-auto">
                        <span class="font-bold">Path:</span>
                        <code dir="ltr">{{ $dashboardUrl }}</code>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="mt-3 rounded-xl border border-[var(--color-border)] bg-white px-3 py-3 text-xs text-[var(--color-text-muted)]">
            حساب تستی در تنظیمات تعریف نشده است.
        </div>
    @endforelse

    @if(!$testPassword)
        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
            <span class="font-bold">SEED_USER_PASSWORD</span> در .env تنظیم نشده است.
        </div>
    @endif
</div>
@endif
@endsection

@if(app()->environment('local', 'testing'))
@push('scripts')
<script>
(() => {
    'use strict';

    const getFeedback = (button) => button.closest('.rounded-xl')?.querySelector('[data-test-login-feedback]');

    document.querySelectorAll('[data-test-login-fill]').forEach((button) => {
        button.addEventListener('click', () => {
            const identifier = document.getElementById('identifier');
            const password = document.getElementById('password');

            if (!identifier || !password) return;

            identifier.value = button.dataset.username || '';
            password.value = button.dataset.password || '';

            identifier.dispatchEvent(new Event('input', { bubbles: true }));
            identifier.dispatchEvent(new Event('change', { bubbles: true }));
            password.dispatchEvent(new Event('input', { bubbles: true }));
            password.dispatchEvent(new Event('change', { bubbles: true }));

            const feedback = getFeedback(button);
            if (feedback) feedback.textContent = 'فرم پر شد؛ برای ورود روی «ورود به حساب» بزن.';
            password.focus();
        });
    });

    document.querySelectorAll('[data-test-login-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const credentials = `Username: ${button.dataset.username || ''}\nPassword: ${button.dataset.password || ''}`;
            const feedback = getFeedback(button);

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(credentials);
                } else {
                    throw new Error('Clipboard API unavailable');
                }
            } catch {
                // Fallback supports browsers/HTTP environments where Clipboard API is unavailable.
                const textarea = document.createElement('textarea');
                textarea.value = credentials;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                textarea.style.pointerEvents = 'none';
                document.body.appendChild(textarea);
                textarea.select();

                let copied = false;
                try {
                    copied = document.execCommand('copy');
                } finally {
                    textarea.remove();
                }

                if (!copied) {
                    if (feedback) feedback.textContent = 'کپی خودکار در این مرورگر انجام نشد؛ از «پر کردن فرم ورود» استفاده کن.';
                    return;
                }
            }

            if (feedback) feedback.textContent = 'یوزرنیم و پسورد کپی شد.';
        });
    });
})();
</script>
@endpush
@endif

