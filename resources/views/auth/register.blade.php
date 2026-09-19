@extends('layouts.auth')

@section('title', 'ثبت‌نام | شیخان')

@section('content')
<div class="rounded-[2rem] border border-[var(--color-border)] bg-white p-6 shadow-[var(--shadow-xl)] sm:p-8">
    <div class="mb-8">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-lg font-black text-[var(--color-primary-700)]">ش</div>
        <h1 class="mt-6 text-2xl font-black text-[var(--color-text)] sm:text-3xl">حساب شیخانت را بساز.</h1>
        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
            نوع حساب را انتخاب کن؛ بعد از ثبت‌نام مستقیم وارد فضای همان نقش می‌شوی.
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

    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="mb-2 block text-sm font-bold text-[var(--color-text)]">نام و نام خانوادگی</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="نام شما">
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-bold text-[var(--color-text)]">ایمیل</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="you@example.com">
        </div>

        <div>
            <label for="account_type" class="mb-2 block text-sm font-bold text-[var(--color-text)]">نوع حساب</label>
            <select id="account_type" name="account_type" required
                    class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]">
                <option value="student" @selected(old('account_type', 'student') === 'student')>دانش‌آموز</option>
                <option value="parent" @selected(old('account_type') === 'parent')>والد</option>
            </select>
            <p class="mt-2 text-xs leading-6 text-[var(--color-text-muted)]">
                حساب مدرس و مدیر آموزشگاه از مسیر مدیریت/دعوت ساخته می‌شود.
            </p>
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-bold text-[var(--color-text)]">رمز عبور</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="حداقل ۸ کاراکتر">
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-bold text-[var(--color-text)]">تکرار رمز عبور</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                   class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm text-[var(--color-text)] outline-none transition placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary-400)] focus:ring-4 focus:ring-[var(--color-primary-100)]"
                   placeholder="رمز را دوباره وارد کن">
        </div>

        <button type="submit"
                class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-5 text-sm font-black text-white shadow-[0_10px_24px_rgba(83,98,223,.18)] transition hover:-translate-y-0.5 hover:bg-[var(--color-primary-700)] hover:shadow-[0_14px_30px_rgba(83,98,223,.22)]">
            ساخت حساب <span class="ms-2" aria-hidden="true">←</span>
        </button>
    </form>

    <div class="mt-6 border-t border-[var(--color-border)] pt-6 text-center text-sm text-[var(--color-text-secondary)]">
        حساب داری؟
        <a href="{{ route('login') }}" class="font-black text-[var(--color-primary-600)] hover:text-[var(--color-primary-700)]">وارد شو</a>
    </div>
</div>
@endsection
