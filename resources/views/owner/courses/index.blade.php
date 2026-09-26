@extends('layouts.owner')

@section('title','دوره‌های آموزشگاه | شیخان')
@section('header-title','دوره‌ها')

@section('content')
<div class="course-page panel-page-enter space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--color-brand-600)]">مدیریت آموزش</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight">دوره‌ها</h1>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-[var(--color-text-secondary)]">
                دوره‌ها را بساز، قیمت و وضعیت انتشارشان را مدیریت کن و قبل از انتشار یک‌جا بازبینی‌شان کن.
            </p>
        </div>
        <a href="{{ route('owner.courses.create') }}" class="course-primary-btn">+ ساخت دوره جدید</a>
    </div>

    <section class="grid gap-3 sm:grid-cols-3" aria-label="آمار دوره‌ها">
        @foreach([
            ['کل دوره‌ها', $stats['total'], 'همه دوره‌های ثبت‌شده', 'all'],
            ['منتشرشده', $stats['published'], 'آماده ارائه', 'published'],
            ['رایگان', $stats['free'], 'بدون نیاز به پرداخت', 'free'],
        ] as [$label,$value,$hint,$tone])
            <article class="course-stat {{ $tone }}">
                <div class="flex items-start justify-between gap-3">
                    <span>{{ $label }}</span>
                    <i aria-hidden="true"></i>
                </div>
                <strong>{{ number_format($value) }}</strong>
                <small>{{ $hint }}</small>
            </article>
        @endforeach
    </section>

    <section class="course-status-guide">
        <div>
            <span class="course-guide-kicker">راهنمای وضعیت</span>
            <strong>رنگ‌ها را ساده نگه داشتیم؛ هر رنگ یک معنی دارد.</strong>
        </div>
        <div class="course-guide-items">
            <span><i class="published"></i>منتشرشده</span>
            <span><i class="draft"></i>پیش‌نویس</span>
            <span><i class="archived"></i>آرشیو</span>
            <span><i class="paid"></i>پولی</span>
        </div>
    </section>

    <section class="panel-card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-[var(--color-border)] bg-[var(--color-background-soft)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-black">فهرست دوره‌ها</h2>
                <p class="mt-1 text-xs text-[var(--color-text-muted)]">برای دیدن جزئیات «بازبینی» و برای تغییرات «ویرایش» را بزن.</p>
            </div>
            @if($courses->total())
                <span class="course-count-badge">{{ number_format($courses->total()) }} دوره</span>
            @endif
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-[860px] text-right">
                <thead class="border-b border-[var(--color-border)] bg-white">
                    <tr>
                        <th class="px-5 py-4 text-[11px] font-black text-[var(--color-text-muted)]">دوره</th>
                        <th class="px-5 py-4 text-[11px] font-black text-[var(--color-text-muted)]">وضعیت</th>
                        <th class="px-5 py-4 text-[11px] font-black text-[var(--color-text-muted)]">دسترسی</th>
                        <th class="px-5 py-4 text-[11px] font-black text-[var(--color-text-muted)]">دانش‌آموز فعال</th>
                        <th class="px-5 py-4 text-[11px] font-black text-[var(--color-text-muted)]">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)]">
                @forelse($courses as $course)
                    <tr class="course-table-row">
                        <td class="px-5 py-4">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-black">{{ $course->title }}</div>
                                <div class="mt-1 text-[11px] text-[var(--color-text-muted)]">
                                    {{ $course->sections_count }} بخش
                                    <span class="mx-1">·</span>
                                    {{ $course->academy?->name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="course-status-badge {{ $course->status }}">
                                <i aria-hidden="true"></i>
                                {{ match($course->status){'published'=>'منتشرشده','archived'=>'آرشیو',default=>'پیش‌نویس'} }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($course->isFree())
                                <span class="course-access-badge free">رایگان</span>
                            @else
                                <div class="space-y-1">
                                    <span class="course-access-badge paid">پولی</span>
                                    <div class="text-xs font-black">{{ number_format((float)$course->price,0,'.',',') }} تومان</div>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm font-black">{{ number_format($course->active_students_count) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('owner.courses.show',$course) }}" class="course-action-btn primary">بازبینی</a>
                                <a href="{{ route('owner.courses.edit',$course) }}" class="course-action-btn">ویرایش</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16">
                            <div class="course-empty-state">
                                <div class="course-empty-icon">+</div>
                                <div class="text-sm font-black">هنوز دوره‌ای ساخته نشده است</div>
                                <p class="mt-1 max-w-md text-xs leading-6 text-[var(--color-text-muted)]">
                                    اولین دوره را بساز؛ بعد از آن می‌توانی محتوا، رسانه، مدرس و وضعیت انتشارش را مدیریت کنی.
                                </p>
                                <a href="{{ route('owner.courses.create') }}" class="course-primary-btn mt-4">ساخت اولین دوره</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[var(--color-border)] md:hidden">
            @forelse($courses as $course)
                <article class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-black">{{ $course->title }}</h3>
                            <p class="mt-1 text-[11px] text-[var(--color-text-muted)]">{{ $course->academy?->name }} · {{ $course->sections_count }} بخش</p>
                        </div>
                        <span class="course-status-badge {{ $course->status }}">
                            <i aria-hidden="true"></i>
                            {{ match($course->status){'published'=>'منتشر','archived'=>'آرشیو',default=>'پیش‌نویس'} }}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="course-mobile-metric">
                            <span>دسترسی</span>
                            <strong>{{ $course->isFree() ? 'رایگان' : number_format((float)$course->price,0,'.',',').' تومان' }}</strong>
                        </div>
                        <div class="course-mobile-metric">
                            <span>دانش‌آموز فعال</span>
                            <strong>{{ number_format($course->active_students_count) }}</strong>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('owner.courses.show',$course) }}" class="course-action-btn primary justify-center">بازبینی دوره</a>
                        <a href="{{ route('owner.courses.edit',$course) }}" class="course-action-btn justify-center">ویرایش</a>
                    </div>
                </article>
            @empty
                <div class="course-empty-state px-5 py-14">
                    <div class="course-empty-icon">+</div>
                    <div class="text-sm font-black">هنوز دوره‌ای ساخته نشده است</div>
                    <p class="mt-1 max-w-md text-xs leading-6 text-[var(--color-text-muted)]">اولین دوره را بساز و ادامه مسیر را از همین‌جا مدیریت کن.</p>
                    <a href="{{ route('owner.courses.create') }}" class="course-primary-btn mt-4">ساخت اولین دوره</a>
                </div>
            @endforelse
        </div>

        @if($courses->hasPages())
            <div class="border-t border-[var(--color-border)] p-4">
                {{ $courses->links() }}
            </div>
        @endif
    </section>
</div>
@endsection