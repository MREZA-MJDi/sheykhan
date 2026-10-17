@extends('layouts.auth')

@section('title', 'ورود | شیخان')

@section('content')


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

    @if(app()->environment('local'))
        @php
            $demoPassword = filled(env('SEED_USER_PASSWORD'))
                ? (string) env('SEED_USER_PASSWORD')
                : 'Sheykhan@12345';
        @endphp

        <section class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-4" aria-labelledby="local-demo-accounts-title">
            <div class="mb-3">
                <h2 id="local-demo-accounts-title" class="text-sm font-black text-amber-950">ورود سریع برای تست پنل‌ها</h2>
                <p class="mt-1 text-xs leading-6 text-amber-900">فقط در محیط Local نمایش داده می‌شود. روی هر نقش بزن تا ایمیل و رمز فرم بالا پر شود؛ سپس خودت دکمهٔ «ورود به حساب» را بزن.</p>
                <p class="mt-1 break-all text-[11px] text-amber-900">رمز پیش‌فرض: <code dir="ltr" class="rounded bg-white/70 px-1">{{ $demoPassword }}</code></p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach([
                    ['label' => 'مدیر آموزشگاه', 'email' => 'owner@sheykhan.test'],
                    ['label' => 'مدرس', 'email' => 'teacher.math@sheykhan.test'],
                    ['label' => 'دانش‌آموز', 'email' => 'student.armin@sheykhan.test'],
                    ['label' => 'والد', 'email' => 'parent.armin@sheykhan.test'],
                    ['label' => 'متخصص SEO', 'email' => 'seo@sheykhan.test'],
                ] as $demoAccount)
                    <button
                        type="button"
                        data-demo-login
                        data-demo-identifier="{{ $demoAccount['email'] }}"
                        data-demo-password="{{ $demoPassword }}"
                        class="min-h-10 rounded-xl border border-amber-300 bg-white px-3 py-2 text-xs font-bold text-amber-950 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                    >{{ $demoAccount['label'] }}</button>
                @endforeach
            </div>
        </section>

        @push('scripts')
            <script>
                (() => {
                    document.querySelectorAll('[data-demo-login]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const identifier = document.getElementById('identifier');
                            const password = document.getElementById('password');

                            if (!identifier || !password) return;

                            identifier.value = button.dataset.demoIdentifier || '';
                            password.value = button.dataset.demoPassword || '';
                            identifier.dispatchEvent(new Event('input', { bubbles: true }));
                            password.dispatchEvent(new Event('input', { bubbles: true }));
                            password.focus();
                        });
                    });
                })();
            </script>
        @endpush
    @endif

    <div class="mt-4 rounded-2xl border border-[var(--color-primary-100)] bg-[var(--color-primary-50)] p-4">
        <strong class="block text-sm font-black text-[var(--color-primary-800)]">دانش‌آموز سامانه قبلی شیخان هستی؟</strong>
        <p class="mt-2 text-xs leading-7 text-[var(--color-text-secondary)]">
            اگر در حساب قبلی ایمیل یا شماره موبایل نداشتی، شناسهٔ عددی کاربر در سامانه قبلی را در کادر بالا وارد کن و همان رمز قبلی را بزن. پس از ورود موفق، رمز با روش امن شیخان ذخیره می‌شود. اگر شناسه یا رمز را نداری، از آموزشگاه درخواست بازیابی حساب کن.
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


@endsection
