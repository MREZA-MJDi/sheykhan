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
}
