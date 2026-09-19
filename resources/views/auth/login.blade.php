@extends('layouts.auth')

@section('title', 'ورود | شیخان')

@section('content')
<div class="rounded-[2rem] border border-[var(--color-border)] bg-white p-6 shadow-[var(--shadow-xl)] sm:p-8">
    <div class="mb-8">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-lg font-black text-[var(--color-primary-700)]">ش</div>
        <h1 class="mt-6 text-2xl font-black text-[var(--color-text)] sm:text-3xl">خوش برگشتی.</h1>
        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
            وارد حساب شیخان شو تا مستقیم به فضای مخصوص نقش خودت بروی.
        </p>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-[var(--color-danger-100)] bg-[var(--color-danger-50)] p-4 text-sm text-[var(--color-danger-700)]">
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
            <label for="email" class="mb-2 block text-sm font-bold text-[var(--color-text)]">ایمیل</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="you@example.com">
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-bold text-[var(--color-text)]">رمز عبور</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="رمز عبور">
        </div>

        <label class="flex items-center gap-3 text-sm text-[var(--color-text-secondary)]">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                   class="h-4 w-4 rounded border-[var(--color-border-strong)] text-[var(--color-primary-600)] focus:ring-[var(--color-primary-100)]">
            <span>مرا به خاطر بسپار</span>
        </label>

        <button type="submit"
                class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-5 text-sm font-black text-white shadow-[0_10px_24px_rgba(83,98,223,.18)] transition hover:-translate-y-0.5 hover:bg-[var(--color-primary-700)] hover:shadow-[0_14px_30px_rgba(83,98,223,.22)]">
            ورود به حساب <span class="ms-2" aria-hidden="true">←</span>
        </button>
    </form>

    <div class="my-6 flex items-center gap-3 text-xs text-[var(--color-text-muted)]">
        <span class="h-px flex-1 bg-[var(--color-border)]"></span><span>یا</span><span class="h-px flex-1 bg-[var(--color-border)]"></span>
    </div>

    <p class="text-center text-sm text-[var(--color-text-secondary)]">
        هنوز حساب نداری؟
        <a href="{{ route('register') }}" class="font-black text-[var(--color-primary-600)] hover:text-[var(--color-primary-700)]">ثبت‌نام کن</a>
    </p>
</div>

<div class="mt-5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-4">
    <div class="text-xs font-black text-[var(--color-text)]">حساب‌های تست</div>
    <div class="mt-2 grid gap-1 text-xs leading-6 text-[var(--color-text-muted)]">
        <span>مدیر: <b dir="ltr">owner@sheykhan.test</b></span>
        <span>مدرس: <b dir="ltr">teacher1@sheykhan.test</b></span>
        <span>دانش‌آموز: <b dir="ltr">student1@sheykhan.test</b></span>
        <span>والد: <b dir="ltr">parent@sheykhan.test</b></span>
    </div>
    <p class="mt-2 text-xs text-[var(--color-text-muted)]">رمز همه: <b dir="ltr">password</b></p>
</div>
@endsection
