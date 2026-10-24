<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
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
            ->assertSee('جلسات این هفته')
            ->assertSee('تکالیف منتظر تصحیح')
            ->assertSee('آزمون‌های منتظر بررسی')
            ->assertSee('روند پیشرفت ثبت‌شده')
            ->assertSee('class="app"', false)
            ->assertSee('id="teacher-progress"', false)
            ->assertSee('data-dashboard-chart', false)
            ->assertSee('data-chart-range="week"', false)
            ->assertSee('id="gs"', false)
            ->assertSee('role-mobile-bottom-nav', false)
            ->assertSee('data-role-more-open', false)
            ->assertSee('آخرین فعالیت‌ها')
            ->assertSee('href="' . route('teacher.classrooms.index') . '"', false)
            ->assertSee('href="' . route('teacher.courses.index') . '"', false)
            ->assertSee('href="' . route('teacher.schedule.index') . '"', false)
            ->assertSee('data-dashboard-search-empty', false)
            ->assertDontSee('فروش دوره‌ها')
            ->assertDontSee('td-dashboard', false);
    }

    public function test_teacher_dashboard_hides_workflows_without_view_permissions(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $teacherRole = Role::query()->where('slug', 'teacher')->firstOrFail();
        $teacherRole->permissions()->sync(
            Permission::query()
                ->whereIn('name', ['dashboard.view', 'profile.view'])
                ->pluck('id')
        );

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('title="داشبورد"', false)
            ->assertSee('title="پروفایل من"', false)
            ->assertDontSee('title="دوره‌های من"', false)
            ->assertDontSee('title="کلاس‌های من"', false)
            ->assertDontSee('title="برنامه هفتگی"', false)
            ->assertDontSee('title="کلاس‌های آنلاین"', false)
            ->assertDontSee('title="تکالیف"', false)
            ->assertDontSee('title="آزمون‌ها"', false)
            ->assertDontSee('title="حضور و غیاب"', false)
            ->assertDontSee('title="دانش‌آموزان"', false)
            ->assertDontSee('title="گزارش عملکرد"', false)
            ->assertDontSee('title="حقوق و پرداخت‌ها"', false)
            ->assertDontSee('id="tdb-progress"', false)
            ->assertDontSee('class="tdb-shortcuts"', false);
    }

    public function test_teacher_dashboard_excludes_inactive_accounts_from_roster_and_course_counts(): void
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
        $courseIds = $teacher->taughtCourses()
            ->whereIn('courses.academy_id', $activeAcademyIds)
            ->pluck('courses.id');

        $student = User::query()
            ->where('status', 'active')
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active'))
            ->whereHas('enrollments', fn ($query) => $query
                ->whereIn('classroom_id', $classroomIds)
                ->whereIn('course_id', $courseIds)
                ->where('status', 'active'))
            ->firstOrFail();

        $service = app(TeacherDashboardService::class);
        $before = $service->build($teacher);
        $beforeRosterCount = $before['metrics']['studentCount'];
        $beforeCourseRosterCount = collect($before['courseProgress'])->sum('student_count');
        $this->assertGreaterThan(0, $beforeRosterCount);
        $this->assertGreaterThan(0, $beforeCourseRosterCount);

        $student->forceFill(['status' => 'inactive'])->save();

        $after = $service->build($teacher);
        $this->assertSame($beforeRosterCount - 1, $after['metrics']['studentCount']);
        $this->assertLessThan(
            $beforeCourseRosterCount,
            collect($after['courseProgress'])->sum('student_count'),
        );
    }

    public function test_teacher_dashboard_roster_excludes_soft_deleted_student_accounts(): void
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

        $student->delete();

        $this->assertSame($before - 1, $service->build($teacher)['metrics']['studentCount']);
    }

    public function test_teacher_and_student_portals_use_consolidated_assets_and_shared_mobile_navigation(): void
    {
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));
        $teacherJs = file_get_contents(resource_path('js/teacher.js'));
        $teacherAdapter = file_get_contents(resource_path('js/teacher/panel.js'));
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $studentJs = file_get_contents(resource_path('js/student.js'));
        $appCss = file_get_contents(resource_path('css/app.css'));
        $appJs = file_get_contents(resource_path('js/app.js'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $dashboard = file_get_contents(resource_path('views/teacher/dashboard.blade.php'));
        $mobileNavigation = file_get_contents(resource_path('views/components/navigation/role-mobile-nav.blade.php'));
        $vite = file_get_contents(base_path('vite.config.js'));

        foreach ([$teacherCss, $teacherJs, $teacherAdapter, $studentCss, $studentJs, $appCss, $appJs,
            $teacherLayout, $studentLayout, $dashboard, $mobileNavigation, $vite] as $content) {
            $this->assertIsString($content);
        }

        $this->assertFileDoesNotExist(resource_path('css/teacher-dashboard.css'));
        $this->assertFileDoesNotExist(resource_path('js/teacher-dashboard.js'));
        $this->assertFileDoesNotExist(resource_path('views/layouts/teacher-dashboard.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/components/navigation/teacher-sidebar.blade.php'));
        $this->assertFileDoesNotExist(resource_path('css/seo.css'));
        $this->assertFileDoesNotExist(resource_path('js/seo.js'));
        $this->assertStringNotContainsString('teacher-dashboard.css', $vite);
        $this->assertStringNotContainsString('teacher-dashboard.js', $vite);
        $this->assertStringNotContainsString('seo.css', $vite);
        $this->assertStringNotContainsString('seo.js', $vite);

        $this->assertSame(1, substr_count($teacherCss, "@import './communication-pages.css';"));
        $this->assertSame(1, substr_count($teacherCss, '@import'));
        $this->assertStringContainsString('.k6', $teacherCss);
        $this->assertStringContainsString('.teacher-workspace-page', $teacherCss);
        $this->assertStringContainsString('.teacher-form-section', $teacherCss);
        $this->assertStringContainsString('@media (max-width:980px)', $teacherCss);
        $this->assertStringContainsString('@media (min-width: 761px) and (max-width: 1000px)', $teacherCss);
        $this->assertStringContainsString('@media (max-width: 760px)', $teacherCss);
        $this->assertStringContainsString('data-dashboard-search-empty', $dashboard);
        $this->assertStringContainsString("main.querySelector('[data-dashboard-search-empty]')", $teacherAdapter);
        $this->assertStringContainsString("import './teacher/app.js';", $teacherJs);
        $this->assertStringNotContainsString("import '../css/app.css';", $appJs);
        $this->assertStringNotContainsString("import '../css/student.css';", $studentJs);
        $this->assertStringNotContainsString("import '../css/teacher.css';", $teacherJs);
        foreach ([
            'resources/css/app.css', 'resources/css/home.css', 'resources/css/student.css',
            'resources/css/teacher.css', 'resources/css/admin.css', 'resources/css/parent.css',
        ] as $entry) {
            $this->assertSame(1, substr_count($vite, $entry), $entry . ' must be registered once in Vite.');
        }
        $this->assertStringContainsString("new Intl.DateTimeFormat('en-u-ca-persian-nu-latn'", $teacherJs);
        $this->assertStringContainsString('Validate Esfand 30', $teacherJs);
        $this->assertStringContainsString('data-dashboard-chart', $teacherAdapter);
        $this->assertStringContainsString('data-chart-range', $teacherAdapter);

        $this->assertStringContainsString('<x-navigation.role-mobile-nav role="teacher" />', $teacherLayout);
        $this->assertStringContainsString('<x-navigation.role-mobile-nav role="student" />', $studentLayout);
        $this->assertStringContainsString('id="student-profile-modal"', $studentLayout);
        $this->assertStringContainsString('data-student-profile-open', $studentLayout);
        $this->assertStringContainsString('data-student-theme-toggle', $studentJs);
        $this->assertStringContainsString('student-profile-modal', $studentCss);
        $this->assertStringContainsString('data-role-more-open', $mobileNavigation);
        $this->assertStringContainsString('data-role-more-panel', $mobileNavigation);
        $this->assertStringContainsString('Route::has($item[', $mobileNavigation);
        $this->assertStringContainsString('bootRoleMobileNavigation', $appJs);
        $this->assertStringContainsString('.role-mobile-bottom-nav', $appCss);
        $this->assertStringContainsString('.role-mobile-more-dialog', $appCss);

        $this->assertStringContainsString('id="teacher-progress"', $dashboard);
        $this->assertStringContainsString('data-dashboard-chart', $dashboard);
        $this->assertStringContainsString('$courseProgress', $dashboard);
        $this->assertStringContainsString('$activities', $dashboard);
        $this->assertStringContainsString('$todaySessions', $dashboard);
        $this->assertStringNotContainsString('teacher-dashboard-shell', $dashboard);
        $this->assertStringContainsString("@source '../../resources/views';", $appCss);
        $this->assertStringContainsString("@source '../../resources/views/components';", $appCss);
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
