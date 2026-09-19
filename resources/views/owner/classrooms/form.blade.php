@extends('layouts.owner')

@section('title', ($classroom->exists ? 'ویرایش کلاس' : 'ساخت کلاس') . ' | شیخان')
@section('header-title', $classroom->exists ? 'ویرایش کلاس' : 'ساخت کلاس')

@section('content')
<div class="owner-page">
    @if($errors->any())
        <div class="owner-alert owner-alert-danger">
            <strong>اطلاعات کلاس کامل نیست.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="owner-page-head">
        <div>
            <span class="owner-eyebrow">کلاس آموزشگاه</span>
            <h1>{{ $classroom->exists ? 'ویرایش کلاس' : 'ساخت کلاس جدید' }}</h1>
            <p>{{ $academy->name }} · اطلاعات کلاس، ظرفیت و مدرس‌های مسئول را مدیریت کن.</p>
        </div>
        <a href="{{ route('owner.classrooms.index', $academy) }}" class="owner-page-action">فهرست کلاس‌ها</a>
    </div>

    <section class="dashboard-panel owner-admin-card">
        <form method="POST" action="{{ $classroom->exists ? route('owner.classrooms.update', [$academy, $classroom]) : route('owner.classrooms.store', $academy) }}" class="owner-form-grid owner-form-grid-2">
            @csrf
            @if($classroom->exists)
                @method('PATCH')
            @endif

            <label>
                <span>دوره</span>
                <select name="course_id" required>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((int) old('course_id', $classroom->course_id) === $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </label>

            <label><span>عنوان کلاس</span><input name="title" required value="{{ old('title', $classroom->title) }}"></label>
            <label><span>کد کلاس</span><input name="code" required value="{{ old('code', $classroom->code) }}" dir="ltr"></label>
            <label><span>ظرفیت</span><input name="capacity" type="number" min="1" value="{{ old('capacity', $classroom->capacity) }}"></label>
            <label><span>شروع</span><input name="starts_at" type="datetime-local" value="{{ old('starts_at', $classroom->starts_at?->format('Y-m-d\TH:i')) }}"></label>
            <label><span>پایان</span><input name="ends_at" type="datetime-local" value="{{ old('ends_at', $classroom->ends_at?->format('Y-m-d\TH:i')) }}"></label>

            <label class="owner-form-span-2">
                <span>توضیحات</span>
                <textarea name="description" rows="5">{{ old('description', $classroom->description) }}</textarea>
            </label>

            <div class="owner-form-span-2">
                <span class="owner-field-title">مدرس‌های کلاس</span>
                <div class="owner-teacher-check-grid">
                    @php($selectedTeacherIds = collect(old('teacher_ids', $classroom->exists ? $classroom->teachers->pluck('id')->all() : []))->map(fn ($id) => (int) $id))
                    @forelse($teachers as $teacher)
                        <label class="owner-teacher-check">
                            <input type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}" @checked($selectedTeacherIds->contains($teacher->id))>
                            <span><strong>{{ $teacher->name }}</strong><small>{{ $teacher->email }}</small></span>
                        </label>
                    @empty
                        <div class="owner-empty">مدرس فعالی برای این آموزشگاه وجود ندارد.</div>
                    @endforelse
                </div>
            </div>

            <label>
                <span>وضعیت</span>
                <select name="status">
                    <option value="active" @selected(old('status', $classroom->status) === 'active')>فعال</option>
                    <option value="archived" @selected(old('status', $classroom->status) === 'archived')>آرشیو</option>
                </select>
            </label>

            <div class="owner-form-actions">
                <a href="{{ route('owner.classrooms.index', $academy) }}" class="owner-page-action">انصراف</a>
                <button class="owner-submit">{{ $classroom->exists ? 'ذخیره تغییرات' : 'ساخت کلاس' }}</button>
            </div>
        </form>
    </section>
</div>
@endsection
