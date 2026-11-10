@extends('layouts.owner')

@section('title','دوره‌های آموزشگاه | شیخان')
@section('header-title','دوره‌ها')

@section('content')
@php
    $statusTabs = [
        'all' => ['label' => 'همه', 'count' => $stats['total']],
        'published' => ['label' => 'منتشرشده', 'count' => $stats['published']],
        'draft' => ['label' => 'پیش‌نویس', 'count' => $stats['draft']],
        'archived' => ['label' => 'آرشیو', 'count' => $stats['archived']],
    ];
    $coursePriceLabel = static function ($price): string {
        $amount = (float) $price;
        if ($amount >= 1000000 && fmod($amount, 1000000) === 0.0) {
            return \App\Support\PersianUi::number($amount / 1000000) . ' میلیون تومان';
        }
        if ($amount >= 1000000) {
            return \App\Support\PersianUi::number($amount / 1000000, 1) . ' میلیون تومان';
        }
        return \App\Support\PersianUi::money($amount);
    };
@endphp
@include('owner.partials.learning-workspace-tabs', ['activeSheet' => 'courses', 'academy' => $workspaceAcademy ?? null])
<div class="course-page panel-page-enter space-y-4">
    <div class="course-page-heading">
        <div>
            <h1 class="text-2xl font-black tracking-tight">دوره‌ها</h1>
            <p class="mt-1 text-sm text-[var(--color-text-secondary)]">فهرست و وضعیت دوره‌های آموزشگاه</p>
        </div>
        <a href="{{ route('owner.courses.create') }}" class="course-primary-btn">+ ساخت دوره جدید</a>
    </div>

    <section class="course-list-panel" aria-label="فهرست دوره‌ها">
        <div class="course-list-toolbar">
            <h2>فهرست دوره‌ها <span>{{ \App\Support\PersianUi::number($stats['total']) }}</span></h2>
            <nav class="course-filter-tabs" aria-label="فیلتر وضعیت دوره‌ها">
                @foreach($statusTabs as $key => $tab)
                    <a href="{{ route('owner.courses.index', $key === 'all' ? [] : ['status' => $key]) }}"
                       @if($activeStatus === $key) aria-current="page" @endif>
                        {{ $tab['label'] }}
                        <span>{{ \App\Support\PersianUi::number($tab['count']) }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="course-table w-full text-right">
                <thead>
                    <tr>
                        <th>دوره</th>
                        <th>وضعیت</th>
                        <th>هزینه</th>
                        <th>دانش‌آموز فعال</th>
                        <th><span class="sr-only">عملیات</span></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($courses as $course)
                    <tr class="course-table-row" data-course-row="{{ route('owner.courses.show', $course) }}" tabindex="0" role="link" aria-label="مشاهده دوره {{ $course->title }}">
                        <td>
                            <a class="course-row-title" href="{{ route('owner.courses.show', $course) }}">{{ $course->title }}</a>
                            <span class="course-row-meta">{{ \App\Support\PersianUi::number($course->sections_count) }} بخش</span>
                        </td>
                        <td>
                            <span class="course-status-badge {{ $course->status }}">
                                <i aria-hidden="true"></i>
                                {{ match($course->status){'published'=>'منتشرشده','archived'=>'آرشیو',default=>'پیش‌نویس'} }}
                            </span>
                        </td>
                        <td>
                            @if($course->isFree())
                                <span class="course-access-badge free">رایگان</span>
                            @else
                                <span class="course-price">{{ $coursePriceLabel($course->price) }}</span>
                            @endif
                        </td>
                        <td><span class="course-student-count">{{ \App\Support\PersianUi::number($course->active_students_count) }}</span></td>
                        <td>
                            <div class="course-row-actions">
                                <a href="{{ route('owner.courses.edit', $course) }}" class="course-action-btn">ویرایش</a>
                                <details class="course-more-menu">
                                    <summary aria-label="عملیات بیشتر برای {{ $course->title }}" title="عملیات بیشتر">⋯</summary>
                                    @if($course->status === 'archived')
                                        <form method="POST" action="{{ route('owner.courses.restore', $course) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" data-confirm="دوره به پیش‌نویس برگردد؟">بازیابی دوره</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('owner.courses.archive', $course) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="is-danger" data-confirm="دوره از دسترس دانش‌آموزان خارج و آرشیو شود؟ سوابق حفظ می‌شوند.">آرشیو دوره</button>
                                        </form>
                                    @endif
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="course-empty-state">
                                <div class="text-sm font-black">{{ $activeStatus === 'all' ? 'هنوز دوره‌ای ساخته نشده است' : 'دوره‌ای در این وضعیت وجود ندارد' }}</div>
                                @if($activeStatus === 'all')
                                    <a href="{{ route('owner.courses.create') }}" class="course-primary-btn mt-3">ساخت اولین دوره</a>
                                @else
                                    <a href="{{ route('owner.courses.index') }}" class="course-empty-link">نمایش همه دوره‌ها</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="course-mobile-list md:hidden">
            @forelse($courses as $course)
                <article class="course-mobile-row">
                    <a href="{{ route('owner.courses.show', $course) }}" class="course-row-title">{{ $course->title }}</a>
                    <div class="course-mobile-meta">
                        <span class="course-status-badge {{ $course->status }}"><i aria-hidden="true"></i>{{ match($course->status){'published'=>'منتشرشده','archived'=>'آرشیو',default=>'پیش‌نویس'} }}</span>
                        <span>{{ $course->isFree() ? 'رایگان' : $coursePriceLabel($course->price) }}</span>
                        <span>{{ \App\Support\PersianUi::number($course->active_students_count) }} دانش‌آموز</span>
                    </div>
                    <div class="course-row-actions">
                        <a href="{{ route('owner.courses.edit', $course) }}" class="course-action-btn">ویرایش</a>
                        <details class="course-more-menu">
                            <summary aria-label="عملیات بیشتر برای {{ $course->title }}">⋯</summary>
                            @if($course->status === 'archived')
                                <form method="POST" action="{{ route('owner.courses.restore', $course) }}">@csrf @method('PATCH')<button type="submit" data-confirm="دوره به پیش‌نویس برگردد؟">بازیابی دوره</button></form>
                            @else
                                <form method="POST" action="{{ route('owner.courses.archive', $course) }}">@csrf @method('PATCH')<button type="submit" class="is-danger" data-confirm="دوره از دسترس دانش‌آموزان خارج و آرشیو شود؟ سوابق حفظ می‌شوند.">آرشیو دوره</button></form>
                            @endif
                        </details>
                    </div>
                </article>
            @empty
                <div class="course-empty-state">دوره‌ای در این وضعیت وجود ندارد.</div>
            @endforelse
        </div>

        @if($courses->hasPages())
            <div class="border-t border-[var(--color-border)] p-4">{{ $courses->links() }}</div>
        @endif
    </section>
</div>
@endsection
