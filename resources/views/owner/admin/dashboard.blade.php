@extends('owner.admin.layout')

@section('title', 'داشبورد آکادمی شیخان')
@section('header-title', 'داشبورد')
@section('content')
@php
    $ownerUser = auth()->user();
    $period = in_array($period ?? 'month', ['today', 'week', 'month'], true) ? ($period ?? 'month') : 'month';
    $periodCaption = $periodCaption ?? 'این ماه';
    $routeAcademyParam = request()->route('academy');
    $currentRouteAcademyId = $routeAcademyParam instanceof \App\Models\Academy
        ? (int) $routeAcademyParam->getKey()
        : (int) ($routeAcademyParam ?? 0);
    $firstAcademy = $academies->first();
    $hasAcademy = (bool) $firstAcademy;
    $hasMultipleAcademies = $academies->count() > 1;
    $canViewAcademy = (bool) $ownerUser?->hasPermission('academy.view');
    $canViewPeople = $canViewAcademy && $hasAcademy;
    $canViewClassrooms = (bool) $ownerUser?->hasPermission('classrooms.view') && $hasAcademy;
    $canViewCourses = (bool) $ownerUser?->hasPermission('courses.view');
    $canViewAttendance = (bool) $ownerUser?->hasPermission('attendance.view') && $hasAcademy;
    $canViewFinance = (bool) $ownerUser?->hasPermission('finance.view');
    $canViewReports = (bool) $ownerUser?->hasPermission('reports.view');
    $canViewAssignments = (bool) $ownerUser?->hasPermission('assignments.view') && $hasAcademy;
    $canViewMeetings = (bool) $ownerUser?->hasPermission('live_classes.view') && $hasAcademy;
    $canViewParents = (bool) $ownerUser?->hasPermission('parents.view') && $hasAcademy;
    $canViewExams = (bool) $ownerUser?->hasPermission('exams.view') && $hasAcademy;
    $canViewResources = (bool) $ownerUser?->hasPermission('resources.view') && $hasAcademy;
    $canViewProducts = (bool) $ownerUser?->hasPermission('products.view') && $hasAcademy;
    $canViewOrders = (bool) $ownerUser?->hasPermission('orders.view') && $hasAcademy;
    $canViewTeacherPayments = (bool) $ownerUser?->hasPermission('teacher_payments.view') && $hasAcademy;
    $canViewLegal = (bool) $ownerUser?->hasPermission('legal.manage');
    $metrics = $metrics ?? [];
    $capacity = $capacity ?? [];
    $teacherReports = $teacherReports ?? collect();
    $subjectAverages = $subjectAverages ?? collect();
    $pendingApprovalItems = $pendingApprovalItems ?? collect();
    $upcomingLiveClasses = $upcomingLiveClasses ?? collect();
    $recentCourses = $recentCourses ?? collect();
    $recentStudentLogins = $recentStudentLogins ?? collect();

    $students = max(0, (int) data_get($metrics, 'students', 0));
    $teachers = max(0, (int) data_get($metrics, 'teachers', 0));
    $classroomCount = max(0, (int) data_get($metrics, 'classrooms', 0));
    $courseCount = max(0, (int) data_get($metrics, 'courses', 0));
    $publishedCourseCount = max(0, (int) data_get($metrics, 'published', 0));
    $liveNowCount = max(0, (int) data_get($metrics, 'liveNow', 0));
    $pendingReviews = $canViewAssignments ? max(0, (int) data_get($metrics, 'pendingReviews', 0)) : 0;
    $capacityAlerts = $canViewClassrooms ? max(0, (int) data_get($metrics, 'capacityAlerts', 0)) : 0;
    $classroomsWithoutTeacher = $canViewClassrooms ? max(0, (int) data_get($metrics, 'classroomsWithoutTeacher', 0)) : 0;
    $attentionCount = $pendingReviews + $capacityAlerts + $classroomsWithoutTeacher;
    $attendanceRate = $canViewAttendance ? data_get($metrics, 'attendanceRate', data_get($metrics, 'todayAttendanceRate')) : null;
    $absentCount = $canViewAttendance ? max(0, (int) data_get($metrics, 'absentCount', 0)) : 0;
    $todayAbsentCount = $canViewAttendance ? max(0, (int) data_get($metrics, 'todayAbsentCount', 0)) : 0;
    $attendanceTotal = $canViewAttendance ? max(0, (int) data_get($metrics, 'attendanceTotal', 0)) : 0;
    $sales = $canViewFinance ? (float) data_get($metrics, 'sales', 0) : 0;
    $capacityTotal = max(0, (int) data_get($capacity, 'total', 0));
    $capacityUsed = max(0, (int) data_get($capacity, 'used', 0));
    $capacityRemaining = max(0, (int) data_get($capacity, 'remaining', 0));
    $capacityRate = data_get($capacity, 'rate');
    $capacityRate = $capacityRate === null ? null : min(100, max(0, (float) $capacityRate));
    $publishedRate = $courseCount > 0 ? min(100, (int) round(($publishedCourseCount / $courseCount) * 100)) : null;
    $nextLiveClass = $upcomingLiveClasses->first();
    $nextLiveTarget = $nextLiveClass?->scheduled_at?->timestamp;
    $trends = $trends ?? [];
    $todayLiveClasses = $todayLiveClasses ?? collect();
    $absentStudents = $absentStudents ?? collect();
    $transactionCount = $canViewFinance ? max(0, (int) data_get($metrics, 'transactionCount', 0)) : 0;
    $weeklyLabels = data_get($trends, 'weekly.labels', []);
    $weeklyStudents = data_get($trends, 'weekly.students', []);
    $weeklyAttendance = data_get($trends, 'weekly.attendance', []);

    $sparklinePoints = static function ($values): string {
        $values = collect($values ?? [])->filter(fn ($value) => is_numeric($value))->values();
        if ($values->count() < 2) {
            return '';
        }

        $minimum = (float) $values->min();
        $maximum = (float) $values->max();
        $spread = $maximum - $minimum;
        $lastIndex = max(1, $values->count() - 1);

        return $values->map(function ($value, $index) use ($minimum, $spread, $lastIndex): string {
            $x = number_format(($index / $lastIndex) * 160, 1, '.', '');
            $y = number_format($spread > 0 ? 28 - (((float) $value - $minimum) / $spread * 22) : 17, 1, '.', '');

            return $x . ',' . $y;
        })->implode(' ');
    };

    $chartSegments = static function ($values, int $width = 520, int $height = 150): array {
        $values = array_values(is_array($values) ? $values : []);
        $numeric = array_values(array_filter($values, static fn ($value) => is_numeric($value)));
        if (count($values) < 2 || count($numeric) < 2) {
            return [];
        }

        $minimum = (float) min($numeric);
        $maximum = (float) max($numeric);
        $spread = $maximum - $minimum;
        $lastIndex = max(1, count($values) - 1);
        $segments = [];
        $segment = [];

        foreach ($values as $index => $value) {
            if (!is_numeric($value)) {
                if (count($segment) > 1) {
                    $segments[] = implode(' ', $segment);
                }
                $segment = [];
                continue;
            }

            $x = number_format(($index / $lastIndex) * $width, 1, '.', '');
            $normalized = $spread > 0 ? (((float) $value - $minimum) / $spread) : 0.5;
            $y = number_format($height - 12 - ($normalized * ($height - 24)), 1, '.', '');
            $segment[] = $x . ',' . $y;
        }

        if (count($segment) > 1) {
            $segments[] = implode(' ', $segment);
        }

        return $segments;
    };

    $weeklyStudentSegments = $chartSegments($weeklyStudents);
    $weeklyAttendanceSegments = $chartSegments($weeklyAttendance);
    $studentSparkline = $sparklinePoints(data_get($trends, 'students', []));
    $attendanceSparkline = $sparklinePoints(data_get($trends, 'attendance', []));
    $salesSparkline = $sparklinePoints(data_get($trends, 'sales', []));
    $attentionSparkline = $sparklinePoints(data_get($trends, 'pendingReviews', []));

    $attentionRoute = $firstAcademy
        ? ($pendingReviews > 0 && $canViewAssignments
            ? route('owner.assignments.index', $firstAcademy)
            : ($canViewClassrooms
                ? route('owner.classrooms.index', $firstAcademy)
                : route('owner.assignments.index', $firstAcademy)))
        : route('owner.dashboard');

