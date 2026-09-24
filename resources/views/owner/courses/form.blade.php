@extends('layouts.owner')
@section('title',($course->exists?'ویرایش دوره':'ساخت دوره').' | شیخان')
@section('header-title',$course->exists?'ویرایش دوره':'ساخت دوره')
@section('content')
<div class="space-y-6"><div><p class="text-xs font-bold text-[var(--color-brand-600)]">مدیریت دوره</p><h1 class="mt-2 text-2xl font-black">{{ $course->exists?'ویرایش دوره':'ساخت دوره جدید' }}</h1><p class="mt-2 text-sm text-[var(--color-text-secondary)]">مشخصات، قیمت، انتشار و فایل‌های خصوصی.</p></div>
<x-education.course-management-form :course="$course" :academies="$academies" :action="$course->exists ? route('owner.courses.update',$course) : route('owner.courses.store')" :method="$course->exists ? 'PATCH' : 'POST'" route-prefix="owner.courses" />
</div>
@endsection