@extends('layouts.student')

@section('title', 'منابع آموزشی | شیخان')
@section('header-title', 'منابع آموزشی')

@section('content')
    <section class="student-panel dashboard-panel" aria-labelledby="student-resources-title">
        <div class="student-panel-head">
            <div>
                <span class="student-kicker" style="color: var(--panel-primary)">فضای اختصاصی</span>
                <h1 id="student-resources-title">منابع آموزشی من</h1>
                <p>جزوه‌ها، فایل‌ها و محتوایی که برای دوره‌ها و کلاس‌های خودت منتشر شده‌اند.</p>
            </div>
        </div>

        <div class="student-resource-list">
            @forelse($resources as $resource)
                <article class="student-resource">
                    <div>
                        <strong>{{ $resource->title }}</strong>
                        <span>{{ $resource->course?->title ?? $resource->classroom?->title ?? $resource->lesson?->title ?? 'منبع آموزشی' }}</span>
                        @if($resource->description)
                            <p>{{ $resource->description }}</p>
                        @endif
                    </div>

                    <div class="student-resource-actions">
                        <a class="student-action" href="{{ route('student.resources.view', $resource) }}">
                            مشاهده
                        </a>

                        @if($resource->downloadable)
                            <a class="student-action" href="{{ route('student.resources.download', $resource) }}">
                                دانلود
                            </a>
                        @else
                            <span class="student-status">فقط مشاهده</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="student-empty">
                    <strong>هنوز منبع آموزشی منتشر نشده است.</strong>
                    <span>با انتشار جزوه یا فایل برای دوره یا کلاس شما، اینجا نمایش داده می‌شود.</span>
                </div>
            @endforelse
        </div>

        @if($resources->hasPages())
            <nav class="student-pagination" aria-label="صفحه‌بندی منابع آموزشی">
                {{ $resources->links() }}
            </nav>
        @endif
    </section>
@endsection
