<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Services\StudentAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCourseAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_lesson_is_available_as_preview_but_paid_lesson_requires_enrollment(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->with('sections.lessons')->firstOrFail();
        $freeLesson = $course->sections->flatMap(fn ($section) => $section->lessons)->firstWhere('is_free', true);
        $paidLesson = $course->sections->flatMap(fn ($section) => $section->lessons)->firstWhere('is_free', false);

        $studentWithoutEnrollment = User::where('email', 'student.nika@sheykhan.test')->firstOrFail();
        $access = app(StudentAccessService::class);

        $this->assertNotNull($freeLesson);
        $this->assertNotNull($paidLesson);
        $this->assertTrue($access->lesson($studentWithoutEnrollment, $freeLesson));
        $this->assertFalse($access->lesson($studentWithoutEnrollment, $paidLesson));

        $this->get(route('courses.lessons.preview', [$course, $freeLesson]))
            ->assertOk()
            ->assertSee($freeLesson->title);

        $this->get(route('courses.lessons.preview', [$course, $paidLesson]))
            ->assertNotFound();
    }

    public function test_partial_paid_enrollment_never_unlocks_protected_course_content(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $enrollment = $student->enrollments()->where('course_id', $course->id)->firstOrFail();

        $enrollment->update([
            'status' => 'active',
            'price_amount' => $course->price,
            'paid_amount' => (float) $course->price / 2,
            'payment_status' => 'partial',
        ]);

        $access = app(StudentAccessService::class);

        $this->assertFalse($access->course($student, $course));

        $enrollment->update([
            'payment_status' => 'paid',
        ]);

        $this->assertFalse(
            $access->course($student->fresh(), $course)
        );

        $enrollment->update([
            'paid_amount' => $course->price,
        ]);

        $this->assertTrue(
            $access->course($student->fresh(), $course)
        );
    }

    public function test_unrelated_student_cannot_use_paid_course_preview_to_bypass_enrollment(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->with('sections.lessons')->firstOrFail();
        $paidLesson = $course->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->firstWhere('is_free', false);

        $student = User::where('email', 'student.nika@sheykhan.test')->firstOrFail();

        $this->assertNotNull($paidLesson);
        $this->assertFalse(app(StudentAccessService::class)->lesson($student, $paidLesson));

        $this->actingAs($student)
            ->get(route('student.lessons.show', $paidLesson))
            ->assertNotFound();
    }
}
