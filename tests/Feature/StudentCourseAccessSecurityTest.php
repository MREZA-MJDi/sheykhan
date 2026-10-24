<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\FinancialTransaction;
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

        $enrollment->financialTransactions()->delete();

        $this->assertFalse(
            $access->course($student->fresh(), $course)
        );

        FinancialTransaction::create([
            'academy_id' => $course->academy_id,
            'enrollment_id' => $enrollment->id,
            'user_id' => $student->id,
            'recorded_by' => null,
            'type' => 'enrollment_payment',
            'status' => 'completed',
            'amount' => $course->price,
            'currency' => 'IRT',
            'reference' => 'test-enrollment-payment-' . $enrollment->id,
            'description' => 'test payment evidence',
            'metadata' => ['source' => 'security_test'],
            'occurred_at' => now(),
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
    public function test_removed_academy_membership_loses_protected_course_access(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->assertTrue(app(StudentAccessService::class)->course($student, $course));

        $student->academies()->updateExistingPivot($course->academy_id, ['status' => 'suspended']);

        $this->assertFalse(
            app(StudentAccessService::class)->course($student->fresh(), $course)
        );
    }



    public function test_active_non_student_academy_membership_does_not_preserve_student_course_access(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->with('sections.lessons')->firstOrFail();
        $freeLesson = $course->sections->flatMap(fn ($section) => $section->lessons)->firstWhere('is_free', true);
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $access = app(StudentAccessService::class);

        $this->assertNotNull($freeLesson);
        $this->assertTrue($student->hasRole('student'));
        $this->assertTrue($access->course($student, $course));
        $this->assertTrue($access->enrolledCourseIds($student)->contains((int) $course->id));

        // A stale active enrollment must not outlive the student's role within this academy.
        $student->academies()->updateExistingPivot($course->academy_id, [
            'role' => 'parent',
            'status' => 'active',
        ]);
        $student = $student->fresh();

        $this->assertTrue($student->hasRole('student'));
        $this->assertFalse($access->course($student, $course));
        $this->assertFalse($access->enrolledCourseIds($student)->contains((int) $course->id));
        $this->assertFalse($access->lesson($student, $freeLesson));

        $this->actingAs($student)
            ->get(route('student.courses.show', $course))
            ->assertNotFound();
    }

}
