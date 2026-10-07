<?php

namespace Tests\Feature;

use App\Models\User;
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
            ->assertSee('آخرین نتیجه‌ها');
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
            ->assertDontSee('فروش دوره‌ها');
    }
}
