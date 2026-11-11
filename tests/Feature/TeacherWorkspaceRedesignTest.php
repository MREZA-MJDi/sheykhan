<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherWorkspaceRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_home_uses_the_new_action_first_information_hierarchy(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('فضای کاری مدرس')
            ->assertSee('میانبر کارهای روزانه')
            ->assertSee('برنامه امروز')
            ->assertSee('کلاس‌های من')
            ->assertSee('کلاس‌های آنلاین پیش‌رو')
            ->assertSee('teacher-home__metrics', false);
    }

    public function test_teacher_work_queues_accept_search_and_status_filters(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.assignments.index', ['q' => 'ریاضی', 'status' => 'published', 'queue' => 'needs_review']))
            ->assertOk()
            ->assertSee('جست‌وجو و فیلتر تکالیف');

        $this->actingAs($teacher)
            ->get(route('teacher.exams.index', ['q' => 'ریاضی', 'status' => 'draft', 'queue' => 'needs_review']))
            ->assertOk()
            ->assertSee('جست‌وجو و فیلتر آزمون‌ها');

        $this->actingAs($teacher)
            ->get(route('teacher.live-classes.index', ['q' => 'ریاضی', 'status' => 'scheduled']))
            ->assertOk()
            ->assertSee('جست‌وجو و فیلتر جلسات');
    }

    public function test_teacher_student_directory_can_be_filtered_by_class(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.students.index'))
            ->assertOk()
            ->assertSee('name="classroom_id"', false)
            ->assertSee('همه کلاس‌های من');
    }

    public function test_teacher_can_filter_and_review_scoped_attendance_history(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.attendance.index', [
                'from' => now()->subDays(30)->toDateString(),
                'to' => now()->toDateString(),
                'status' => 'present',
            ]))
            ->assertOk()
            ->assertSee('تاریخچه حضور و غیاب')
            ->assertSee('اعمال فیلتر')
            ->assertSee('حاضر')
            ->assertSee('پاک‌کردن')
            ->assertSee('name="from"', false)
            ->assertSee('name="to"', false)
            ->assertSee('name="classroom_id"', false)
            ->assertSee('name="status"', false)
            ->assertSee('غایب')
            ->assertSee('موجه');
    }

    public function test_teacher_sidebar_uses_clear_grouped_labels_instead_of_duplicate_message_links(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('نمای کلی')
            ->assertSee('تدریس و کلاس‌ها')
            ->assertSee('ارزیابی و پیگیری')
            ->assertSee('ارتباط و گزارش')
            ->assertSee('کلاس‌های من')
            ->assertSee('کلاس آنلاین')
            ->assertSee('دوره‌ها و درس‌ها');
    }


    public function test_production_teacher_quick_login_matches_the_seeded_teacher_account(): void
    {
        $previousEnvironment = getenv('APP_ENV');

        try {
            putenv('APP_ENV=production');
            $productionConfig = require config_path('role_access.php');
        } finally {
            if ($previousEnvironment === false) {
                putenv('APP_ENV');
            } else {
                putenv('APP_ENV=' . $previousEnvironment);
            }
        }

        $teacherAccount = collect($productionConfig['test_accounts'])
            ->firstWhere('role_key', 'teacher');

        $seededMobile = '0912' . str_pad(
            (string) (abs(crc32('teacher-math')) % 10000000),
            7,
            '0',
            STR_PAD_LEFT,
        );

        $this->assertSame($seededMobile, $teacherAccount['username']);

        $this->seed();
        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->assertSame($seededMobile, $teacher->mobile);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('فضای کاری مدرس');
    }

    public function test_teacher_quick_login_uses_the_persisted_mobile_and_reaches_the_dashboard(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        // Simulate a demo account whose mobile was changed before this fix.
        $persistedMobile = '09125551234';
        $configuredMobile = (string) collect(config('role_access.test_accounts'))
            ->firstWhere('role_key', 'teacher')['username'];

        $teacher->forceFill(['mobile' => $persistedMobile])->save();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee($persistedMobile)
            ->assertDontSee($configuredMobile);

        $this->post(route('login.store'), [
            'mobile' => $persistedMobile,
            'password' => (string) config('role_access.test_password'),
        ])->assertRedirect(route('teacher.dashboard'));

        $this->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('فضای کاری مدرس');
    }

}