@endphp


        @include('owner.admin.dashboard.partials.overview')
@include('owner.admin.dashboard.partials.activity')
@include('owner.admin.dashboard.partials.upcoming')

@if($canViewPeople || $canViewAttendance || $canViewClassrooms || $canViewReports || $canViewCourses || $canViewLegal || $canViewExams || $canViewFinance)
    <details class="c12 group overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4">
            <span class="min-w-0">
                <strong class="block text-sm font-black text-slate-800 dark:text-slate-100">گزارش‌ها و آمار تکمیلی</strong>
                <small class="mt-1 block text-xs leading-6 text-slate-500">نمودار حضور، رشد دانش‌آموزان، وضعیت دوره‌ها، عملکرد مدرس‌ها و مجوز محتوا</small>
            </span>
            <span class="flex shrink-0 items-center gap-2 text-xs font-bold text-slate-500">
                <span>در صورت نیاز باز کن</span>
                <span aria-hidden="true" class="text-lg transition-transform group-open:rotate-180">⌄</span>
            </span>
        </summary>
        <div class="grid grid-cols-12 gap-4 border-t border-slate-100 p-4 dark:border-slate-700">
            @include('owner.admin.dashboard.partials.analytics')
            @include('owner.admin.dashboard.partials.reports')
        </div>
    </details>
@endif

@if(!$canViewPeople && !$canViewClassrooms && !$canViewCourses && !$canViewAttendance && !$canViewFinance && !$canViewReports && !$canViewAssignments && !$canViewMeetings && !$canViewOrders && !$canViewExams && !$canViewLegal)
            <section class="card c12"><div class="card-empty">برای نمایش شاخص‌ها، دسترسی مشاهده به بخش‌های مجاز این کارتابل برای این حساب فعال نیست.</div></section>
        @endif

@endsection
