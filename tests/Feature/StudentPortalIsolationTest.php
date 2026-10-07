<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_open_or_submit_another_students_assignment_context(): void
    {
        $this->seed();

        $owner = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $other = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();

        $assignment = Assignment::whereHas('submissions', fn ($query) => $query->where('student_id', $owner->id))
            ->firstOrFail();

        $this->actingAs($other)
            ->get(route('student.assignments.show', $assignment))
            ->assertNotFound();

        $this->actingAs($other)
            ->post(route('student.assignments.submit', $assignment), [
                'content' => 'attempted cross-user submission',
            ])
            ->assertNotFound();
    }

    public function test_student_cannot_open_another_students_exam_attempt(): void
    {
        $this->seed();

        $owner = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $other = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();

        $attempt = ExamAttempt::where('student_id', $owner->id)->firstOrFail();
        $exam = Exam::findOrFail($attempt->exam_id);

        $this->actingAs($other)
            ->get(route('student.exams.attempt', [
                'exam' => $exam,
                'attempt' => $attempt,
            ]))
            ->assertNotFound();
    }

    public function test_student_cannot_delete_another_students_note(): void
    {
        $this->seed();

        $owner = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $other = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();

        $note = LessonNote::firstOrCreate(
            ['lesson_id' => Lesson::query()->firstOrFail()->id, 'user_id' => $owner->id],
            ['content' => 'private note']
        );

        $this->actingAs($other)
            ->delete(route('student.notes.destroy', $note))
            ->assertNotFound();

        $this->assertDatabaseHas('lesson_notes', [
            'id' => $note->id,
            'user_id' => $owner->id,
        ]);
    }
}
