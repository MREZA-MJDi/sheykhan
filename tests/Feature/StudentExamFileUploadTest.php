<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;
use App\Services\StudentAssignmentService;
use App\Services\StudentExamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentExamFileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_a_private_file_with_exam_answers_and_teacher_can_review_it(): void
    {
        $this->seed();
        Storage::fake('local');

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $courseAssignment = app(StudentAssignmentService::class)->query($student)->firstOrFail();
        $teacher = User::query()->findOrFail($courseAssignment->teacher_id);

        $exam = Exam::query()->create([
            'course_id' => $courseAssignment->course_id,
            'classroom_id' => $courseAssignment->classroom_id,
            'teacher_id' => $teacher->id,
            'title' => 'آزمون همراه فایل پاسخ',
            'description' => 'فایل پاسخ را همراه پاسخ‌های آزمون ارسال کن.',
            'duration_minutes' => 60,
            'attempts_allowed' => 1,
            'status' => 'published',
        ]);

        $question = Question::query()->create([
            'exam_id' => $exam->id,
            'type' => 'text',
            'question' => 'پاسخ تشریحی را بنویس.',
            'options' => [],
            'correct_answer' => null,
            'expected_answer' => 'پاسخ مرجع',
            'score' => 10,
            'sort_order' => 1,
        ]);

        $attempt = app(StudentExamService::class)->start($student, $exam);
        $pdf = UploadedFile::fake()->create('exam-answer.pdf', 96, 'application/pdf');

        $this->actingAs($student)
            ->post(route('student.exam-attempts.submit', $attempt), [
                'answers' => [$question->id => 'پاسخ تشریحی دانش‌آموز'],
                'attachments' => [$pdf],
            ])
            ->assertRedirect(route('student.exams.index'))
            ->assertSessionHas('success');

        $attempt = ExamAttempt::query()->findOrFail($attempt->id);
        $answerFile = $attempt->media()->wherePivot('collection', 'exam-answer')->firstOrFail();

        $this->assertNotNull($attempt->submitted_at);
        $this->assertSame('private', $answerFile->visibility);
        $this->assertSame('exam-answer', $answerFile->pivot->collection);
        Storage::disk('local')->assertExists($answerFile->path);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.attempts', $exam))
            ->assertOk()
            ->assertSee('exam-answer.pdf')
            ->assertSee(route('media.view', $answerFile), false);
    }
}
