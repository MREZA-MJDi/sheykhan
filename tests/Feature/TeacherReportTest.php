<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_reports_page_with_metrics_and_charts(): void
    {
        $this->seed();
        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $response = $this->actingAs($teacher)
            ->get(route('teacher.reports.index'));

        $response->assertOk()
            ->assertViewIs('teacher.reports.index')
            ->assertSee('گزارش جامع عملکرد آموزشی')
            ->assertSee('دانش‌آموزان فعال')
            ->assertSee('میانگین پیشرفت یادگیری')
            ->assertSee('نرخ حضور و غیاب کلی')
            ->assertSee('عملکرد دوره‌های آموزشی')
            ->assertSee('کلاس‌ها و آمار حضور و غیاب')
            ->assertSee('برترین دانش‌آموزان در یادگیری')
            ->assertSee('دانش‌آموزان نیازمند توجه و پیگیری')
            ->assertSee('data-dashboard-chart', false)
            ->assertSee('data-chart-range="week"', false)
            ->assertSee('data-chart-range="month"', false)\n            ->assertSee('مشاهده فهرست دانش‌آموزان و ورود به پرونده‌ها')\n            ->assertSee('جزئیات دانش‌آموزان کلاس')\n            ->assertSee('تاریخچه حضور و غیاب');
    }

    public function test_unauthenticated_or_unauthorized_user_cannot_view_teacher_reports(): void
    {
        $this->get(route('teacher.reports.index'))
            ->assertRedirect(route('login'));

        $this->seed();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('teacher.reports.index'))
            ->assertForbidden();
    }
}
