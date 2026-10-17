<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Services\CourseLearningProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherLearningProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_only_view_progress_for_assigned_courses(): void
    {
        $this->seed();

        $teacherOne = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $teacherTwo = User::where('email', 'teacher.science@sheykhan.test')->firstOrFail();

        $teacherOneCourse = Course::whereHas('teachers', fn ($query) => $query->whereKey($teacherOne->id))
            ->firstOrFail();

        $teacherTwoCourse = Course::whereHas('teachers', fn ($query) => $query->whereKey($teacherTwo->id))
            ->whereDoesntHave('teachers', fn ($query) => $query->whereKey($teacherOne->id))
            ->firstOrFail();

        $this->actingAs($teacherOne)
            ->get(route('teacher.courses.progress', $teacherOneCourse))
            ->assertOk();

        $this->actingAs($teacherOne)
            ->get(route('teacher.courses.progress', $teacherTwoCourse))
            ->assertForbidden();
    }

    public function test_same_course_teachers_only_see_students_in_their_assigned_classroom_cohort(): void
    {
        $this->seed();

        $teacherOne = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $teacherTwo = User::where('email', 'teacher.science@sheykhan.test')->firstOrFail();
        $studentOne = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $studentTwo = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();
        $owner = User::where('email', 'owner@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        // This test isolates roster privacy, not the paid-enrollment transaction gate.
        $course->update(['access_type' => 'free', 'price' => 0]);
        $teacherOneClassroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        // Both teachers are assigned to the same public course, but each teaches
        // a separate cohort. The course-teacher pivot alone must not grant access
        // to the other teacher's classroom roster.
        $course->teachers()->syncWithoutDetaching([
            $teacherTwo->id => ['is_primary' => false],
        ]);

        $teacherTwoClassroom = $teacherOneClassroom->replicate();
        $teacherTwoClassroom->code = 'MATH7-TEACHER2';
        $teacherTwoClassroom->title = 'کلاس ریاضی - گروه مدرس دوم';
        $teacherTwoClassroom->save();
        $teacherTwoClassroom->teachers()->sync([$teacherTwo->id]);

        $teacherTwoClassroom->students()->syncWithoutDetaching([
            $studentTwo->id => [
                'status' => 'active',
                'enrolled_at' => now(),
                'completed_at' => null,
            ],
        ]);

        CourseEnrollment::updateOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $studentTwo->id,
                'academic_year_id' => $teacherOneClassroom->academic_year_id,
            ],
            [
                'classroom_id' => $teacherTwoClassroom->id,
                'registered_by' => $owner->id,
                'registration_source' => 'admin',
                'status' => 'active',
                'payment_status' => 'paid',
                'price_amount' => $course->price,
                'paid_amount' => $course->price,
                'started_at' => now(),
                'completed_at' => null,
            ],
        );

        $report = app(CourseLearningProgressService::class);

        $teacherOneStudentIds = $report->forTeacher($teacherOne, $course)['students']
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $teacherTwoStudentIds = $report->forTeacher($teacherTwo, $course)['students']
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertContains($studentOne->id, $teacherOneStudentIds);
        $this->assertNotContains($studentTwo->id, $teacherOneStudentIds);

        $this->assertContains($studentTwo->id, $teacherTwoStudentIds);
        $this->assertNotContains($studentOne->id, $teacherTwoStudentIds);
    }

}
