<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LearningResource;
use App\Models\User;
use App\Services\AttendanceMetricsService;
use App\Services\StudentAccessService;
use App\Services\StudentLearningResourceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentClassroomEntitlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_paid_enrollment_in_one_class_does_not_unlock_resources_in_an_unpaid_class_of_the_same_course(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $paidClassroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();
        $unpaidClassroom = $this->createUnpaidSecondClassroom($student, $course, $paidClassroom);

        $paidResource = LearningResource::query()
            ->where('classroom_id', $paidClassroom->id)
            ->firstOrFail();

        $unpaidResource = $paidResource->replicate();
        $unpaidResource->title = 'جزوه گروه پرداخت‌نشده';
        $unpaidResource->classroom_id = $unpaidClassroom->id;
        $unpaidResource->save();

        $access = app(StudentAccessService::class);
        $resources = app(StudentLearningResourceService::class);

        $this->assertTrue($access->classroom($student, (int) $paidClassroom->id));
        $this->assertFalse($access->classroom($student, (int) $unpaidClassroom->id));

        $this->assertTrue($resources->canAccess($student, $paidResource));
        $this->assertFalse($resources->canAccess($student, $unpaidResource));

        $this->assertFalse(
            $resources->query($student)->whereKey($unpaidResource->id)->exists()
        );

        $this->actingAs($student)
            ->get(route('student.resources.view', $unpaidResource))
            ->assertNotFound();
    }

    public function test_attendance_rows_and_metrics_ignore_an_unpaid_second_classroom(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $paidClassroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();
        $unpaidClassroom = $this->createUnpaidSecondClassroom($student, $course, $paidClassroom);

        $access = app(StudentAccessService::class);
        $metrics = app(AttendanceMetricsService::class);
        $before = $metrics->forStudent($student);

        Attendance::query()->create([
            'classroom_id' => $unpaidClassroom->id,
            'student_id' => $student->id,
            'marked_by' => User::query()->where('email', 'teacher.science@sheykhan.test')->value('id'),
            'attendance_date' => today()->toDateString(),
            'scheduled_minutes' => 90,
            'attended_minutes' => 0,
            'status' => 'absent',
            'note' => 'ثبت آزمایشی کلاس بدون پرداخت',
        ]);

        $after = $metrics->forStudent($student);

        $this->assertFalse($access->attendanceBelongsToStudent($student, (int) $unpaidClassroom->id));
        $this->assertSame($before, $after);

        $this->actingAs($student)
            ->get(route('student.attendance.index'))
            ->assertOk()
            ->assertDontSee($unpaidClassroom->title);
    }

    private function createUnpaidSecondClassroom(
        User $student,
        Course $course,
        Classroom $referenceClassroom
    ): Classroom {
        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $teacher = User::query()->where('email', 'teacher.science@sheykhan.test')->firstOrFail();

        $classroom = Classroom::query()->create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'grade_id' => $referenceClassroom->grade_id,
            'academic_year_id' => $referenceClassroom->academic_year_id,
            'title' => 'کلاس دوم ریاضی با ثبت‌نام پرداخت‌نشده',
            'code' => 'MATH7-' . Str::upper(Str::random(8)),
            'description' => 'کلاس تست ایزولاسیون ثبت‌نام',
            'capacity' => 20,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        $classroom->teachers()->attach($teacher->id);
        $classroom->students()->attach($student->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        CourseEnrollment::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => null,
            'registered_by' => $owner->id,
            'registration_source' => 'admin',
            'status' => 'active',
            'payment_status' => 'pending',
            'price_amount' => $course->price,
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        return $classroom;
    }
}
