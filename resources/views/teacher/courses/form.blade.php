@extends('layouts.teacher')

@section('title', ($course->exists ? 'ویرایش دوره' : 'ساخت دوره') . ' | شیخان')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold text-[var(--color-brand-600)]">مدیریت دوره</p>
                <h1 class="mt-2 text-2xl font-black text-[var(--color-text)]">
                    {{ $course->exists ? 'ویرایش دوره' : 'ساخت دوره جدید' }}
                </h1>
                <p class="mt-2 text-sm text-[var(--color-text-secondary)]">
                    محتوا، دسترسی و قیمت دوره را بدون دست‌کاری منطق backend مدیریت کن.
                </p>
            </div>

            <a href="{{ route('teacher.courses.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm font-bold text-[var(--color-text-secondary)]">
                دوره‌های من
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] px-4 py-3 text-sm font-semibold text-[var(--color-success-700)]">
                {{ session('success') }}
            </div>
        @endif

        <x-education.course-management-form
            :course="$course"
            :academies="$academies"
            :action="$course->exists ? route('teacher.courses.update', $course) : route('teacher.courses.store')"
            :method="$course->exists ? 'PATCH' : 'POST'"
            route-prefix="teacher.courses"
        />
    </div>
@endsection
