@extends('layouts.teacher')
@section('title', 'تنظیمات پروفایل')
@section('heading', 'تنظیمات پروفایل')
@section('content')
    <x-teacher.section-head title="حساب و معرفی مدرس" subtitle="فیلدها دقیقاً به UpdateProfileRequest و TeacherProfile فعلی متصل هستند." />
    <form method="POST" action="{{ route('teacher.profile.update') }}" enctype="multipart/form-data" class="tp-stack-form">
        @csrf @method('PATCH')
        <div class="tp-grid tp-grid--main tp-grid--start">
            <x-teacher.card title="تصویر و کاور">
                <div class="tp-hero" style="margin-bottom:14px">
                    <div class="tp-hero__cover" data-tp-preview-bg="cover" @if($cover) style="background-image:url('{{ $cover->visibility === 'public' ? $cover->url() : route('media.view', $cover) }}')" @endif></div>
                    <div class="tp-hero__body">
                        <div class="tp-hero__photo" data-tp-preview-bg="avatar" @if($avatar) style="background-image:url('{{ $avatar->visibility === 'public' ? $avatar->url() : route('media.view', $avatar) }}')" @endif>
                            <span data-tp-photo-initials @if($avatar) hidden @endif>{{ mb_substr(trim($teacher->name), 0, 1) }}</span>
                        </div>
                        <div><b class="tp-hero__name">{{ $teacher->name }}</b><small>{{ $profile->specialization ?: 'مدرس' }}</small></div>
                    </div>
                </div>
                <div class="tp-stack-form">
                    <x-teacher.field label="تصویر پروفایل"><input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/avif" data-tp-image="avatar"><small>JPG، PNG، WEBP یا AVIF · حداکثر ۳ مگابایت.</small></x-teacher.field>
                    <input type="hidden" name="remove_avatar" value="0" data-tp-remove-flag="avatar">
                    @if ($avatar)<button class="tp-btn tp-btn--light" type="button" data-tp-image-remove="avatar">حذف تصویر فعلی</button>@endif
                    <x-teacher.field label="تصویر کاور"><input type="file" name="cover" accept="image/jpeg,image/png,image/webp,image/avif" data-tp-image="cover"><small>حداکثر ۵ مگابایت.</small></x-teacher.field>
                    <input type="hidden" name="remove_cover" value="0" data-tp-remove-flag="cover">
                    @if ($cover)<button class="tp-btn tp-btn--light" type="button" data-tp-image-remove="cover">حذف کاور فعلی</button>@endif
                </div>
            </x-teacher.card>

            <x-teacher.card title="نمایش و اعلان‌ها">
                <x-teacher.switch name="is_public" title="پروفایل عمومی" desc="نمایش معرفی مدرس در وب‌سایت عمومی" :on="old('is_public', $profile->is_public)" />
                @php($preferences = $profile->notification_preferences ?? [])
                <x-teacher.switch name="notification_preferences[parent_messages]" title="پیام جدید اولیا" desc="اعلان هنگام دریافت پیام والد" :on="old('notification_preferences.parent_messages', $preferences['parent_messages'] ?? true)" />
                <x-teacher.switch name="notification_preferences[class_reminders]" title="یادآوری کلاس‌ها" desc="یادآوری برنامه جلسات" :on="old('notification_preferences.class_reminders', $preferences['class_reminders'] ?? true)" />
                <x-teacher.switch name="notification_preferences[assignment_submissions]" title="تحویل تکلیف جدید" desc="اعلان هنگام ارسال تکلیف دانش‌آموز" :on="old('notification_preferences.assignment_submissions', $preferences['assignment_submissions'] ?? false)" />
                @if ($profile->is_public)<a class="tp-link" href="{{ route('teachers.show', $teacher) }}" target="_blank" rel="noopener noreferrer">مشاهده پروفایل عمومی</a>@endif
            </x-teacher.card>
        </div>

        <x-teacher.card title="اطلاعات شخصی و تخصصی">
            <div class="tp-form-grid">
                <x-teacher.field label="نام و نام خانوادگی"><input name="name" value="{{ old('name', $teacher->name) }}" required maxlength="120"></x-teacher.field>
                <x-teacher.field label="ایمیل"><input type="email" name="email" dir="ltr" value="{{ old('email', $teacher->email) }}" maxlength="255"></x-teacher.field>
                <x-teacher.field label="تخصص"><input name="specialization" value="{{ old('specialization', $profile->specialization) }}" maxlength="255"></x-teacher.field>
                <x-teacher.field label="سابقه (سال)"><input type="number" name="experience_years" value="{{ old('experience_years', $profile->experience_years) }}" min="0" max="80"></x-teacher.field>
                <x-teacher.field label="تحصیلات"><input name="education" value="{{ old('education', $profile->education) }}" maxlength="1000"></x-teacher.field>
                <x-teacher.field label="معرفی کوتاه"><textarea name="bio" rows="5" maxlength="4000">{{ old('bio', $profile->bio) }}</textarea></x-teacher.field>
            </div>
            <div class="tp-form-actions"><button class="tp-btn" type="submit">ذخیره تغییرات</button></div>
        </x-teacher.card>
    </form>
@endsection