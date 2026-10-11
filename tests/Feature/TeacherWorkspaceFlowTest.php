<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use App\Services\MeetingLinkService;
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

        $before = $classroom->schedules()->count();

        $classroom->schedules()->create([
            'weekday' => 6,
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.schedule.store'), [
                'classroom_id' => $classroom->id,
                'weekday' => 6,
                'provider' => 'Jitsi',
                'start_time' => '10:00',
                'end_time' => '11:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertSame($before + 1, $classroom->schedules()->count());

    }

    public function test_weekly_schedule_generates_a_jitsi_meeting_link(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.schedule.store'), [
                'classroom_id' => $classroom->id,
                'weekday' => 6,
                'provider' => 'Jitsi',
                'start_time' => '09:00',
                'end_time' => '10:30',
                'room' => 'کلاس آنلاین',
            ])
            ->assertSessionHasNoErrors();

        $schedule = $classroom->schedules()->where('weekday', 6)->latest('id')->firstOrFail();

        $this->assertSame('Jitsi', $schedule->provider);
        $this->assertStringStartsWith('https://meet.jit.si/Sheykhan-', (string) $schedule->meeting_url);
        $this->assertGreaterThan(20, strlen((string) $schedule->meeting_url));
    }

    public function test_weekly_schedule_keeps_a_real_google_meet_link_and_provider(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();

        $url = 'https://meet.google.com/abc-defg-hij';

        $this->actingAs($teacher)
            ->post(route('teacher.schedule.store'), [
                'classroom_id' => $classroom->id,
                'weekday' => 5,
                'start_time' => '23:00',
                'end_time' => '23:30',
                'provider' => 'Google Meet',
                'meeting_url' => $url,
                'room' => 'جلسه Meet',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('class_schedules', [
            'classroom_id' => $classroom->id,
            'weekday' => 5,
            'provider' => 'Google Meet',
            'meeting_url' => $url,
        ]);
    }

    public function test_live_class_creation_generates_a_jitsi_link_when_provider_is_selected_without_a_url(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $scheduledAt = Carbon::now()->addDays(2)->setTime(18, 0, 0);

        $this->actingAs($teacher)
            ->post(route('teacher.live-classes.store'), [
                'course_id' => $course->id,
                'title' => 'جلسه Jitsi خودکار',
                'provider' => 'Jitsi',
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'duration_minutes' => 45,
            ])
            ->assertRedirect(route('teacher.live-classes.index'))
            ->assertSessionHasNoErrors();

        $class = \App\Models\LiveClass::query()->where('title', 'جلسه Jitsi خودکار')->firstOrFail();
        $this->assertStringStartsWith('https://meet.jit.si/Sheykhan-', (string) $class->meeting_url);
        $this->assertDatabaseHas('live_classes', [
            'id' => $class->id,
            'provider' => 'Jitsi',
            'scheduled_end_at' => $scheduledAt->copy()->addMinutes(45)->toDateTimeString(),
        ]);
    }

    public function test_google_meet_rejects_a_jitsi_url_instead_of_mislabelling_the_provider(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.live-classes.store'), [
                'course_id' => $course->id,
                'title' => 'لینک ناسازگار',
                'provider' => 'Google Meet',
                'meeting_url' => 'https://meet.jit.si/not-a-google-meet',
                'scheduled_at' => Carbon::now()->addDays(2)->toDateTimeString(),
                'duration_minutes' => 30,
            ])
            ->assertSessionHasErrors('meeting_url');

        $this->assertDatabaseMissing('live_classes', ['title' => 'لینک ناسازگار']);
    }

    public function test_meeting_link_service_requires_a_room_path_for_known_providers(): void
    {
        $service = app(MeetingLinkService::class);

        $this->assertFalse($service->urlMatchesProvider('Jitsi', 'https://meet.jit.si'));
        $this->assertFalse($service->urlMatchesProvider('Google Meet', 'https://meet.google.com'));
        $this->assertFalse($service->urlMatchesProvider('Zoom', 'https://zoom.us'));

        $this->assertTrue($service->urlMatchesProvider('Jitsi', 'https://meet.jit.si/Sheykhan-test-room'));
        $this->assertTrue($service->urlMatchesProvider('Google Meet', 'https://meet.google.com/abc-defg-hij'));
        $this->assertTrue($service->urlMatchesProvider('Zoom', 'https://us02web.zoom.us/j/123456789'));
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

    public function test_teacher_can_store_attendance_with_a_jalali_date(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();
        $student = $classroom->students()->wherePivot('status', 'active')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.classrooms.attendance.store', $classroom), [
                'attendance_date' => '۱۴۰۵/۰۷/۱۶',
                'attendance' => [
                    $student->id => 'absent',
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
            'attendance_date' => '2026-10-08',
            'status' => 'absent',
            'marked_by' => $teacher->id,
        ]);
    }

    public function test_attendance_edit_loads_existing_status_for_the_selected_date(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();
        $student = $classroom->students()->wherePivot('status', 'active')->firstOrFail();

        $attendance = Attendance::query()
            ->where('classroom_id', $classroom->id)
            ->where('student_id', $student->id)
            ->whereDate('attendance_date', '2026-10-08')
            ->first();

        if (! $attendance) {
            $attendance = new Attendance([
                'classroom_id' => $classroom->id,
                'student_id' => $student->id,
                'attendance_date' => '2026-10-08',
            ]);
        }

        $attendance->forceFill([
            'marked_by' => $teacher->id,
            'status' => 'absent',
        ])->save();

        $attendanceUrl = route('teacher.classrooms.attendance.edit', $classroom)
            . '?attendance_date=2026-10-08';

        $this->actingAs($teacher)
            ->get($attendanceUrl)
            ->assertOk()
            ->assertSee('value="absent" selected', false);
    }
}
