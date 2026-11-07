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
            ->assertSee('data-chart-range="month"', false)
            ->assertSee('مشاهده فهرست دانش‌آموزان و ورود به پرونده‌ها')
            ->assertSee('جزئیات دانش‌آموزان کلاس')
            ->assertSee('تاریخچه حضور و غیاب');
    }


    public function test_teacher_report_pages_courses_and_classrooms_instead_of_loading_every_row(): void
    {
        $this->seed();
        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $academy = $teacher->academies()->where('academies.status', 'active')->firstOrFail();

        for ($index = 1; $index <= 22; $index++) {
            $course = \App\Models\Course::query()->create([
                'academy_id' => $academy->id,
                'created_by' => $academy->owner_id,
                'title' => 'گزارش دوره ' . $index,
                'slug' => 'report-course-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
                'status' => 'draft',
                'access_type' => 'free',
                'price' => 0,
            ]);
            $course->teachers()->attach($teacher->id, ['is_primary' => true]);

            if ($index <= 13) {
                $classroom = \App\Models\Classroom::query()->create([
                    'academy_id' => $academy->id,
                    'course_id' => $course->id,
                    'title' => 'گزارش کلاس ' . $index,
                    'code' => 'RPT-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'status' => 'active',
                ]);
                $classroom->teachers()->attach($teacher->id);
            }
        }

        $response = $this->actingAs($teacher)->get(route('teacher.reports.index'));

        $response->assertOk()
            ->assertViewHas('courseReports', fn ($paginator): bool => $paginator->total() >= 22 && $paginator->perPage() === 20)
            ->assertViewHas('classroomReports', fn ($paginator): bool => $paginator->total() >= 13 && $paginator->perPage() === 12)
            ->assertSee('صفحه‌بندی گزارش دوره‌ها')
            ->assertSee('صفحه‌بندی گزارش کلاس‌ها');
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
