@extends('layouts.owner')

@section('title',($course->exists?'ویرایش دوره':'ساخت دوره').' | شیخان')
@section('header-title',$course->exists?'ویرایش دوره':'ساخت دوره')

@section('content')
<div class="course-page panel-page-enter space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--color-brand-600)]">{{ $course->exists ? 'مدیریت دوره' : 'راه‌اندازی دوره' }}</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight">{{ $course->exists?'ویرایش دوره':'ساخت دوره جدید' }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-[var(--color-text-secondary)]">
                {{ $course->exists ? 'اطلاعات دوره را اصلاح کن؛ تغییرات بعد از ذخیره روی نسخه مدیریتی اعمال می‌شوند.' : 'مشخصات پایه را وارد کن؛ قیمت‌گذاری و انتشار را هم همین‌جا مشخص کن.' }}
            </p>
        </div>
        <a href="{{ route('owner.courses.index') }}" class="course-secondary-btn">بازگشت به دوره‌ها</a>
    </div>

    @if($course->exists)
        <div class="course-info-strip">
            <span class="course-info-strip-dot"></span>
            <div>
                <strong>در حال ویرایش «{{ $course->title }}»</strong>
                <p>آموزشگاه دوره بعد از ساخت قابل جابه‌جایی نیست؛ برای همین در این فرم فقط وضعیت و محتوای خود دوره را تغییر می‌دهیم.</p>
            </div>
        </div>
    @endif

    <x-education.course-management-form
        :course="$course"
        :academies="$academies"
        :action="$course->exists ? route('owner.courses.update',$course) : route('owner.courses.store')"
        :method="$course->exists ? 'PATCH' : 'POST'"
        route-prefix="owner.courses"
    />
</div>
@endsection