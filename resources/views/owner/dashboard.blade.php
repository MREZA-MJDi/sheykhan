@extends('layouts.owner')

@section('title', 'مدیریت آموزشگاه | شیخان')
@section('header-title', 'داشبورد آموزشگاه')

@section('content')
    @php
        $executionLabels = [
            'running' => 'در حال اجرا',
            'scheduled' => 'برنامه‌ریزی‌شده',
            'finished' => 'پایان‌یافته',
            'active' => 'فعال',
            'archived' => 'آرشیو',
        ];

        $firstAcademy = $academies->first();

        $attentionCount =
            (int) ($metrics['pendingReviews'] ?? 0) +
            (int) ($metrics['capacityAlerts'] ?? 0) +
            (int) ($metrics['classroomsWithoutTeacher'] ?? 0);

        $sales = (float) ($metrics['sales'] ?? 0);
        $attendanceRate = $metrics['todayAttendanceRate'] ?? null;
    @endphp

    <div class="owner-dashboard space-y-6 pb-8">

        {{-- =========================================================
            HERO
        ========================================================== --}}
        <section class="owner-hero relative overflow-hidden">
            <div class="owner-hero-copy relative z-10">
                <span class="owner-kicker">
                    مرکز کنترل آموزشگاه
                </span>

                <h1 class="mt-3">
                    @if($academies->count() > 1)
                        وضعیت آموزشگاه‌ها را از یک صفحه مدیریت کن.
                    @else
                        {{ $firstAcademy?->name ?? 'آموزشگاه شما' }} را از یک صفحه مدیریت کن.
                    @endif
                </h1>

                <p class="mt-4 max-w-2xl">
                    تصویر واقعی عملیات آموزشی امروز را ببین؛
                    از کلاس‌ها و مدرس‌ها تا دانش‌آموزان، حضور و غیاب، دوره‌ها و مواردی که نیاز به پیگیری دارند.
                </p>

                <div class="owner-hero-actions mt-6">
                    @if($firstAcademy)
                        <a
                            href="{{ route('owner.classrooms.index', $firstAcademy) }}"
                            class="owner-btn"
                        >
                            مدیریت کلاس‌ها
                        </a>

                        <a
                            href="{{ route('owner.people.index', $firstAcademy) }}"
                            class="owner-btn ghost"
                        >
                            مدیریت اعضا
                        </a>

                        <a href="{{ route('owner.resources.index', $firstAcademy) }}" class="owner-btn ghost">
                            جزوه‌ها و فایل‌ها
                        </a>

                        <a href="{{ route('owner.showcase.index', $firstAcademy) }}" class="owner-btn ghost">
                            افتخارآفرینان و رضایتمندی
                        </a>

                        <a href="{{ route('owner.orders.index', $firstAcademy) }}" class="owner-btn ghost">
                            سفارش‌ها و رسیدها
                        </a>
                    @endif

                    <a
                        href="{{ route('owner.reports.index') }}"
                        class="owner-btn ghost"
                    >
                        گزارش‌ها
                    </a>
                </div>
            </div>

            <div class="owner-hero-art" aria-hidden="true">
                <div class="owner-orb one"></div>
                <div class="owner-orb two"></div>

                <div class="owner-hero-stat">
                    <small>درآمد خالص ثبت‌شده</small>

                    <strong>
                        {{ number_format($sales, 0, '.', ',') }}
                    </strong>

                    <span>
                        از ledger پرداخت‌های تکمیل‌شده
                    </span>
                </div>
            </div>
        </section>


        <section class="owner-focus-card" aria-labelledby="owner-focus-title">
            <div class="owner-focus-status" aria-hidden="true">
                <span class="{{ $attentionCount > 0 ? 'has-alert' : 'is-clear' }}"></span>
            </div>

            <div class="owner-focus-copy">
                <span class="owner-focus-kicker">صف امروز</span>
                <h2 id="owner-focus-title">
                    @if(($metrics['classroomsWithoutTeacher'] ?? 0) > 0)
                        {{ $metrics['classroomsWithoutTeacher'] }} کلاس بدون مدرس داری.
                    @elseif(($metrics['capacityAlerts'] ?? 0) > 0)
                        {{ $metrics['capacityAlerts'] }} کلاس نزدیک ظرفیت است.
                    @elseif(($metrics['pendingReviews'] ?? 0) > 0)
                        {{ $metrics['pendingReviews'] }} مورد هنوز نیازمند بررسی است.
                    @else
                        امروز مورد بحرانی برای پیگیری نداری.
                    @endif
                </h2>
                <p>
                    این نوار فقط مواردی را بالا می‌آورد که از داده واقعی عملیات آموزشگاه می‌آیند؛
                    اولویت را باز کن و مستقیم سراغ همان کار برو.
                </p>
            </div>

            <div class="owner-focus-actions">
                @if(($metrics['classroomsWithoutTeacher'] ?? 0) > 0 && $firstAcademy)
                    <a href="{{ route('owner.classrooms.index', $firstAcademy) }}">تکمیل کلاس‌ها ←</a>
                @elseif(($metrics['capacityAlerts'] ?? 0) > 0 && $firstAcademy)
                    <a href="{{ route('owner.classrooms.index', $firstAcademy) }}">بررسی ظرفیت ←</a>
                @elseif(($metrics['pendingReviews'] ?? 0) > 0)
                    <a href="{{ route('owner.reports.index') }}">باز کردن گزارش ←</a>
                @else
                    <a href="{{ route('owner.courses.index') }}">مدیریت دوره‌ها ←</a>
                @endif
            </div>
        </section>

        {{-- =========================================================
            PRIMARY METRICS
        ========================================================== --}}
        <section class="owner-stats">

            <article class="owner-stat-card">
                <span>کلاس‌های فعال</span>

                <strong>
                    {{ \App\Support\PersianUi::digits($metrics['classrooms'] ?? 0) }}
                </strong>

                <small>
                    {{ \App\Support\PersianUi::digits($metrics['liveNow'] ?? 0) }}
                    کلاس آنلاین در حال اجرا
                </small>
            </article>

            <article class="owner-stat-card">
                <span>دانش‌آموز فعال</span>

                <strong>
                    {{ \App\Support\PersianUi::digits($metrics['students'] ?? 0) }}
                </strong>

                <small>
                    @if($attendanceRate === null)
                        حضور امروز ثبت نشده
                    @else
                        حضور امروز {{ $attendanceRate }}٪
                    @endif
                </small>
            </article>

            <article class="owner-stat-card">
                <span>مدرس فعال</span>

                <strong>
                    {{ \App\Support\PersianUi::digits($metrics['teachers'] ?? 0) }}
                </strong>

                <small>
                    {{ \App\Support\PersianUi::digits($metrics['courses'] ?? 0) }}
                    دوره در آموزشگاه‌ها
                </small>
            </article>

            <article class="owner-stat-card">
                <span>نیازمند توجه</span>

                <strong>
                    {{ $attentionCount }}
                </strong>

                <small>
                    {{ $metrics['pendingReviews'] ?? 0 }}
                    بررسی ·
                    {{ $metrics['capacityAlerts'] ?? 0 }}
                    کلاس نزدیک ظرفیت
                </small>
            </article>

        </section>


        {{-- =========================================================
            QUICK CONTROL SUMMARY
        ========================================================== --}}
        <section class="grid gap-4 md:grid-cols-3">

            <article class="dashboard-panel p-5">
                <span class="text-[10px] font-bold text-slate-500">
                    حضور امروز
                </span>

                <div class="mt-2 flex items-end gap-2">
                    <strong class="text-2xl font-black">
                        {{ $attendanceRate === null ? '—' : $attendanceRate . '٪' }}
                    </strong>

                    <span class="pb-1 text-[10px] text-slate-400">
                        بر اساس رکوردهای امروز
                    </span>
                </div>
            </article>

            <article class="dashboard-panel p-5">
                <span class="text-[10px] font-bold text-slate-500">
                    دوره‌های منتشرشده
                </span>

                <div class="mt-2 flex items-end gap-2">
                    <strong class="text-2xl font-black">
                        {{ $metrics['published'] ?? 0 }}
                    </strong>

                    <span class="pb-1 text-[10px] text-slate-400">
                        از {{ $metrics['courses'] ?? 0 }} دوره
                    </span>
                </div>
            </article>

            <article class="dashboard-panel p-5">
                <span class="text-[10px] font-bold text-slate-500">
                    موارد کنترل
                </span>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="owner-pill">
                        {{ $metrics['pendingReviews'] ?? 0 }} تکلیف معوق
                    </span>

                    <span class="owner-pill">
                        {{ $metrics['capacityAlerts'] ?? 0 }} ظرفیت بالا
                    </span>

                    <span class="owner-pill">
                        {{ $metrics['classroomsWithoutTeacher'] ?? 0 }} بدون مدرس
                    </span>
                </div>
            </article>

        </section>


        {{-- =========================================================
            ACADEMIES
        ========================================================== --}}
        <section>
            <div class="owner-panel-head mb-4">
                <div>
                    <h2>آموزشگاه‌های شما</h2>
                    <p>
                        وضعیت و دسترسی سریع به آموزشگاه‌های فعال
                    </p>
                </div>
            </div>

            @if($academies->isNotEmpty())
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($academies as $academy)
                        <article class="dashboard-panel overflow-hidden p-5">

                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <span class="owner-kicker">
                                        آموزشگاه
                                    </span>

                                    <h3 class="mt-2 truncate text-lg font-black text-slate-950">
                                        {{ $academy->name }}
                                    </h3>

                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $academy->city ?: 'شهر ثبت نشده' }}
                                        <span class="mx-1">·</span>
                                        فعال
                                    </p>
                                </div>

                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--panel-primary-soft)] text-sm font-black text-[var(--panel-primary)]">
                                    {{ mb_substr($academy->name, 0, 1) }}
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-2 md:grid-cols-4">
                                <a
                                    href="{{ route('owner.people.index', $academy) }}"
                                    class="owner-pill justify-center"
                                >
                                    اعضا
                                </a>

                                <a
                                    href="{{ route('owner.classrooms.index', $academy) }}"
                                    class="owner-pill justify-center"
                                >
                                    کلاس‌ها
                                </a>

                                <a
                                    href="{{ route('owner.academy.edit', $academy) }}"
                                    class="owner-pill success justify-center"
                                >
                                    تنظیمات
                                </a>

                                <a
                                    href="{{ route('owner.academy.banners.edit', $academy) }}"
                                    class="owner-pill banner justify-center"
                                >
                                    بنرها
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="dashboard-panel owner-empty p-8 text-center">
                    برای این حساب هنوز آموزشگاه فعالی ثبت نشده است.
                </div>
            @endif
        </section>


        {{-- =========================================================
            TODAY'S CLASSROOMS
        ========================================================== --}}
        @if($classrooms->isNotEmpty())
            <section class="dashboard-panel owner-panel">

                <div class="owner-panel-head">
                    <div>
                        <h2>کلاس‌های امروز</h2>

                        <p>
                            تصویر اجرایی کلاس‌ها از داده‌های واقعی سیستم
                        </p>
                    </div>

                    @if($firstAcademy)
                        <a
                            href="{{ route('owner.classrooms.index', $firstAcademy) }}"
                            class="owner-link"
                        >
                            همه کلاس‌ها ←
                        </a>
                    @endif
                </div>

                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                    @foreach($classrooms as $classroom)

                        <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:border-slate-200 hover:bg-white">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3 class="truncate text-sm font-black text-slate-950">
                                            {{ $classroom->title }}
                                        </h3>

                                        <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-slate-600">
                                            {{ $executionLabels[$classroom->execution_status] ?? 'فعال' }}
                                        </span>

                                    </div>

                                    <p class="mt-1 truncate text-[10px] text-slate-500">
                                        {{ $classroom->course?->title }}
                                        <span class="mx-1">·</span>
                                        {{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}
                                    </p>
                                </div>

                                <a
                                    href="{{ route('owner.classrooms.show', [$classroom->academy, $classroom]) }}"
                                    class="owner-pill shrink-0"
                                >
                                    جزئیات
                                </a>

                            </div>


                            {{-- Classroom metrics --}}
                            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">

                                <div class="rounded-xl bg-white p-3 text-center">
                                    <strong class="block text-sm font-black text-slate-950">
                                        {{ $classroom->active_students_count }}
                                    </strong>

                                    <span class="text-[9px] text-slate-400">
                                        دانش‌آموز
                                    </span>
                                </div>

                                <div class="rounded-xl bg-white p-3 text-center">
                                    <strong class="block text-sm font-black text-slate-950">
                                        {{ $classroom->capacity ?? '∞' }}
                                    </strong>

                                    <span class="text-[9px] text-slate-400">
                                        ظرفیت
                                    </span>
                                </div>

                                <div class="rounded-xl bg-white p-3 text-center">
                                    <strong class="block text-sm font-black text-slate-950">
                                        {{ $classroom->capacity_remaining ?? '∞' }}
                                    </strong>

                                    <span class="text-[9px] text-slate-400">
                                        خالی
                                    </span>
                                </div>

                                <div class="rounded-xl bg-white p-3 text-center">
                                    <strong class="block text-sm font-black text-slate-950">
                                        {{ $classroom->today_attendance_rate === null ? '—' : $classroom->today_attendance_rate . '٪' }}
                                    </strong>

                                    <span class="text-[9px] text-slate-400">
                                        حضور امروز
                                    </span>
                                </div>

                            </div>


                            {{-- Occupancy --}}
                            @if($classroom->occupancy_percent !== null)
                                <div class="mt-4">

                                    <div class="mb-2 flex items-center justify-between text-[9px] text-slate-500">
                                        <span>پرشدگی</span>

                                        <strong>
                                            {{ $classroom->occupancy_percent }}٪
                                        </strong>
                                    </div>

                                    <div class="h-1.5 overflow-hidden rounded-full bg-white">
                                        <span
                                            class="block h-full rounded-full bg-[var(--panel-primary)] transition-all duration-500"
                                            style="width: {{ min(100, max(0, $classroom->occupancy_percent)) }}%"
                                        ></span>
                                    </div>

                                </div>
                            @endif


                            {{-- Live class --}}
                            @if($classroom->live_now)
                                <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2.5">
                                    <div class="flex items-center justify-between gap-3">

                                        <span class="flex shrink-0 items-center gap-2 text-[9px] font-black text-emerald-700">
                                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                                            در حال اجرا
                                        </span>

                                        <span class="truncate text-[10px] font-bold text-emerald-900">
                                            {{ $classroom->live_now->title }}
                                        </span>

                                    </div>
                                </div>
                            @endif

                        </article>
                    @endforeach
                </div>

            </section>
        @endif


        {{-- =========================================================
            TEACHERS + UPCOMING LIVE CLASSES
        ========================================================== --}}
        <div class="owner-grid">

            {{-- Teachers --}}
            <section class="dashboard-panel owner-panel">

                <div class="owner-panel-head">
                    <div>
                        <h2>برآیند مدرس‌ها</h2>

                        <p>
                            دوره، دانش‌آموز، پیشرفت و کارهای معوق
                        </p>
                    </div>

                    <a
                        href="{{ route('owner.reports.index') }}"
                        class="owner-link"
                    >
                        جزئیات ←
                    </a>
                </div>

                <div class="owner-teacher-list">

                    @forelse($teacherReports as $teacher)
                        <article class="owner-teacher-row">

                            <div class="owner-avatar">
                                {{ mb_substr($teacher->name, 0, 1) }}
                            </div>

                            <div class="min-w-0">
                                <div class="owner-row-title">
                                    {{ $teacher->name }}
                                </div>

                                <div class="owner-row-meta">
                                    {{ $teacher->course_count }} دوره
                                    ·
                                    {{ $teacher->student_count }} دانش‌آموز
                                    ·
                                    {{ $teacher->pending_reviews }} بررسی معوق
                                </div>

                                <div class="owner-progress">
                                    <span
                                        style="width: {{ min(100, max(0, (float) $teacher->progress_average)) }}%"
                                    ></span>
                                </div>
                            </div>

                            <div class="owner-teacher-metric">
                                <strong>
                                    {{ $teacher->progress_average }}٪
                                </strong>

                                <small>
                                    پیشرفت میانگین
                                </small>
                            </div>

                        </article>
                    @empty
                        <div class="owner-empty">
                            هنوز مدرس فعالی ثبت نشده است.
                        </div>
                    @endforelse

                </div>
            </section>


            {{-- Upcoming live classes --}}
            <section class="dashboard-panel owner-panel">

                <div class="owner-panel-head">
                    <div>
                        <h2>جلسات آنلاین آینده</h2>

                        <p>
                            هفت روز آینده
                        </p>
                    </div>
                </div>

                <div class="owner-live-list">

                    @forelse($upcomingLiveClasses as $item)
                        <article class="owner-live-row">

                            <div class="owner-pill shrink-0">
                                {{ \App\Support\PersianUi::date(\Illuminate\Support\Carbon::parse($item->scheduled_at)) }}
                                ·
                                {{ \App\Support\PersianUi::time(\Illuminate\Support\Carbon::parse($item->scheduled_at)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="owner-row-title truncate">
                                    {{ $item->title }}
                                </div>

                                <div class="owner-row-meta truncate">
                                    {{ $item->course?->title }}
                                    ·
                                    {{ $item->teacher?->name }}
                                </div>
                            </div>

                            <span class="owner-pill success shrink-0">
                                {{ $item->classroom?->title ?? 'آنلاین' }}
                            </span>

                        </article>
                    @empty
                        <div class="owner-empty">
                            جلسه آنلاینی در هفت روز آینده ثبت نشده است.
                        </div>
                    @endforelse

                </div>
            </section>

        </div>


        {{-- =========================================================
            RECENT COURSES
        ========================================================== --}}
        <section class="dashboard-panel owner-panel">

            <div class="owner-panel-head">
                <div>
                    <h2>آخرین دوره‌ها</h2>

                    <p>
                        انتشار و دانش‌آموز فعال
                    </p>
                </div>

                <a
                    href="{{ route('owner.courses.index') }}"
                    class="owner-link"
                >
                    همه دوره‌ها ←
                </a>
            </div>

            <div class="owner-course-list">

                @forelse($recentCourses as $course)
                    <article class="owner-course-row">

                        <div class="min-w-0">
                            <div class="owner-row-title truncate">
                                {{ $course->title }}
                            </div>

                            <div class="owner-row-meta truncate">
                                {{ $course->academy?->name ?? 'بدون آموزشگاه' }}
                                ·
                                {{ $course->active_students_count }} دانش‌آموز
                            </div>
                        </div>

                        <span class="owner-pill {{ $course->status === 'published' ? 'success' : '' }}">
                            {{ $course->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}
                        </span>

                        <a
                            href="{{ route('owner.courses.show', $course) }}"
                            class="owner-pill shrink-0"
                        >
                            بازبینی
                        </a>

                    </article>
                @empty
                    <div class="owner-empty">
                        دوره‌ای هنوز ثبت نشده است.
                    </div>
                @endforelse

            </div>
        </section>


        {{-- =========================================================
            FOOTER QUICK ACTIONS
        ========================================================== --}}
        @if($firstAcademy)
            <section class="grid gap-3 sm:grid-cols-3">

                <a
                    href="{{ route('owner.classrooms.index', $firstAcademy) }}"
                    class="dashboard-panel group p-5 transition hover:-translate-y-0.5 hover:border-slate-200"
                >
                    <span class="text-xs font-bold text-slate-500">
                        عملیات
                    </span>

                    <strong class="mt-2 flex items-center justify-between text-sm font-black text-slate-950">
                        مدیریت کلاس‌ها
                        <span class="transition-transform group-hover:-translate-x-1">
                            ←
                        </span>
                    </strong>
                </a>

                <a
                    href="{{ route('owner.people.index', $firstAcademy) }}"
                    class="dashboard-panel group p-5 transition hover:-translate-y-0.5 hover:border-slate-200"
                >
                    <span class="text-xs font-bold text-slate-500">
                        اعضای آموزشگاه
                    </span>

                    <strong class="mt-2 flex items-center justify-between text-sm font-black text-slate-950">
                        مدیریت دانش‌آموز و مدرس
                        <span class="transition-transform group-hover:-translate-x-1">
                            ←
                        </span>
                    </strong>
                </a>

                <a
                    href="{{ route('owner.reports.index') }}"
                    class="dashboard-panel group p-5 transition hover:-translate-y-0.5 hover:border-slate-200"
                >
                    <span class="text-xs font-bold text-slate-500">
                        تحلیل
                    </span>

                    <strong class="mt-2 flex items-center justify-between text-sm font-black text-slate-950">
                        گزارش‌های آموزشگاه
                        <span class="transition-transform group-hover:-translate-x-1">
                            ←
                        </span>
                    </strong>
                </a>

            </section>
        @endif

    </div>
@endsection
