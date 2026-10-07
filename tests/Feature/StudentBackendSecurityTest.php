<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonNote;
use App\Models\LiveClass;
use App\Models\Question;
use App\Models\User;
use App\Services\MediaService;
use App\Services\CourseAccessService;
use App\Services\StudentAccessService;
use App\Services\StudentAssignmentService;
use App\Services\StudentCourseService;
use App\Services\StudentExamService;
use App\Services\StudentLearningResourceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentBackendSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_route_contract_is_complete(): void
    {
        $this->seed();

        foreach ([
            'student.dashboard',
            'student.courses.index',
            'student.courses.show',
            'student.lessons.show',
            'student.lessons.progress.update',
            'student.assignments.index',
            'student.assignments.show',
            'student.assignments.submit',
            'student.exams.index',
            'student.exams.show',
            'student.exams.start',
            'student.exams.attempt',
            'student.exam-attempts.submit',
            'student.exams.result',
            'student.live-classes.index',
            'student.live-classes.join',
            'student.attendance.index',
            'student.results.index',
            'student.achievements.index',
            'student.notes.index',
            'student.lessons.notes.store',
            'student.notes.destroy',
            'student.profile.edit',
            'student.profile.update',
            'student.profile.password.update',
            'student.resources.index',
            'student.resources.view',
            'student.resources.download',
        ] as $name) {
            $this->assertTrue(Route::has($name), "Missing Student route: {$name}");
        }

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $this->assertTrue($student->hasPermission('achievements.view'));
        $this->assertTrue($student->hasPermission('media.view'));
        $this->assertFalse($student->hasPermission('media.download'));
    }

    public function test_paid_course_access_requires_a_real_payment_and_does_not_leak_into_any_student_service(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $course->update(['access_type' => 'paid']);

        $student->enrollments()
            ->where('course_id', $course->id)
            ->update(['paid_amount' => 0]);

        $this->assertFalse(app(StudentAccessService::class)->course($student, $course));
        $this->assertFalse(app(CourseAccessService::class)->canDownload($student, $course));
        $this->assertNotContains($course->id, app(StudentAccessService::class)->enrolledCourseIds($student)->all());

        $this->assertFalse(app(StudentCourseService::class)->query($student)->whereKey($course->id)->exists());
    }

    public function test_paid_course_is_not_leaked_into_student_dashboard(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $course->update(['access_type' => 'paid']);
        $student->enrollments()
            ->where('course_id', $course->id)
            ->update(['paid_amount' => 0]);

        $payload = app(\App\Services\StudentDashboardService::class)->build($student);

        $this->assertNotContains($course->id, $payload['courses']->pluck('id')->all());
        $this->assertSame(0, $payload['activeCourseCount']);
    }

    public function test_assignment_submission_is_student_owned_classroom_scoped_and_attachment_safe(): void
    {
        Storage::fake('local');
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherStudent = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();
        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تمرین امنیتی دانش‌آموز',
            'instructions' => 'پاسخ دهید.',
            'due_at' => now()->addDay(),
            'max_score' => 20,
            'status' => 'published',
        ]);

        app(StudentAccessService::class)->course($student, $course)
            ?: $student->enrollments()->where('course_id', $course->id)->update(['paid_amount' => max(1, (int) $course->price)]);

        $this->assertTrue(app(StudentAccessService::class)->assignment($student, $assignment));
        $this->assertFalse(app(StudentAccessService::class)->assignment($otherStudent, $assignment));

        $this->actingAs($student);

        $submission = app(StudentAssignmentService::class)->submit(
            $student,
            $assignment,
            ['content' => 'پاسخ تست'],
            [UploadedFile::fake()->create('answer.pdf', 20, 'application/pdf')],
            app(MediaService::class),
        );

        $this->assertInstanceOf(AssignmentSubmission::class, $submission);
        $this->assertSame($student->id, $submission->student_id);
        $this->assertCount(1, $submission->fresh()->media);

        $submission->update(['graded_at' => now(), 'score' => 18, 'graded_by' => $teacher->id]);

        $this->expectException(ValidationException::class);

        app(StudentAssignmentService::class)->submit(
            $student,
            $assignment,
            ['content' => 'نباید دوباره ارسال شود'],
            [],
            app(MediaService::class),
        );
    }

    public function test_assignment_cannot_be_submitted_after_deadline(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'classroom_id' => null,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف منقضی',
            'instructions' => '—',
            'due_at' => now()->subMinute(),
            'max_score' => 10,
            'status' => 'published',
        ]);

        $student->enrollments()->where('course_id', $course->id)->update(['paid_amount' => max(1, (int) $course->price)]);

        $this->expectException(ValidationException::class);

        app(StudentAssignmentService::class)->submit(
            $student,
            $assignment,
            ['content' => 'late'],
            [],
            app(MediaService::class),
        );
    }

    public function test_exam_is_server_graded_hides_answer_key_limits_attempts_and_handles_timeout(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $course->update(['access_type' => 'free']);
        $student->enrollments()->where('course_id', $course->id)->update(['paid_amount' => 0]);

        $exam = Exam::create([
            'course_id' => $course->id,
            'classroom_id' => null,
            'teacher_id' => User::where('email', 'teacher.math@sheykhan.test')->value('id'),
            'title' => 'آزمون تست دانش‌آموز',
            'description' => '—',
            'duration_minutes' => 30,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'attempts_allowed' => 1,
            'status' => 'published',
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'type' => 'single',
            'question' => 'گزینه صحیح؟',
            'options' => ['a' => 'الف', 'b' => 'ب'],
            'correct_answer' => 'b',
            'score' => 10,
            'sort_order' => 1,
        ]);

        $service = app(StudentExamService::class);
        $attempt = $service->start($student, $exam);

        $found = $service->find($student, $exam);
        $questionArray = $found->questions->first()->toArray();

        $this->assertArrayNotHasKey('correct_answer', $questionArray);

        $submitted = $service->submit($student, $attempt, [$question->id => 'b']);

        $this->assertSame('graded', $submitted->status);
        $this->assertSame(10.0, (float) $submitted->score);
        $this->assertSame(10.0, (float) ExamAnswer::where('exam_attempt_id', $attempt->id)->value('score'));

        $this->expectException(ValidationException::class);
        $service->start($student, $exam);
    }

    public function test_timed_out_exam_attempt_is_closed_before_a_new_attempt_is_allowed(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $course->update(['access_type' => 'free']);
        $student->enrollments()->where('course_id', $course->id)->update(['paid_amount' => 0]);

        $exam = Exam::create([
            'course_id' => $course->id,
            'classroom_id' => null,
            'teacher_id' => User::where('email', 'teacher.math@sheykhan.test')->value('id'),
            'title' => 'آزمون زمان‌دار',
            'description' => '—',
            'duration_minutes' => 20,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'attempts_allowed' => 2,
            'status' => 'published',
        ]);

        $old = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(30),
            'status' => 'in_progress',
        ]);

        $new = app(StudentExamService::class)->start($student, $exam);

        $this->assertSame('submitted', $old->fresh()->status);
        $this->assertSame(2, $new->attempt_number);
    }

    public function test_student_cannot_join_a_live_class_without_scope_or_without_a_meeting_url(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherStudent = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        $student->enrollments()->where('course_id', $course->id)->update(['paid_amount' => max(1, (int) $course->price)]);
        $otherStudent->enrollments()->updateOrCreate(
            ['course_id' => $course->id],
            ['status' => 'active', 'paid_amount' => max(1, (int) $course->price), 'started_at' => now()],
        );

        $live = LiveClass::create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => User::where('email', 'teacher.math@sheykhan.test')->value('id'),
            'title' => 'کلاس زنده تست',
            'provider' => 'test',
            'meeting_url' => 'https://meet.example.test/student',
            'scheduled_at' => now()->subMinutes(5),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        $noLink = $live->replicate();
        $noLink->meeting_url = null;
        $noLink->save();

        $access = app(StudentAccessService::class);

        $this->assertTrue($access->canJoinLiveClass($student, $live));
        $this->assertFalse($access->canJoinLiveClass($otherStudent, $live));
        $this->assertFalse($access->canJoinLiveClass($student, $noLink));
    }

    public function test_lesson_progress_can_only_move_forward_and_only_for_an_accessible_lesson(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherStudent = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->orderBy('sort_order')->skip(1)->firstOrFail();

        $student->enrollments()->where('course_id', $course->id)->update(['paid_amount' => max(1, (int) $course->price)]);
        $otherStudent->enrollments()->where('course_id', $course->id)->update(['paid_amount' => max(1, (int) $course->price)]);

        $this->actingAs($student)
            ->patchJson(route('student.lessons.progress.update', $lesson), [
                'progress_percent' => 80,
                'seconds_watched' => 120,
            ])
            ->assertOk()
            ->assertJsonPath('completed', false);

        $this->actingAs($student)
            ->patchJson(route('student.lessons.progress.update', $lesson), [
                'progress_percent' => 20,
                'seconds_watched' => 60,
            ])
            ->assertOk()
            ->assertJsonPath('progress_percent', 80)
            ->assertJsonPath('seconds_watched', 120);

        $this->assertDatabaseHas('lesson_progress', [
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
        ]);

        $progress = LessonProgress::query()->where('lesson_id', $lesson->id)->where('user_id', $student->id)->firstOrFail();
        $this->assertSame(80.0, (float) $progress->progress_percent);
        $this->assertSame(120, (int) $progress->seconds_watched);

        $this->assertNotEquals(
            200,
            $this->actingAs($otherStudent)->get(route('student.lessons.show', $lesson))->status()
        );
    }

    public function test_student_note_is_owner_scoped(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherStudent = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->firstOrFail();

        $student->enrollments()->where('course_id', $course->id)->update(['paid_amount' => max(1, (int) $course->price)]);

        $note = LessonNote::create([
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'content' => 'یادداشت خصوصی',
        ]);

        $this->actingAs($otherStudent)
            ->delete(route('student.notes.destroy', $note))
            ->assertNotFound();

        $this->assertDatabaseHas('lesson_notes', ['id' => $note->id]);
    }

    public function test_student_password_change_requires_current_password(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->patch(route('student.profile.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($student)
            ->patch(route('student.profile.password.update'), [
                'current_password' => 'Sheykhan@12345',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('student.profile.edit'));

        $this->assertTrue(Hash::check('new-password-123', $student->fresh()->password));
    }
}
