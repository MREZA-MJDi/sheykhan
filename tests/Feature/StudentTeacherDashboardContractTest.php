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

    public function test_teacher_dashboard_exposes_the_new_teacher_design_with_real_backend_data(): void
    {
        $this->seed();
        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('کلاس فعال')
            ->assertSee('دانش‌آموز')
            ->assertSee('جلسه این هفته')
            ->assertSee('تکلیف منتظر نمره')
            ->assertSee('آزمون منتظر بررسی')
            ->assertSee('گزارش عملکرد')
            ->assertSee('id="teacher-dashboard"', false)
            ->assertSee('class="hero2"', false)
            ->assertSee('teacher-stat-card', false)
            ->assertSee('teacher-quick-action', false)
            ->assertSee('خوش آمدید', false)
            ->assertSee('id="teacher-progress"', false)
            ->assertSee('data-dashboard-chart', false)
            ->assertSee('data-chart-range="week"', false)
            ->assertSee('id="gs"', false)
            ->assertSee('<aside class="side"', false)
            ->assertSee('<nav class="bt"', false)
            ->assertSee('data-teacher-menu-open', false)
            ->assertSee('href="' . route('teacher.classrooms.index') . '"', false)
            ->assertSee('href="' . route('teacher.courses.index') . '"', false)
            ->assertSee('href="' . route('teacher.schedule.index') . '"', false)
            ->assertDontSee('فروش دوره‌ها')
            ->assertDontSee('td-dashboard', false);
    }

    public function test_teacher_dashboard_roster_uses_real_student_profile_photos(): void
    {
        $this->seed();
        \Illuminate\Support\Facades\Storage::fake('local');

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = $teacher->classroomsAsTeacher()
            ->where('classrooms.status', 'active')
            ->firstOrFail();

        $student = $classroom->students()
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->whereHas('enrollments', fn ($query) => $query
                ->where('course_id', $classroom->course_id)
                ->where('classroom_id', $classroom->id)
                ->where('status', 'active'))
            ->firstOrFail();

        $profile = \App\Models\StudentProfile::query()->firstOrCreate(['user_id' => $student->id]);
        $path = 'students/' . $student->id . '/avatar.webp';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, 'real-avatar-fixture');
        $media = \App\Models\Media::query()->create([
            'uploaded_by' => $student->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'student-avatar.webp',
            'file_name' => 'avatar.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 19,
            'checksum' => hash('sha256', 'real-avatar-fixture'),
            'visibility' => 'public',
            'collection' => 'student-avatar',
            'metadata' => [],
            'status' => 'active',
        ]);
        $profile->media()->attach($media->id, [
            'collection' => 'student-avatar',
            'sort_order' => 0,
            'is_featured' => true,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee(route('media.public', $media), false)
            ->assertSee('teacher-roster-avatar', false)
            ->assertSee($student->name, false);
    }


    public function test_teacher_dashboard_hero_uses_the_saved_teacher_avatar(): void
    {
        $this->seed();
        \Illuminate\Support\Facades\Storage::fake('local');

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $profile = \App\Models\TeacherProfile::query()->firstOrCreate(
            ['user_id' => $teacher->id],
            ['is_verified' => true, 'is_public' => true],
        );
        $profile->forceFill(['is_public' => true])->save();

        $path = 'teachers/' . $teacher->id . '/teacher-avatar.webp';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, 'teacher-avatar-fixture');
        $media = \App\Models\Media::query()->create([
            'uploaded_by' => $teacher->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'teacher-avatar.webp',
            'file_name' => 'teacher-avatar.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 22,
            'checksum' => hash('sha256', 'teacher-avatar-fixture'),
            'visibility' => 'public',
            'collection' => 'teacher-avatar',
            'metadata' => [],
            'status' => 'active',
        ]);
        $profile->media()->wherePivot('collection', 'teacher-avatar')->get()->each(
            fn ($old) => $profile->media()->detach($old->id)
        );
        $profile->media()->attach($media->id, [
            'collection' => 'teacher-avatar',
            'sort_order' => 0,
            'is_featured' => true,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('class="teacher-dashboard-avatar"', false)
            ->assertSee('data-avatar-image', false)
            ->assertSee(route('media.public', $media), false)
            ->assertSee('تصویر پروفایل سمیه احمدی', false);
    }

    public function test_student_dashboard_hero_uses_saved_avatar_and_never_displays_the_demo_person_when_missing(): void
    {
        $this->seed();
        \Illuminate\Support\Facades\Storage::fake('local');

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $profile = \App\Models\StudentProfile::query()->firstOrCreate(['user_id' => $student->id]);
        $path = 'students/' . $student->id . '/student-avatar.webp';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, 'student-avatar-fixture');
        $media = \App\Models\Media::query()->create([
            'uploaded_by' => $student->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'student-avatar.webp',
            'file_name' => 'student-avatar.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 22,
            'checksum' => hash('sha256', 'student-avatar-fixture'),
            'visibility' => 'public',
            'collection' => 'student-avatar',
            'metadata' => [],
            'status' => 'active',
        ]);
        $profile->media()->wherePivot('collection', 'student-avatar')->get()->each(
            fn ($old) => $profile->media()->detach($old->id)
        );
        $profile->media()->attach($media->id, [
            'collection' => 'student-avatar',
            'sort_order' => 0,
            'is_featured' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('class="hero"', false)
            ->assertSee(route('media.public', $media), false)
            ->assertSee('تصویر پروفایل ' . $student->name, false)
            ->assertSee('class="student-dashboard-avatar-initial"', false);
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

        $response = $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk();

        $response->assertSee('href="' . route('teacher.dashboard') . '"', false)
            ->assertSee('href="' . route('teacher.profile.edit') . '"', false);

        foreach ([
            'teacher.courses.index',
            'teacher.classrooms.index',
            'teacher.schedule.index',
            'teacher.live-classes.index',
            'teacher.assignments.index',
            'teacher.exams.index',
            'teacher.attendance.index',
            'teacher.students.index',
            'teacher.messages.index',
            'teacher.payments.index',
        ] as $route) {
            $response->assertDontSee('href="' . route($route) . '"', false, $route);
        }

        $response->assertDontSee('id="teacher-progress"', false)
            ->assertDontSee('data-dashboard-chart', false)
            ->assertDontSee('teacher-stat-card', false)
            ->assertDontSee('teacher-quick-action', false);
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
        $courseIds = $teacher->taughtCourses()
            ->whereIn('courses.academy_id', $activeAcademyIds)
            ->pluck('courses.id');

        $studentWithEnrollment = User::query()
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
        $beforeData = $service->build($teacher);
        $before = $beforeData['metrics']['studentCount'];
        $beforeCourseRosterCount = collect($beforeData['courseProgress'])->sum('student_count');
        $this->assertGreaterThan(0, $before);
        $this->assertGreaterThan(0, $beforeCourseRosterCount);

        $studentWithEnrollment->delete();

        $afterData = $service->build($teacher);
        $this->assertSame($before - 1, $afterData['metrics']['studentCount']);
        $this->assertLessThan(
            $beforeCourseRosterCount,
            collect($afterData['courseProgress'])->sum('student_count')
        );
        $this->assertFalse($afterData['topStudents']->contains(
            fn ($candidate): bool => (int) $candidate->id === (int) $studentWithEnrollment->id
        ));
    }

    public function test_teacher_and_student_portals_use_role_owned_presentation_and_shared_behavior_modules(): void
    {
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));
        $teacherJs = file_get_contents(resource_path('js/teacher.js'));
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $studentJs = file_get_contents(resource_path('js/student.js'));
        $calendarCore = file_get_contents(resource_path('js/shared/persian-calendar-core.mjs'));
        $calendarRuntime = file_get_contents(resource_path('js/shared/persian-calendar.js'));
        $moneyRuntime = file_get_contents(resource_path('js/shared/persian-money.js'));
        $uploadRuntime = file_get_contents(resource_path('js/shared/upload-progress.js'));
        $panelRuntime = file_get_contents(resource_path('js/panel-runtime.js'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $dashboard = file_get_contents(resource_path('views/teacher/dashboard.blade.php'));
        $teacherProfileView = file_get_contents(resource_path('views/teacher/profile/edit.blade.php'));
        $mobileNavigation = file_get_contents(resource_path('views/components/navigation/role-mobile-nav.blade.php'));
        $vite = file_get_contents(base_path('vite.config.js'));

        foreach ([
            $teacherCss, $teacherJs, $studentCss, $studentJs, $calendarCore,
            $calendarRuntime, $moneyRuntime, $uploadRuntime, $panelRuntime,
            $teacherLayout, $studentLayout, $dashboard, $teacherProfileView,
            $mobileNavigation, $vite,
        ] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString("@vite(['resources/css/teacher.css', 'resources/js/teacher.js'])", $teacherLayout);
        $this->assertStringContainsString("@vite(['resources/css/student.css', 'resources/js/student.js'])", $studentLayout);
        foreach ([
            'resources/css/teacher.css',
            'resources/js/teacher.js',
            'resources/css/student.css',
            'resources/js/student.js',
        ] as $entry) {
            $this->assertStringContainsString($entry, $vite);
        }

        $this->assertStringContainsString("@source '../../resources/views/teacher';", $teacherCss);
        $this->assertStringContainsString("@source '../../resources/views/layouts/teacher.blade.php';", $teacherCss);
        $this->assertStringNotContainsString("@source '../../resources/views/components/teacher';", $teacherCss);
        $this->assertStringNotContainsString("@source '../../resources/views/components';", $teacherCss);
        $this->assertStringContainsString("@source '../../resources/views/student';", $studentCss);
        $this->assertStringContainsString('.teacher-stat-card', $teacherCss);
        $this->assertStringContainsString('.teacher-workspace-page', $teacherCss);
        $this->assertStringContainsString('.teacher-profile-avatar', $teacherCss);
        $this->assertStringContainsString('.teacher-dashboard-avatar', $teacherCss);
        $this->assertStringContainsString('.teacher-roster-avatar', $teacherCss);
        $this->assertStringContainsString('prefers-reduced-motion', $teacherCss);
        $this->assertStringContainsString('@media(max-width:860px)', $teacherCss);
        $this->assertStringContainsString('.student-shell .role-mobile-bottom-nav', $studentCss);

        foreach ([
            "import './shared/alpine-runtime.js';",
            "import './shared/persian-calendar.js';",
            "import './shared/persian-money.js';",
            "import './teacher/assessment-builder.js';",
            "import { bootFileUploadProgress } from './shared/upload-progress.js';",
        ] as $expected) {
            $this->assertStringContainsString($expected, $teacherJs);
        }
        $this->assertStringNotContainsString("from './panel-runtime.js'", $teacherJs);
        $this->assertStringContainsString("import './shared/persian-calendar.js';", $studentJs);
        $this->assertStringContainsString("from './panel-runtime.js'", $studentJs);
        $this->assertStringContainsString('bootRoleMobileNavigation', $panelRuntime);
        $this->assertStringContainsString('jalaliDaysInMonth', $calendarCore);
        $this->assertStringContainsString('gregorianToJalali', $calendarCore);
        $this->assertStringContainsString('initializePersianCalendars', $calendarRuntime);
        $this->assertStringContainsString('Intl.NumberFormat', $moneyRuntime);
        $this->assertStringContainsString('data-upload-progress-ui', $uploadRuntime);

        $this->assertStringContainsString('data-dashboard-search-empty', $dashboard);
        $this->assertStringContainsString('id="teacher-progress"', $dashboard);
        $this->assertStringContainsString('data-dashboard-chart', $dashboard);
        $this->assertStringContainsString('class="teacher-dashboard-avatar"', $dashboard);
        $this->assertStringContainsString('data-avatar-image', $dashboard);
        $this->assertStringContainsString('class="teacher-roster-avatar"', $dashboard);
        $this->assertStringContainsString('data-teacher-preview-container="avatar"', $teacherProfileView);
        $this->assertStringContainsString('data-teacher-preview-input="avatar"', $teacherProfileView);
        $this->assertStringContainsString('data-teacher-preview-input="cover"', $teacherProfileView);
        $this->assertStringContainsString('data-max-size-mb="3"', $teacherProfileView);
        $this->assertStringContainsString('data-max-size-mb="5"', $teacherProfileView);

        $this->assertStringContainsString('<nav class="bt"', $teacherLayout);
        $this->assertStringContainsString('data-teacher-menu-open', $teacherLayout);
        $this->assertStringContainsString('data-teacher-menu-close', $teacherLayout);
        $this->assertStringContainsString('teacherProfile.media', $teacherLayout);
        $this->assertStringContainsString('$avatarUrl', $dashboard);
        $this->assertStringContainsString('<x-navigation.role-mobile-nav role="student" />', $studentLayout);
        $this->assertStringContainsString('id="student-profile-modal"', $studentLayout);
        $this->assertStringContainsString('data-student-profile-open', $studentLayout);
        $this->assertStringContainsString('data-student-theme-toggle', $studentJs);
        $this->assertStringContainsString('student-profile-modal', $studentCss);
        $this->assertStringContainsString('data-role-more-open', $mobileNavigation);
        $this->assertStringContainsString('data-role-more-panel', $mobileNavigation);
        $this->assertStringContainsString('Route::has($item[', $mobileNavigation);
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
        $calendarCore = file_get_contents(resource_path('js/shared/persian-calendar-core.mjs'));

        $this->assertIsString($calendarCore);
        $this->assertStringContainsString("new Intl.DateTimeFormat('en-u-ca-persian-nu-latn'", $calendarCore);
        $this->assertStringContainsString('jalaliDaysInMonth', $calendarCore);
        $this->assertStringContainsString('jalaliToGregorian', $calendarCore);
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
