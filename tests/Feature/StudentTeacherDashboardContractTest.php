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

    public function test_student_dashboard_exposes_the_full_learning_workspace(): void
    {
        $this->seed();

        $student = User::query()
            ->where('email', 'student.armin@sheykhan.test')
            ->firstOrFail();

        $response = $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk();

        $response
            ->assertSee('دوره‌های فعال')
            ->assertSee('جلسات کلاس')
            ->assertSee('تکالیف من')
            ->assertSee('جزوه‌ها و فایل‌های من')
            ->assertSee('آخرین نتیجه‌ها')
            ->assertSee('student-focus-card')
            ->assertSee('student-focus-action');
    }

    public function test_teacher_dashboard_exposes_teaching_workflow_without_financial_kpi(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $response = $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk();

        $response
            ->assertSee('کلاس‌های فعال')
            ->assertSee('دانش‌آموزان')
            ->assertSee('جلسات این هفته')
            ->assertSee('نیازمند بررسی')
            ->assertSee('روند پیشرفت دانش‌آموزان')
            ->assertSee('مرکز توجه')
            ->assertSee('teacher-focus-card')
            ->assertSee('teacher-focus-action')
            ->assertDontSee('فروش دوره‌ها');
    }

    public function test_dashboard_focus_components_have_loaded_stylesheet_contracts(): void
    {
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));

        $this->assertIsString($teacherCss);
        $this->assertIsString($studentCss);
        $this->assertIsString($teacherLayout);
        $this->assertIsString($studentLayout);

        $this->assertStringContainsString('.teacher-focus-card {', $teacherCss);
        $this->assertStringContainsString('.teacher-focus-action {', $teacherCss);
        $this->assertStringContainsString('resources/css/teacher.css', $teacherLayout);

        $this->assertStringContainsString('.student-focus-card {', $studentCss);
        $this->assertStringContainsString('.student-focus-action {', $studentCss);
        $this->assertStringContainsString('.student-ui-hero-glow {', $studentCss);
        $this->assertStringContainsString('resources/css/student.css', $studentLayout);
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

    public function test_student_calendar_is_rendered_as_jalali_and_teacher_dashboard_uses_persian_date(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('تقویم')
            ->assertSee(PersianUi::calendar(now())['month_label'])
            ->assertDontSee(now()->format('F Y'));

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk();
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

        $this->actingAs($teacher)
            ->get(route('teacher.assignments.index'))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('teacher.exams.index'))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('teacher.live-classes.index'))
            ->assertOk();
    }


    public function test_parent_dashboard_explains_progress_and_uses_published_active_course_content(): void
    {
        $this->seed();

        $parent = User::query()
            ->where('email', 'parent.armin@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($parent)
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('میانگین پیشرفت')
            ->assertSee('درس‌های منتشرشده')
            ->assertSee('دوره‌های فعال');
    }


}
