@extends('layouts.student')

@section('title', 'حساب کاربری')
@section('header-title', 'حساب کاربری')

@section('content')
<section class="page" id="profile">
    <form class="card" method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="chead"><h2>اطلاعات حساب</h2></div>
        <div class="avu">
            <div class="avp" data-avatar-preview>
                @if($avatar && $avatar->url())
                    <img data-avatar-image src="{{ $avatar->url() }}" alt="تصویر پروفایل فعلی" width="100" height="100" loading="eager" decoding="async">
                    <svg data-avatar-fallback hidden viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
                @else
                    <svg data-avatar-fallback viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
                @endif
            </div>
            <div>
                <p>تصویر JPG، PNG، WebP یا AVIF؛ حداکثر ۳ مگابایت.</p>
                <input class="vh" id="av-file" data-avatar-input name="avatar" type="file" accept="image/jpeg,image/png,image/webp,image/avif">
                <label class="btn s" for="av-file">انتخاب تصویر</label>
                @error('avatar')<p class="tag no">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="pf" style="margin-top:20px">
            <div>
                <label for="profile-name">نام و نام خانوادگی</label>
                <input class="fld" id="profile-name" name="name" value="{{ old('name', $student->name) }}" maxlength="120" autocomplete="name" required>
                @error('name')<small class="tag no">{{ $message }}</small>@enderror
            </div>
            <div>
                <label for="profile-email">ایمیل</label>
                <input class="fld" id="profile-email" type="email" name="email" value="{{ old('email', $student->email) }}" maxlength="255" autocomplete="email">
                @error('email')<small class="tag no">{{ $message }}</small>@enderror
            </div>
            <div>
                <label for="profile-grade">پایه تحصیلی</label>
                <input class="fld" id="profile-grade" value="{{ $student->studentProfile?->gradeRelation?->title ?? $student->studentProfile?->grade ?? '—' }}" readonly>
            </div>
            <div>
                <label for="profile-number">شماره دانش‌آموزی</label>
                <input class="fld" id="profile-number" value="{{ $student->studentProfile?->student_number ?? '—' }}" readonly>
            </div>
        </div>
        <div class="frm"><button class="btn" type="submit">ذخیره اطلاعات</button></div>
    </form>

    @if(auth()->user()->hasPermission('profile.security'))
        <form class="card" method="POST" action="{{ route('student.profile.password.update') }}">
            @csrf
            @method('PATCH')
            <div class="chead"><h2>تغییر رمز عبور</h2></div>
            <div class="pf">
                <div>
                    <label for="current-password">رمز عبور فعلی</label>
                    <input class="fld" id="current-password" type="password" name="current_password" autocomplete="current-password" required>
                    @error('current_password')<small class="tag no">{{ $message }}</small>@enderror
                </div>
                <div>
                    <label for="new-password">رمز عبور جدید</label>
                    <input class="fld" id="new-password" type="password" name="password" minlength="8" autocomplete="new-password" required>
                    @error('password')<small class="tag no">{{ $message }}</small>@enderror
                </div>
                <div>
                    <label for="password-confirmation">تکرار رمز عبور جدید</label>
                    <input class="fld" id="password-confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                </div>
            </div>
            <p class="note" style="margin-top:12px">رمز عبور باید حداقل ۸ نویسه باشد.</p>
            <div class="frm"><button class="btn s" type="submit">تغییر رمز عبور</button></div>
        </form>
    @endif
</section>
@endsection
