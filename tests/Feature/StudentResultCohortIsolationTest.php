<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\StudentDashboardService;
use App\Services\StudentResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentResultCohortIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_results_require_the_assessment_teacher_to_match_the_students_active_cohort(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherTeacher = User::query()->where('email', 'teacher.science@sheykhan.test')->firstOrFail();

        $assignment = Assignment::query()->where('title', 'تکلیف هفته 1')->firstOrFail();
        $exam = Exam::query()->where('title', 'آزمون دوره 1')->firstOrFail();

        $service = app(StudentResultService::class);

        $initialTitles = $service->paginate($student, 50)->getCollection()->pluck('title')->all();

        $this->assertContains($assignment->title, $initialTitles);
        $this->assertContains($exam->title, $initialTitles);

        // Graded history remains visible when a teacher later closes an assignment.
        $assignment->update(['status' => 'closed']);
        $closedAssignmentTitles = $service->paginate($student, 50)->getCollection()->pluck('title')->all();
        $this->assertContains($assignment->title, $closedAssignmentTitles);
        $assignment->update(['status' => 'published']);

        // Course-level items remain visible only while their teacher belongs
        // to this student's active classroom cohort.
        $assignment->update(['classroom_id' => null]);
        $exam->update(['classroom_id' => null]);

        $courseLevelTitles = $service->paginate($student, 50)->getCollection()->pluck('title')->all();

        $this->assertContains($assignment->title, $courseLevelTitles);
        $this->assertContains($exam->title, $courseLevelTitles);

        // A different teacher in the same academy/course context cannot expose
        // their results just because the assessment has no classroom_id.
        $assignment->update(['teacher_id' => $otherTeacher->id]);
        $exam->update(['teacher_id' => $otherTeacher->id]);

        $isolatedTitles = $service->paginate($student, 50)->getCollection()->pluck('title')->all();

        $this->assertNotContains($assignment->title, $isolatedTitles);
        $this->assertNotContains($exam->title, $isolatedTitles);
    }
    public function test_results_do_not_leak_through_an_unpaid_enrollment_in_a_second_classroom(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherTeacher = User::query()->where('email', 'teacher.science@sheykhan.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $dashboardService = app(StudentDashboardService::class);
        $baselineDashboard = $dashboardService->build($student);
        $originalClassroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        $otherClassroom = Classroom::query()->create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'grade_id' => $originalClassroom->grade_id,
            'academic_year_id' => $originalClassroom->academic_year_id,
            'title' => 'کلاس ریاضی گروه دوم برای تست دسترسی',
            'code' => 'MATH7-02-RESULTS',
            'description' => 'کلاس مستقل تست ایزولاسیون',
            'capacity' => 20,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);
        $otherClassroom->teachers()->attach($otherTeacher->id);
        $otherClassroom->students()->attach($student->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        // The student already has a valid paid enrollment in the same course,
        // but that must not unlock this second classroom's unpaid enrollment.
        CourseEnrollment::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'classroom_id' => $otherClassroom->id,
            'academic_year_id' => null,
            'registered_by' => $owner->id,
            'registration_source' => 'admin',
            'status' => 'active',
            'payment_status' => 'pending',
            'price_amount' => $course->price,
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => $otherTeacher->id,
            'title' => 'نتیجه تکلیف گروه پرداخت‌نشده',
            'instructions' => 'این نتیجه نباید در پنل دانش‌آموز گروه اول دیده شود.',
            'due_at' => now()->subDay(),
            'max_score' => 20,
            'status' => 'published',
        ]);
        AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'content' => 'پاسخ ثبت‌شده برای تست',
            'submitted_at' => now()->subDays(2),
            'score' => 18,
            'graded_at' => now()->subDay(),
            'graded_by' => $otherTeacher->id,
        ]);

        $exam = Exam::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => $otherTeacher->id,
            'title' => 'نتیجه آزمون گروه پرداخت‌نشده',
            'description' => 'این نتیجه نباید افشا شود.',
            'duration_minutes' => 30,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
            'attempts_allowed' => 1,
            'status' => 'closed',
        ]);
        ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subDays(2),
            'submitted_at' => now()->subDays(2)->addMinutes(20),
            'score' => 17,
            'status' => 'graded',
        ]);

        $resultTitles = app(StudentResultService::class)
            ->paginate($student, 50)
            ->getCollection()
            ->pluck('title')
            ->all();

        $this->assertNotContains($assignment->title, $resultTitles);
        $this->assertNotContains($exam->title, $resultTitles);

        $pendingAssignment = Assignment::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => $otherTeacher->id,
            'title' => 'تکلیف باز گروه پرداخت‌نشده',
            'instructions' => 'این تکلیف نباید وارد آمار یا پیش‌نمایش شود.',
            'due_at' => now()->addDay(),
            'max_score' => 20,
            'status' => 'published',
        ]);

        $dashboard = $dashboardService->build($student);

        $this->assertSame($baselineDashboard['pendingAssignments'], $dashboard['pendingAssignments']);
        $this->assertSame($baselineDashboard['dueTomorrowCount'], $dashboard['dueTomorrowCount']);
        $this->assertFalse($dashboard['assignments']->contains('title', $pendingAssignment->title));
        $this->assertFalse($dashboard['recentResults']->contains('title', $assignment->title));
        $this->assertFalse($dashboard['recentResults']->contains('title', $exam->title));
        $this->assertFalse($dashboard['teachers']->contains('id', $otherTeacher->id));
    }

}
