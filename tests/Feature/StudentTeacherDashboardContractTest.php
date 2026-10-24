<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TeacherDashboardService;
use App\Support\PersianUi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTeacherDashboardContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_renders_backend_connected_data(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('خانه یادگیری من')
            ->assertSee('دوره‌های فعال')
            ->assertSee('student-shell', false)
            ->assertDontSee('sdr-hero', false);
    }

    public function test_teacher_dashboard_exposes_backend_driven_teaching_workflow_without_financial_kpi(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('کلاس‌های فعال')
            ->assertSee('دانش‌آموزان')
            ->assertSee('کلاس‌های آنلاین این هفته')
            ->assertSee('تکلیف‌های منتظر تصحیح')
            ->assertSee('آزمون‌های منتظر بررسی')
            ->assertSee('روند پیشرفت دانش‌آموزان')
            ->assertSee('tdb-focus-card', false)
            ->assertSee('tdb-focus-action', false)
            ->assertSee('tdb-dashboard', false)
            ->assertSee('teacher-dashboard-shell', false)
            ->assertSee('data-teacher-theme-toggle', false)
            ->assertSee('data-teacher-range', false)
            ->assertSee('data-dashboard-search', false)
            ->assertSee('teacher-dashboard.css')
            ->assertSee('teacher-dashboard.js')
            ->assertSee('آخرین تکالیف')
            ->assertDontSee('فروش دوره‌ها')
            ->assertDontSee('td-dashboard', false);
    }

    public function test_teacher_dashboard_student_count_excludes_inactive_accounts(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $activeAcademyIds = $teacher->academies()
            ->where('academies.status', 'active')
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->pluck('academies.id');
        $classroomIds = $teacher->classroomsAsTeacher()
            ->whereIn('classrooms.academy_id', $activeAcademyIds)
            ->where('classrooms.status', 'active')
            ->pluck('classrooms.id');

        $student = User::query()
            ->where('status', 'active')
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active'))
            ->firstOrFail();

        $service = app(TeacherDashboardService::class);
        $before = $service->build($teacher)['metrics']['studentCount'];
        $this->assertGreaterThan(0, $before);

        $student->forceFill(['status' => 'inactive'])->save();

        $this->assertSame($before - 1, $service->build($teacher)['metrics']['studentCount']);
    }

    public function test_student_and_teacher_dashboard_assets_are_isolated_from_shared_teacher_assets(): void
    {
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));
        $teacherDashboardCss = file_get_contents(resource_path('css/teacher-dashboard.css'));
        $teacherJs = file_get_contents(resource_path('js/teacher.js'));
        $teacherDashboardJs = file_get_contents(resource_path('js/teacher-dashboard.js'));
        $teacherSidebar = file_get_contents(resource_path('views/components/navigation/teacher-sidebar.blade.php'));
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $appCss = file_get_contents(resource_path('css/app.css'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $teacherDashboardLayout = file_get_contents(resource_path('views/layouts/teacher-dashboard.blade.php'));
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));

        $this->assertIsString($teacherCss);
        $this->assertIsString($teacherDashboardCss);
        $this->assertIsString($teacherJs);
        $this->assertIsString($teacherDashboardJs);
        $this->assertIsString($teacherSidebar);
        $this->assertIsString($studentCss);
        $this->assertIsString($appCss);
        $this->assertIsString($teacherLayout);
        $this->assertIsString($teacherDashboardLayout);
        $this->assertIsString($studentLayout);

        $this->assertStringContainsString("@import './communication-pages.css';", $teacherCss);
        $this->assertStringNotContainsString("@import './teacher-dashboard.css';", $teacherCss);
        $this->assertStringNotContainsString('.teacher-focus-card {', $teacherCss);
        $this->assertStringNotContainsString('.teacher-welcome-card {', $teacherCss);
        $this->assertStringContainsString('.tdb-focus-card', $teacherDashboardCss);
        $this->assertStringContainsString('.tdb-focus-action', $teacherDashboardCss);
        $this->assertStringContainsString("@vite(['resources/css/teacher-dashboard.css', 'resources/js/teacher-dashboard.js'])", $teacherDashboardLayout);
        $this->assertStringContainsString('.tdb-dashboard', $teacherDashboardCss);
        $this->assertStringContainsString('body.teacher-dashboard-shell .tdb-app', $teacherDashboardCss);
        $this->assertStringContainsString('@media(max-width:1250px)', $teacherDashboardCss);
        $this->assertStringContainsString('body.teacher-dashboard-shell .tdb-dashboard', $teacherDashboardCss);
        $this->assertStringContainsString('@media(max-width:720px)', $teacherDashboardCss);
        $this->assertStringContainsString('data-teacher-theme-toggle', $teacherDashboardJs);
        $this->assertStringContainsString('data-teacher-range', $teacherDashboardJs);
        $this->assertStringContainsString("new Intl.DateTimeFormat('en-u-ca-persian-nu-latn'", $teacherJs);
        $this->assertStringNotContainsString('[data-teacher-dashboard]', $teacherJs);
        $this->assertStringContainsString('aria-label="دوره‌های من"', $teacherSidebar);
        $this->assertStringContainsString('id="teacher-dashboard-sidebar"', $teacherSidebar);
        $this->assertStringContainsString('resources/css/teacher-dashboard.css', $teacherDashboardLayout);
        $this->assertStringContainsString('resources/js/teacher-dashboard.js', $teacherDashboardLayout);
        $this->assertStringNotContainsString('resources/css/teacher.css', $teacherDashboardLayout);
        $this->assertStringNotContainsString('resources/js/teacher.js', $teacherDashboardLayout);
        $this->assertStringContainsString("request()->routeIs('teacher.dashboard')", $teacherLayout);
        $this->assertStringContainsString('resources/css/teacher.css', $teacherLayout);
        // Tailwind source scanning remains centralized in app.css (source(none)).
        $this->assertStringContainsString("@source '../../resources/views';", $appCss);
        $this->assertStringContainsString("@source '../../resources/views/components';", $appCss);
        $this->assertStringContainsString('resources/css/app.css', $studentLayout);
        $this->assertStringContainsString('resources/css/student.css', $studentLayout);
        $this->assertStringContainsString('#dashboard.page', $studentCss);
        $this->assertStringContainsString('@media (max-width: 640px)', $studentCss);
        $this->assertStringNotContainsString('resources/css/student-dashboard-reference.css', $studentLayout);
        $this->assertStringContainsString('resources/js/student.js', $studentLayout);
    }

    public function test_persian_calendar_conversion_is_jalali(): void
    {
        $calendar = PersianUi::calendar(Carbon::create(2026, 10, 8));

        $this->assertSame(1405, $calendar['year']);
        $this->assertSame(7, $calendar['month']);
        $this->assertSame(16, $calendar['day']);
        $this->assertSame('مهر', $calendar['month_name']);
        $this->assertSame(30, $calendar['days_in_month']);
    }

    public function test_student_workspace_no_longer_renders_the_legacy_calendar_and_teacher_dashboard_remains_available(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('خانه یادگیری من')
            ->assertSee('student-shell', false)
            ->assertDontSee('تقویم')
            ->assertDontSee(now()->format('F Y'));

        $this->actingAs($teacher)->get(route('teacher.dashboard'))->assertOk();
    }

    public function test_teacher_jalali_calendar_has_esfand_leap_year_detection(): void
    {
        $teacherJs = file_get_contents(resource_path('js/teacher.js'));

        $this->assertIsString($teacherJs);
        $this->assertStringContainsString("new Intl.DateTimeFormat('en-u-ca-persian-nu-latn'", $teacherJs);
        $this->assertStringContainsString('Validate Esfand 30', $teacherJs);
    }

    public function test_teacher_lists_are_paginated_contracts(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)->get(route('teacher.assignments.index'))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.exams.index'))->assertOk();
        $this->actingAs($teacher)->get(route('teacher.live-classes.index'))->assertOk();
    }

    public function test_parent_dashboard_explains_progress_and_uses_published_active_course_content(): void
    {
        $this->seed();

        $parent = User::query()->where('email', 'parent.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($parent)
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('میانگین پیشرفت')
            ->assertSee('درس‌های منتشرشده')
            ->assertSee('دوره‌های فعال');
    }
}
