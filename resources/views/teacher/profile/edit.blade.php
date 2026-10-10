@extends('layouts.teacher')

@section('title','پروفایل استاد | شیخان')
@section('header-title','پروفایل استاد')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">حساب و معرفی</span>
            <h1 class="teacher-workspace-title">پروفایل استاد</h1>
            <p class="teacher-workspace-description">اطلاعاتی که دانش‌آموز قبل از ورود به دوره می‌بیند را کامل کن و آواتار حرفه‌ای خودت را تنظیم کن.</p>
        </div>
        @if($profile->is_public)
            <a href="{{ route('teachers.show',$teacher) }}" class="teacher-workspace-btn secondary">مشاهده پروفایل عمومی</a>
        @else
            <span class="teacher-workspace-btn secondary" aria-disabled="true">پروفایل عمومی خاموش است</span>
        @endif
    </header>

    @if(session('success'))<div class="teacher-workspace-alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="teacher-workspace-alert error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('teacher.profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PATCH')

        <section class="teacher-profile-editor">
            <div class="teacher-profile-editor-hero">
                <div class="teacher-profile-avatar">
                    @if($avatar)
                        <img src="{{ $avatar->visibility === 'public' ? $avatar->url() : route('media.view',$avatar) }}" alt="{{ $teacher->name }}">
                    @else
                        <span>{{ mb_substr(trim($teacher->name),0,1) }}</span>
                    @endif
                </div>
                <div class="teacher-profile-editor-copy">
                    <span class="teacher-workspace-kicker">تصویر پروفایل</span>
                    <h2>{{ $teacher->name }}</h2>
                    <p>یک تصویر واضح و حرفه‌ای انتخاب کن؛ این تصویر در صفحه معرفی عمومی مدرس نمایش داده می‌شود.</p>
                    <label class="teacher-upload-btn">
                        انتخاب تصویر
                        <input
                            type="file"
                            name="avatar"
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            aria-describedby="teacher-avatar-help"
                        >
                    </label>
                    <small id="teacher-avatar-help">
                        JPG، PNG، WEBP یا AVIF · حداکثر ۳ مگابایت.
                        این تصویر به‌عنوان آواتار شما در فهرست مدرس‌ها و صفحه معرفی عمومی نمایش داده می‌شود.
                    </small>
                </div>
            </div>

            <section class="teacher-cover-editor" aria-labelledby="teacher-cover-title">
                <div class="teacher-cover-preview {{ $cover ? 'has-cover' : 'no-cover' }}">
                    @if($cover)
                        <img
                            src="{{ $cover->visibility === 'public' ? $cover->url() : route('media.view', $cover) }}"
                            alt=""
                            loading="lazy"
                        >
                        <span class="teacher-cover-preview-label">کاور فعلی</span>
                    @else
                        <div class="teacher-cover-placeholder">
                            <span>معرفی مدرس</span>
                            <strong>{{ $teacher->name }}</strong>
                            <small>کاور اختیاری است؛ اگر انتخاب نکنی، صفحه بدون تصویر اجباری نمایش داده می‌شود.</small>
                            <i aria-hidden="true">ش</i>
                        </div>
                    @endif
                </div>
                <div class="teacher-cover-editor-copy">
                    <span class="teacher-workspace-kicker">اختیاری · تصویر عریض</span>
                    <h2 id="teacher-cover-title">کاور صفحه معرفی</h2>
                    <p>یک عکس عریض مرتبط با فضای تدریس یا تخصصت انتخاب کن. کاور فقط در صفحه عمومی مدرس نمایش داده می‌شود؛ آواتار همچنان جداست.</p>
                    <label class="teacher-upload-btn">
                        انتخاب کاور
                        <input
                            type="file"
                            name="cover"
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            aria-describedby="teacher-cover-help"
                        >
                    </label>
                    <small id="teacher-cover-help">JPG، PNG، WEBP یا AVIF · حداکثر ۵ مگابایت. انتخاب کاور اجباری نیست.</small>
                </div>
            </section>

            <div class="teacher-profile-form-grid">
                <label class="teacher-workspace-field"><span>نام و نام خانوادگی</span><input name="name" value="{{ old('name',$teacher->name) }}" required maxlength="120"></label>
                <label class="teacher-workspace-field"><span>ایمیل</span><input type="email" name="email" value="{{ old('email',$teacher->email) }}" maxlength="255" dir="ltr"></label>
                <label class="teacher-workspace-field"><span>تخصص / حوزه تدریس</span><input name="specialization" value="{{ old('specialization',$profile->specialization) }}" maxlength="255" placeholder="مثلاً ریاضی و فیزیک"></label>
                <label class="teacher-workspace-field"><span>سابقه تدریس <small>سال</small></span><input type="number" name="experience_years" min="0" max="80" value="{{ old('experience_years',$profile->experience_years) }}"></label>
                <label class="teacher-workspace-field full"><span>تحصیلات</span><input name="education" value="{{ old('education',$profile->education) }}" maxlength="1000" placeholder="مثلاً کارشناسی ارشد مهندسی..."></label>
                <label class="teacher-workspace-field full"><span>معرفی کوتاه</span><textarea name="bio" rows="6" maxlength="4000" placeholder="تجربه، روش تدریس و زمینه تخصصی خودت را برای دانش‌آموز توضیح بده...">{{ old('bio',$profile->bio) }}</textarea></label>
            </div>

            <div class="teacher-profile-visibility">
                <div>
                    <strong>پروفایل عمومی</strong>
                    <span>اگر فعال باشد، نام، آواتار، تخصص، معرفی و دوره‌های منتشرشده شما در بخش مدرس‌ها دیده می‌شود.</span>
                </div>
                <label class="teacher-toggle">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public',$profile->is_public ?? true))>
                    <span></span>
                    <b>نمایش در سایت</b>
                </label>
            </div>

            <div class="teacher-workspace-actions" style="justify-content:flex-end">
                <button type="submit" class="teacher-workspace-btn primary">ذخیره پروفایل</button>
            </div>
        </section>
    </form>
</div>
@endsection
