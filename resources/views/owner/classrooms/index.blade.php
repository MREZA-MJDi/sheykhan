@extends('layouts.owner')

@section('title','کلاس‌های آموزشگاه | شیخان')
@section('header-title','کلاس‌های آموزشگاه')

@section('content')
<div class="owner-page">
    @if(session('success'))
        <div class="owner-alert owner-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="owner-alert owner-alert-danger">
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="owner-page-head">
        <div>
            <span class="owner-eyebrow">نظارت آموزشی</span>
            <h1>{{ $academy->name }}</h1>
            <p>کلاس‌ها، مدرس‌ها، ظرفیت و زمان‌بندی را از یک نقطه مدیریت کن.</p>
        </div>
        <a href="{{ route('owner.classrooms.create', $academy) }}" class="owner-page-action owner-page-action-primary">+ کلاس جدید</a>
    </div>

    <section class="owner-mini-stats">
        <article><span>کل کلاس‌ها</span><strong>{{ $classrooms->count() }}</strong></article>
        <article><span>فعال</span><strong>{{ $classrooms->where('status','active')->count() }}</strong></article>
        <article><span>آرشیو</span><strong>{{ $classrooms->where('status','archived')->count() }}</strong></article>
        <article><span>دانش‌آموز ثبت‌شده</span><strong>{{ $classrooms->sum('students_count') }}</strong></article>
    </section>

    <div class="owner-classroom-grid">
        @forelse($classrooms as $classroom)
            <article class="dashboard-panel owner-classroom-card">
                <div class="owner-card-head">
                    <div>
                        <span class="owner-eyebrow">{{ $classroom->code }}</span>
                        <h2>{{ $classroom->title }}</h2>
                        <p>{{ $classroom->course?->title }}</p>
                    </div>
                    <span class="owner-status {{ $classroom->status === 'active' ? 'is-success' : 'is-muted' }}">{{ $classroom->status === 'active' ? 'فعال' : 'آرشیو' }}</span>
                </div>

                <div class="owner-classroom-metrics">
                    <div><span>مدرس</span><strong>{{ $classroom->teachers->count() }}</strong><small>{{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}</small></div>
                    <div><span>دانش‌آموز</span><strong>{{ $classroom->students_count }}</strong><small>{{ $classroom->capacity ? 'ظرفیت ' . $classroom->capacity : 'بدون ظرفیت مشخص' }}</small></div>
                </div>

                <div class="owner-classroom-schedule">
                    <span>برنامه</span>
                    <strong>
                        @if($classroom->starts_at)
                            {{ $classroom->starts_at->format('Y/m/d H:i') }}
                        @else
                            بدون زمان شروع
                        @endif
                    </strong>
                </div>

                <div class="owner-classroom-actions">
                    <a href="{{ route('owner.classrooms.edit', [$academy, $classroom]) }}" class="owner-page-action">ویرایش</a>
                    <form method="POST" action="{{ route('owner.classrooms.status', [$academy, $classroom]) }}" data-confirm="{{ $classroom->status === 'active' ? 'این کلاس آرشیو شود؟' : 'این کلاس دوباره فعال شود؟' }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $classroom->status === 'active' ? 'archived' : 'active' }}">
                        <button class="owner-remove-btn" type="submit">{{ $classroom->status === 'active' ? 'آرشیو' : 'فعال‌سازی' }}</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="dashboard-panel owner-empty-card">کلاسی در این آموزشگاه ثبت نشده است.</div>
        @endforelse
    </div>
</div>
@endsection
