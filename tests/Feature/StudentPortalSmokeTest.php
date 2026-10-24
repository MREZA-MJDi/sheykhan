<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_render_the_portal_entry_pages_without_cross_role_redirects(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->with('sections.lessons')->firstOrFail();
        $lesson = $course->sections->flatMap(fn ($section) => $section->lessons)->firstOrFail();
        $assignment = Assignment::where('course_id', $course->id)->firstOrFail();
        $exam = Exam::where('course_id', $course->id)->firstOrFail();

        $routes = [
            ['student.dashboard', []],
            ['student.courses.index', []],
            ['student.courses.show', [$course]],
            ['student.lessons.show', [$lesson]],
            ['student.assignments.index', []],
            ['student.assignments.show', [$assignment]],
            ['student.exams.index', []],
            ['student.exams.show', [$exam]],
            ['student.live-classes.index', []],
            ['student.attendance.index', []],
            ['student.results.index', []],
            ['student.achievements.index', []],
            ['student.notes.index', []],
            ['student.profile.edit', []],
            ['student.resources.index', []],
        ];

        foreach ($routes as [$routeName, $parameters]) {
            $this->actingAs($student)
                ->get(route($routeName, $parameters))
                ->assertOk();
        }

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('خانه یادگیری من')
            ->assertSee('دوره‌های فعال')
            ->assertSee('student-shell', false)
            ->assertDontSee('رابط کاربری جدید بخش دانش‌آموز در حال آماده‌سازی است.')
            ->assertDontSee('sdr-hero', false);
    }

    public function test_completed_student_exam_attempt_redirects_to_results_instead_of_returning_a_404(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $exam = Exam::where('course_id', $course->id)->firstOrFail();

        // Historical results stay available even after the teacher closes the exam.
        $exam->status = 'draft';
        $exam->save();

        $attempt = ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(10),
            'submitted_at' => now()->subMinutes(1),
            'score' => 8,
            'status' => 'graded',
        ]);

        $this->actingAs($student)
            ->get(route('student.exams.attempt', ['exam' => $exam->id, 'attempt' => $attempt->id]))
            ->assertRedirect(route('student.exams.result', ['exam' => $exam->id]));

        $this->actingAs($student)
            ->get(route('student.exams.result', ['exam' => $exam->id]))
            ->assertOk()
            ->assertSee('تصحیح‌شده');
    }

    public function test_only_the_owner_can_open_an_exam_attempt(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $exam = Exam::where('course_id', $course->id)->firstOrFail();

        $attempt = ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $teacher->id,
            'attempt_number' => 1,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($student)
            ->get(route('student.exams.attempt', ['exam' => $exam->id, 'attempt' => $attempt->id]))
            ->assertNotFound();
    }


    public function test_student_can_render_an_in_progress_exam_attempt(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $exam = Exam::where('course_id', $course->id)->firstOrFail();

        $attempt = ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($student)
            ->get(route('student.exams.attempt', ['exam' => $exam->id, 'attempt' => $attempt->id]))
            ->assertOk()
            ->assertSee('exam-form', false)
            ->assertSee($exam->title);
    }

}
