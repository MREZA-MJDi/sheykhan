@extends('layouts.auth')

@section('title', 'تغییر رمز عبور | شیخان')

@section('content')
<div class="rounded-[2rem] border border-[var(--color-border)] bg-white p-6 shadow-[var(--shadow-xl)] sm:p-8">
    <div class="mb-8">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-lg font-black text-[var(--color-primary-700)]">ش</div>
        <h1 class="mt-6 text-2xl font-black text-[var(--color-text)] sm:text-3xl">رمز عبورت را تغییر بده.</h1>
        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
            برای امنیت حساب، رمز موقت فقط برای ورود اولیه است. قبل از رفتن به پنل، یک رمز جدید و اختصاصی انتخاب کن.
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

    <form method="POST" action="{{ route('password.change.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="password" class="mb-2 block text-sm font-bold text-[var(--color-text)]">رمز عبور جدید</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required autofocus
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="حداقل ۱۲ کاراکتر">
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-bold text-[var(--color-text)]">تکرار رمز عبور جدید</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="رمز جدید را دوباره وارد کن">
        </div>

        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-5 text-sm font-black text-white transition hover:bg-[var(--color-primary-700)]">
            ذخیره رمز جدید و ادامه
            <span class="ms-2" aria-hidden="true">←</span>
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-bold text-[var(--color-text-secondary)] underline underline-offset-4">خروج از حساب</button>
    </form>
</div>
@endsection
