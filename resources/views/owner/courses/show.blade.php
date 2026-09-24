@extends('layouts.owner')

@section('title',$course->title.' | مدیریت دوره')
@section('header-title','بازبینی دوره')

@section('content')
<div class="course-page panel-page-enter space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-xs font-black text-[var(--color-brand-600)]">بازبینی دوره</p>
                <span class="course-status-badge {{ $course->status }}">
                    <i aria-hidden="true"></i>
                    {{ match($course->status){'published'=>'منتشرشده','archived'=>'آرشیو',default=>'پیش‌نویس'} }}
                </span>
            </div>
            <h1 class="mt-2 break-words text-2xl font-black tracking-tight">{{ $course->title }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--color-text-secondary)]">
                {{ $course->short_description ?: 'برای این دوره هنوز خلاصه‌ای ثبت نشده است.' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('owner.courses.edit',$course) }}" class="course-primary-btn">ویرایش دوره</a>
            <a href="{{ route('owner.courses.index') }}" class="course-secondary-btn">بازگشت</a>
        </div>
    </div>

    <div class="course-readiness-strip {{ $course->status }}">
        <div class="course-readiness-icon">{{ $course->status === 'published' ? '✓' : ($course->status === 'archived' ? '!' : '•') }}</div>
        <div class="min-w-0">
            <strong>{{ match($course->status){'published'=>'این دوره منتشرشده است','archived'=>'این دوره آرشیو شده است',default=>'این دوره هنوز پیش‌نویس است'} }}</strong>
            <p>{{ $course->status === 'published' ? 'اطلاعات زیر وضعیت فعلی دوره را نشان می‌دهد.' : 'قبل از ارائه به دانش‌آموزها، اطلاعات و محتوای دوره را بررسی کن.' }}</p>
        </div>
        <span class="course-readiness-tag">{{ $course->isFree() ? 'رایگان' : 'پولی' }}</span>
    </div>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="خلاصه دوره">
        @foreach([
            ['دانش‌آموز فعال',$course->active_students_count,'ثبت‌نام فعال'],
            ['میانگین پیشرفت',$averageProgress.'٪','بر اساس درس‌های منتشرشده'],
            ['تکلیف',$course->assignments_count,'تکلیف ثبت‌شده'],
            ['آزمون',$course->exams_count,'آزمون ثبت‌شده'],
        ] as [$label,$value,$hint])
            <article class="course-stat">
                <span>{{ $label }}</span>
                <strong>{{ $value }}</strong>
                <small>{{ $hint }}</small>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(18rem,.75fr)]">
        <div class="panel-card p-5 sm:p-7">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black">ساختار محتوا</h2>
                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">بخش‌ها و درس‌های دوره.</p>
                </div>
                <span class="course-count-badge">{{ $course->sections->count() }} بخش</span>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse($course->sections as $section)
                    <article class="course-section-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="break-words text-sm font-black">{{ $section->title }}</h3>
                                @if($section->description)
                                    <p class="mt-1 text-xs leading-6 text-[var(--color-text-secondary)]">{{ $section->description }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-[10px] text-[var(--color-text-muted)]">{{ $section->lessons->count() }} درس</span>
                        </div>
                        <div class="mt-3 grid gap-2">
                            @forelse($section->lessons as $lesson)
                                <div class="flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2.5">
                                    <span class="min-w-0 truncate text-xs font-semibold">{{ $lesson->title }}</span>
                                    <span class="shrink-0 text-[9px] {{ $lesson->status==='published'?'text-[var(--color-success-700)]':'text-[var(--color-text-muted)]' }}">{{ $lesson->status==='published'?'منتشر':'پیش‌نویس' }}</span>
                                </div>
                            @empty
                                <div class="text-xs text-[var(--color-text-muted)]">درسی در این بخش ثبت نشده است.</div>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="course-empty-state rounded-2xl border-dashed border p-8">
                        <div class="text-sm font-black">ساختار محتوا هنوز آماده نیست</div>
                        <p class="mt-1 text-xs leading-6 text-[var(--color-text-muted)]">این صفحه فعلاً برای بازبینی دوره است؛ محتوای ساختاریافته از بخش‌های مرتبط مدیریت می‌شود.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="space-y-5">
            <section class="panel-card p-5">
                <h2 class="text-base font-black">مدرس‌ها</h2>
                <div class="mt-4 grid gap-2">
                    @forelse($course->teachers as $teacher)
                        <div class="flex items-center justify-between rounded-xl bg-[var(--color-background-soft)] px-3 py-3">
                            <span class="min-w-0 truncate text-xs font-bold">{{ $teacher->name }}</span>
                            @if($teacher->pivot->is_primary)
                                <span class="course-mini-badge">مدرس اصلی</span>
                            @endif
                        </div>
                    @empty
                        <div class="course-empty-note">مدرسی برای این دوره اختصاص داده نشده است.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-card p-5">
                <h2 class="text-base font-black">وضعیت و فروش</h2>
                <div class="mt-4 grid gap-3 text-xs">
                    <div class="course-detail-row"><span>وضعیت</span><strong>{{ match($course->status){'published'=>'منتشرشده','archived'=>'آرشیو',default=>'پیش‌نویس'} }}</strong></div>
                    <div class="course-detail-row"><span>دسترسی</span><strong>{{ $course->isFree() ? 'رایگان' : number_format((float)$course->price,0,'.',',').' تومان' }}</strong></div>
                    <div class="course-detail-row"><span>کلاس آنلاین</span><strong>{{ number_format($course->live_classes_count) }}</strong></div>
                    <div class="course-detail-row"><span>رسانه خصوصی</span><strong>{{ number_format($course->media->count()) }} فایل</strong></div>
                </div>
            </section>
        </aside>
    </section>

    <section class="grid gap-5 lg:grid-cols-2">
        <section class="panel-card p-5 sm:p-6">
            <div>
                <h2 class="text-base font-black">آزمون‌ها</h2>
                <p class="mt-1 text-xs text-[var(--color-text-muted)]">خلاصه آزمون‌های مرتبط با دوره.</p>
            </div>
            <div class="mt-4 grid gap-2">
                @forelse($course->exams as $exam)
                    <div class="course-list-card">
                        <div class="flex items-center justify-between gap-3">
                            <strong class="min-w-0 truncate text-xs">{{ $exam->title }}</strong>
                            <span class="text-[9px] {{ $exam->status==='published'?'text-[var(--color-success-700)]':'text-[var(--color-text-muted)]' }}">{{ $exam->status==='published'?'منتشر':'پیش‌نویس' }}</span>
                        </div>
                        <div class="mt-1 truncate text-[9px] text-[var(--color-text-muted)]">{{ $exam->teacher?->name ?: 'بدون مدرس' }} · {{ $exam->questions_count }} سؤال · {{ $exam->attempts_count }} تلاش</div>
                    </div>
                @empty
                    <div class="course-empty-note">آزمونی برای این دوره ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="panel-card p-5 sm:p-6">
            <div>
                <h2 class="text-base font-black">تکالیف</h2>
                <p class="mt-1 text-xs text-[var(--color-text-muted)]">مواردی که نیاز به بررسی دارند.</p>
            </div>
            <div class="mt-4 grid gap-2">
                @forelse($course->assignments as $assignment)
                    <div class="course-list-card">
                        <div class="flex items-center justify-between gap-3">
                            <strong class="min-w-0 truncate text-xs">{{ $assignment->title }}</strong>
                            <span class="text-[9px] font-bold">{{ $assignment->pending_submissions_count }} مورد نیازمند بررسی</span>
                        </div>
                        <div class="mt-1 truncate text-[9px] text-[var(--color-text-muted)]">{{ $assignment->teacher?->name ?: 'بدون مدرس' }} · {{ $assignment->submissions_count }} ارسال</div>
                    </div>
                @empty
                    <div class="course-empty-note">تکلیفی برای این دوره ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </section>
</div>
@endsection