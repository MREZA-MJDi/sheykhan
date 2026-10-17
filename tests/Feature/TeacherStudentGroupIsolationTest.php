<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\User;
use App\Services\CourseLearningProgressService;
use App\Services\StudentAssignmentService;
use App\Services\StudentExamService;
use App\Services\TeacherAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherStudentGroupIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_course_does_not_allow_one_teacher_to_see_another_teachers_roster_or_assessments(): void
    {
        $this->seed();

        $teacherA = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $teacherB = User::where('email', 'teacher.science@sheykhan.test')->firstOrFail();
        $studentA = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $studentB = User::where('email', 'student.ava@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        // Use a free published course to keep this test about roster isolation,
        // not about payment gateway fixtures.
        $course->update(['access_type' => 'free', 'price' => 0]);
        $course->teachers()->syncWithoutDetaching([
            $teacherB->id => ['is_primary' => false],
        ]);

        $classA = Classroom::where('course_id', $course->id)
            ->where('code', 'MATH7-01')
            ->firstOrFail();

        $classB = Classroom::create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'grade_id' => $classA->grade_id,
            'academic_year_id' => $classA->academic_year_id,
            'title' => 'ریاضی هفتم - گروه استاد دوم',
            'code' => 'MATH7-TCH2',
            'description' => 'گروه مجزای همان دوره برای تست مرز دسترسی مدرس‌ها.',
            'capacity' => 20,
            'status' => 'active',
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->addMonths(3),
        ]);
        $classB->teachers()->sync([$teacherB->id]);

        $this->moveStudentToClass($studentA, $course, $classA);
        $this->moveStudentToClass($studentB, $course, $classB);

        $otherTeacherAssignment = Assignment::create([
            'course_id' => $course->id,
            'classroom_id' => null,
            'teacher_id' => $teacherB->id,
            'title' => 'تکلیف ویژه گروه استاد دوم',
            'instructions' => 'این تکلیف نباید برای گروه استاد اول دیده شود.',
            'due_at' => now()->addDays(2),
            'max_score' => 20,
            'status' => 'published',
        ]);

        $otherTeacherExam = Exam::create([
            'course_id' => $course->id,
            'classroom_id' => null,
            'teacher_id' => $teacherB->id,
            'title' => 'آزمون ویژه گروه استاد دوم',
            'description' => 'این آزمون فقط برای کلاس‌های استاد دوم است.',
            'duration_minutes' => 30,
            'attempts_allowed' => 1,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addDay(),
            'status' => 'published',
        ]);

        $this->assertTrue(
            app(StudentAssignmentService::class)->query($studentB)
                ->whereKey($otherTeacherAssignment->id)
                ->exists()
        );
        $this->assertFalse(
            app(StudentAssignmentService::class)->query($studentA)
                ->whereKey($otherTeacherAssignment->id)
                ->exists()
        );

        $this->assertTrue(
            app(StudentExamService::class)->query($studentB)
                ->whereKey($otherTeacherExam->id)
                ->exists()
        );
        $this->assertFalse(
            app(StudentExamService::class)->query($studentA)
                ->whereKey($otherTeacherExam->id)
                ->exists()
        );

        $teacherAProgress = app(CourseLearningProgressService::class)
            ->forTeacher($teacherA, $course);
        $rosterIds = $teacherAProgress['students']->getCollection()
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $this->assertContains((int) $studentA->id, $rosterIds);
        $this->assertNotContains((int) $studentB->id, $rosterIds);

        // Student-facing teacher cards must reflect the student's own cohort,
        // not every teacher attached to the shared public course.
        $studentATeacherIds = app(\\App\\Services\\StudentDashboardService::class)
            ->build($studentA)['teachers']
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        $studentBTeacherIds = app(\\App\\Services\\StudentDashboardService::class)
            ->build($studentB)['teachers']
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $this->assertContains((int) $teacherA->id, $studentATeacherIds);
        $this->assertNotContains((int) $teacherB->id, $studentATeacherIds);
        $this->assertContains((int) $teacherB->id, $studentBTeacherIds);
        $this->assertNotContains((int) $teacherA->id, $studentBTeacherIds);

        $this->assertNotFound(
            $this->actingAs($studentA)
                ->get(route('student.assignments.show', $otherTeacherAssignment))
        );
        $this->assertNotFound(
            $this->actingAs($studentA)
                ->get(route('student.exams.show', $otherTeacherExam))
        );

        $crossGroupAssignment = Assignment::create([
            'course_id' => $course->id,
            'classroom_id' => $classB->id,
            'teacher_id' => $teacherA->id,
            'title' => 'تکلیف با کلاس متعلق به مدرس دیگر',
            'instructions' => 'نباید اجازه مدیریت داشته باشد.',
            'due_at' => now()->addDay(),
            'max_score' => 20,
            'status' => 'draft',
        ]);

        $this->assertFalse(
            app(TeacherAccessService::class)->canManageAssignment($teacherA, $crossGroupAssignment)
        );
    }

    private function moveStudentToClass(User $student, Course $course, Classroom $classroom): void
    {
        $courseClassroomIds = $course->classrooms()->pluck('classrooms.id');

        DB::table('classroom_student')
            ->where('student_id', $student->id)
            ->whereIn('classroom_id', $courseClassroomIds)
            ->update([
                'status' => 'archived',
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

        $classroom->students()->syncWithoutDetaching([
            $student->id => [
                'status' => 'active',
                'enrolled_at' => now(),
                'completed_at' => null,
            ],
        ]);

        CourseEnrollment::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $student->id,
            ],
            [
                'classroom_id' => $classroom->id,
                'academic_year_id' => $classroom->academic_year_id,
                'status' => 'active',
                'started_at' => now(),
            ],
        );
    }
}
