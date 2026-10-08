@extends('layouts.student')

@section('title','حساب کاربری | شیخان')
@section('header-title','حساب کاربری')

@section('content')
<div class="student-workspace-page"><header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">حساب کاربری</span><h1 class="student-workspace-title">حساب من</h1><p class="student-workspace-description">اطلاعات پایه و امنیت حساب دانش‌آموز از همین‌جا مدیریت می‌شود.</p></div></header>
<section class="student-panel dashboard-panel" aria-labelledby="profile-title">
    <div class="student-panel-head"><div><span class="student-kicker" style="color:var(--panel-primary)">حساب من</span><h2 id="profile-title">اطلاعات حساب</h2><p>اطلاعات پایه‌ای که دانش‌آموز می‌تواند مدیریت کند.</p></div><span class="student-status primary">امن</span></div>
    <form method="POST" action="{{ route('student.profile.update') }}" class="student-form">
        @csrf @method('PATCH')
        <label for="profile-name">نام و نام خانوادگی</label>
        <input id="profile-name" class="student-input" type="text" name="name" value="{{ old('name',$student->name) }}" required maxlength="120">
        <label for="profile-email">ایمیل</label>
        <input id="profile-email" class="student-input" type="email" name="email" value="{{ old('email',$student->email) }}" maxlength="255">
        <div class="student-profile-facts">
            <span class="student-status">شماره دانش‌آموزی: {{ $student->studentProfile?->student_number ?: 'ثبت نشده' }}</span>
            <span class="student-status">{{ $student->studentProfile?->gradeRelation?->title ?: $student->studentProfile?->grade ?: 'پایه ثبت نشده' }}</span>
        </div>
        <button type="submit" class="student-welcome-action primary">ذخیره تغییرات</button>
    </form>
</section>

<section class="student-panel dashboard-panel" aria-labelledby="security-title">
    <div class="student-panel-head"><div><span class="student-kicker" style="color:var(--panel-primary)">امنیت</span><h2 id="security-title">تغییر رمز عبور</h2><p>برای تغییر رمز، رمز فعلی نیز باید تأیید شود.</p></div></div>
    <form method="POST" action="{{ route('student.profile.password.update') }}" class="student-form">
        @csrf @method('PATCH')
        <label for="current-password">رمز فعلی</label>
        <input id="current-password" class="student-input" type="password" name="current_password" required autocomplete="current-password">
        <label for="password">رمز جدید</label>
        <input id="password" class="student-input" type="password" name="password" required minlength="8" autocomplete="new-password">
        <label for="password-confirmation">تکرار رمز جدید</label>
        <input id="password-confirmation" class="student-input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
        <button type="submit" class="student-welcome-action primary">تغییر رمز</button>
    </form>
</section>
</div>
@endsection
