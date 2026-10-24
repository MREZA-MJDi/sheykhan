<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_teacher_dashboard_exposes_teaching_workflow_without_financial_kpi(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('کلاس‌های فعال')
            ->assertSee('دانش‌آموزان')
            ->assertSee('جلسات این هفته')
            ->assertSee('تکالیف منتظر تصحیح')
            ->assertSee('آزمون‌های منتظر بررسی')
            ->assertSee('روند پیشرفت دانش‌آموزان')
            ->assertSee('tdb-focus-card', false)
            ->assertSee('tdb-focus-action', false)
            ->assertSee('tdb-dashboard', false)
            ->assertSee('teacher-dashboard-shell', false)
            ->assertSee('data-teacher-theme-toggle', false)
            ->assertSee('آخرین تکالیف')
            ->assertDontSee('فروش دوره‌ها');
    }

    public function test_student_and_teacher_stylesheet_entrypoints_are_kept_separate(): void
    {
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));
        $teacherDashboardCss = file_get_contents(resource_path('css/teacher-dashboard.css'));
        $teacherDashboardLayout = file_get_contents(resource_path('views/layouts/teacher-dashboard.blade.php'));
        $teacherSidebar = file_get_contents(resource_path('views/components/navigation/teacher-sidebar.blade.php'));
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $appCss = file_get_contents(resource_path('css/app.css'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));

        $this->assertIsString($teacherCss);
        $this->assertIsString($teacherDashboardCss);
        $this->assertIsString($teacherDashboardLayout);
        $this->assertIsString($teacherSidebar);
        $this->assertIsString($studentCss);
        $this->assertIsString($appCss);
        $this->assertIsString($teacherLayout);
        $this->assertIsString($studentLayout);

        $this->assertStringContainsString('.tdb-focus-card', $teacherDashboardCss);
        $this->assertStringContainsString('.tdb-focus-action', $teacherDashboardCss);
        $this->assertStringContainsString("@vite(['resources/css/teacher-dashboard.css', 'resources/js/teacher-dashboard.js'])", $teacherDashboardLayout);
        $this->assertStringContainsString('.tdb-dashboard', $teacherDashboardCss);
        $this->assertStringContainsString('body.teacher-dashboard-shell .tdb-app', $teacherDashboardCss);
        $this->assertStringContainsString('@media(max-width:1250px)', $teacherDashboardCss);
        $this->assertStringContainsString('aria-label="دوره‌های من"', $teacherSidebar);
        $this->assertStringContainsString("request()->routeIs('teacher.dashboard')", $teacherLayout);
        $this->assertStringContainsString('data-teacher-theme-toggle', $teacherDashboardLayout);
        $this->assertStringContainsString('resources/css/teacher.css', $teacherLayout);
        // Tailwind source scanning is centralized in app.css (source(none));
        // the role stylesheet is plain CSS and stays focused on component styling.
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
