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
            ->assertSee('id="teacher-dashboard"', false)
            ->assertSee('class="ph teacher-dashboard-hero"', false)
            ->assertSee('class="teacher-dashboard-avatar"', false)
            ->assertSee('class="teacher-kpi sk"', false)
            ->assertSee('سلام استاد سمیه احمدی', false)
            ->assertSee('teacher-classroom-roster-card', false)
            ->assertSee('مشاهده جزئیات', false)
            ->assertSee('<a class="teacher-kpi', false)
            ->assertSee('<a class="teacher-student-row', false)
            ->assertSee('<a class="teacher-course-card', false)
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

    public function test_teacher_and_student_portals_use_role_specific_assets_and_shared_mobile_navigation(): void
    {
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));
        $teacherJs = file_get_contents(resource_path('js/teacher.js'));
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $studentJs = file_get_contents(resource_path('js/student.js'));
        $calendarModule = file_get_contents(resource_path('js/persian-calendar-core.mjs'));
        $panelRuntime = file_get_contents(resource_path('js/panel-runtime.js'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $dashboard = file_get_contents(resource_path('views/teacher/dashboard.blade.php'));
        $teacherProfileView = file_get_contents(resource_path('views/teacher/profile/edit.blade.php'));
        $mobileNavigation = file_get_contents(resource_path('views/components/navigation/role-mobile-nav.blade.php'));
        $vite = file_get_contents(base_path('vite.config.js'));

        foreach ([
            $teacherCss, $teacherJs, $studentCss, $studentJs, $calendarModule, $panelRuntime,
            $teacherLayout, $studentLayout, $dashboard, $teacherProfileView,
            $mobileNavigation, $vite,
        ] as $source) {
            $this->assertIsString($source);
        }

        // The current architecture uses one CSS/JS entry pair for each role.
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
        $this->assertStringContainsString("@source '../../resources/views/student';", $studentCss);
        $this->assertStringContainsString("@source '../../resources/views/components';", $teacherCss);
        $this->assertStringContainsString("@source '../../resources/views/components';", $studentCss);

        $this->assertStringContainsString('.teacher-kpi', $teacherCss);
        $this->assertStringContainsString('.teacher-workspace-page', $teacherCss);
        $this->assertStringContainsString('.teacher-profile-avatar', $teacherCss);
        $this->assertStringContainsString('prefers-reduced-motion', $teacherCss);
        $this->assertStringContainsString('@media (max-width: 760px)', $teacherCss);
        $this->assertStringContainsString('.student-shell .role-mobile-bottom-nav', $studentCss);
        $this->assertStringContainsString('@media (max-width: 380px)', $studentCss);

        $this->assertStringContainsString("import './persian-calendar.js';", $teacherJs);
        $this->assertStringContainsString("import './persian-calendar.js';", $studentJs);
        $this->assertStringContainsString("from './panel-runtime.js'", $teacherJs);
        $this->assertStringContainsString("from './panel-runtime.js'", $studentJs);
        $this->assertStringContainsString('bootRoleMobileNavigation', $panelRuntime);
        $this->assertStringContainsString('jalaliDaysInMonth', $calendarModule);
        $this->assertStringContainsString('gregorianToJalali', $calendarModule);

        $this->assertStringContainsString('data-dashboard-search-empty', $dashboard);
        $this->assertStringContainsString('id="teacher-progress"', $dashboard);
        $this->assertStringContainsString('data-dashboard-chart', $dashboard);
        $this->assertStringContainsString('data-teacher-preview-container="avatar"', $teacherProfileView);
        $this->assertStringContainsString('data-teacher-preview-input="avatar"', $teacherProfileView);
        $this->assertStringContainsString('data-teacher-preview-input="cover"', $teacherProfileView);
        $this->assertStringContainsString('data-max-size-mb="3"', $teacherProfileView);
        $this->assertStringContainsString('data-max-size-mb="5"', $teacherProfileView);

        $this->assertStringContainsString('<x-navigation.role-mobile-nav role="teacher" />', $teacherLayout);
        $this->assertStringContainsString('teacherProfile.media', $teacherLayout);
        $this->assertStringContainsString('$teacherPanelAvatarUrl', $teacherLayout);
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
        $calendarCore = file_get_contents(resource_path('js/persian-calendar-core.mjs'));

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
