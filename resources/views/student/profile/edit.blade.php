@extends('layouts.student')

@section('title', 'تنظیمات حساب | شیخان')
@section('header-title', 'تنظیمات حساب')

@section('content')
@php
    $avatarUrl = $avatar ? $avatar->url() : asset('images/default-account-avatar.svg');
@endphp

<div class="student-workspace-page student-profile-page">
    <header class="student-workspace-head">
        <div class="student-workspace-head-copy">
            <span class="student-workspace-kicker">شخصی‌سازی حساب</span>
            <h1 class="student-workspace-title">تنظیمات پروفایل</h1>
            <p class="student-workspace-description">عکس و اطلاعاتت را مدیریت کن تا در فضای یادگیری شیخان راحت‌تر شناخته شوی.</p>
        </div>
        <span class="student-workspace-chip">حساب دانش‌آموز</span>
    </header>

    <section class="student-profile-hero student-panel" aria-labelledby="profile-title">
        <div class="student-profile-avatar-wrap">
            <img id="student-profile-avatar-preview" class="student-profile-avatar" src="{{ $avatarUrl }}" alt="تصویر پروفایل" width="112" height="112">
            <span class="student-profile-avatar-status" aria-hidden="true"></span>
        </div>
        <div class="student-profile-hero-copy">
            <span class="student-kicker">پروفایل من</span>
            <h2 id="profile-title">{{ $student->name }}</h2>
            <p>یک تصویر واضح انتخاب کن. این عکس کنار نامت در بخش‌های مجاز سامانه نمایش داده می‌شود.</p>
            <div class="student-profile-badges">
                <span class="student-status primary">{{ $student->studentProfile?->gradeRelation?->title ?: $student->studentProfile?->grade ?: 'پایه ثبت نشده' }}</span>
                <span class="student-status">شماره دانش‌آموزی: {{ $student->studentProfile?->student_number ?: 'ثبت نشده' }}</span>
            </div>
        </div>
    </section>

    <section class="student-panel dashboard-panel student-profile-panel" aria-labelledby="account-details-title">
        <div class="student-panel-head">
            <div><span class="student-kicker">اطلاعات پایه</span><h2 id="account-details-title">اطلاعات حساب</h2><p>نام، ایمیل و عکس نمایه‌ات را از این قسمت تغییر بده.</p></div>
        </div>

        <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" class="student-form student-profile-form">
            @csrf
            @method('PATCH')

            <div class="student-profile-avatar-field">
                <div class="student-profile-avatar-field-copy">
                    <strong>عکس پروفایل</strong>
                    <span>JPG، PNG، WEBP یا AVIF؛ حداکثر ۳ مگابایت.</span>
                </div>
                <label class="student-profile-upload">
                    <input id="student-profile-avatar-input" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif">
                    <span aria-hidden="true">＋</span>
                    انتخاب عکس
                </label>
            </div>
            @error('avatar')<p class="student-profile-field-error">{{ $message }}</p>@enderror

            <div class="student-profile-fields">
                <label for="profile-name"><span>نام و نام خانوادگی</span><input id="profile-name" class="student-input" type="text" name="name" value="{{ old('name', $student->name) }}" required maxlength="120" autocomplete="name"></label>
                <label for="profile-email"><span>ایمیل</span><input id="profile-email" class="student-input" type="email" name="email" value="{{ old('email', $student->email) }}" maxlength="255" autocomplete="email"></label>
            </div>

            <div class="student-profile-form-footer">
                <span>تغییرات پس از ذخیره در حساب کاربری‌ات اعمال می‌شود.</span>
                <button type="submit" class="student-welcome-action primary">ذخیره تغییرات <span aria-hidden="true">←</span></button>
            </div>
        </form>
    </section>

    <section class="student-panel dashboard-panel student-profile-panel" aria-labelledby="security-title">
        <div class="student-panel-head"><div><span class="student-kicker">امنیت حساب</span><h2 id="security-title">تغییر رمز عبور</h2><p>برای محافظت از حساب، رمز فعلی را هم تأیید کن.</p></div></div>
        <form method="POST" action="{{ route('student.profile.password.update') }}" class="student-form student-profile-fields">
            @csrf
            @method('PATCH')
            <label for="current-password"><span>رمز فعلی</span><input id="current-password" class="student-input" type="password" name="current_password" required autocomplete="current-password"></label>
            <label for="password"><span>رمز جدید</span><input id="password" class="student-input" type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <label for="password-confirmation"><span>تکرار رمز جدید</span><input id="password-confirmation" class="student-input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
            <div class="student-profile-form-footer"><span>حداقل ۸ نویسه انتخاب کن.</span><button type="submit" class="student-welcome-action primary">تغییر رمز <span aria-hidden="true">←</span></button></div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('student-profile-avatar-input');
    const preview = document.getElementById('student-profile-avatar-preview');
    if (!input || !preview) return;

    let objectUrl = null;
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
    });
    window.addEventListener('pagehide', function () {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
    }, { once: true });
});
</script>
@endpush
