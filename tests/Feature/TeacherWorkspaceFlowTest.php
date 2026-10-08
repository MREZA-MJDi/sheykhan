<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherWorkspaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_workspace_entries_are_available_and_paginated(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        foreach ([
            'teacher.courses.index',
            'teacher.classrooms.index',
            'teacher.schedule.index',
            'teacher.attendance.index',
            'teacher.assignments.index',
            'teacher.exams.index',
            'teacher.live-classes.index',
        ] as $route) {
            $this->actingAs($teacher)
                ->get(route($route))
                ->assertOk();
        }

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.content', $course))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.progress', $course))
            ->assertOk();
    }

    public function test_teacher_can_create_a_course_section_for_owned_course(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.sections.store', $course), [
                'title' => 'سرفصل تست',
                'description' => 'توضیح تست',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('course_sections', [
            'course_id' => $course->id,
            'title' => 'سرفصل تست',
            'description' => 'توضیح تست',
        ]);
    }

    public function test_teacher_can_create_a_class_with_the_manage_permission(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.classrooms.store'), [
                'course_id' => $course->id,
                'title' => 'کلاس تست استاد',
                'code' => 'TEST-MATH-01',
                'capacity' => 20,
            ])
            ->assertRedirect(route('teacher.classrooms.index'));

        $this->assertDatabaseHas('classrooms', [
            'course_id' => $course->id,
            'title' => 'کلاس تست استاد',
            'code' => 'TEST-MATH-01',
        ]);
    }

    public function test_overlapping_weekly_schedule_is_rejected(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();

        $classroom->schedules()->create([
            'weekday' => 6,
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.schedule.store'), [
                'classroom_id' => $classroom->id,
                'weekday' => 6,
                'start_time' => '10:00',
                'end_time' => '11:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, $classroom->schedules()->count());
    }

    public function test_live_class_creation_persists_a_calculated_end_time(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $scheduledAt = Carbon::now()->addDay()->setTime(18, 0, 0);

        $this->actingAs($teacher)
            ->post(route('teacher.live-classes.store'), [
                'course_id' => $course->id,
                'title' => 'جلسه تست آنلاین',
                'provider' => 'Jitsi',
                'meeting_url' => 'https://meet.jit.si/sheykhan-test',
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'duration_minutes' => 60,
            ])
            ->assertRedirect(route('teacher.live-classes.index'));

        $this->assertDatabaseHas('live_classes', [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'status' => 'scheduled',
            'scheduled_end_at' => $scheduledAt->copy()->addHour()->toDateTimeString(),
        ]);
    }

    public function test_attendance_edit_loads_existing_status_for_the_selected_date(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();
        $student = $classroom->students()->wherePivot('status', 'active')->firstOrFail();

        Attendance::create([
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
            'marked_by' => $teacher->id,
            'attendance_date' => '2026-10-08',
            'status' => 'absent',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.classrooms.attendance.edit', [
                'classroom' => $classroom,
                'attendance_date' => '2026-10-08',
            ]))
            ->assertOk()
            ->assertSee('value="absent" selected', false);
    }
}
