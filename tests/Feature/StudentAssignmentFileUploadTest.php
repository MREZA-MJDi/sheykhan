<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use App\Services\StudentAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentAssignmentFileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_a_pdf_and_the_teacher_can_open_it_from_grading(): void
    {
        $this->seed();
        Storage::fake('local');

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $template = app(StudentAssignmentService::class)->query($student)->firstOrFail();
        $assignment = Assignment::query()->create([
            'course_id' => $template->course_id,
            'classroom_id' => $template->classroom_id,
            'teacher_id' => $template->teacher_id,
            'title' => 'PDF attachment submission test',
            'instructions' => 'Attach a PDF file as the assignment answer.',
            'due_at' => now()->addDays(2),
            'max_score' => 20,
            'status' => 'published',
        ]);

        $pdf = UploadedFile::fake()->create('homework-answer.pdf', 64, 'application/pdf');

        $this->actingAs($student)
            ->post(route('student.assignments.submit', $assignment), [
                'content' => 'پاسخ تکلیف در فایل PDF پیوست شده است.',
                'attachments' => [$pdf],
            ])
            ->assertRedirect(route('student.assignments.show', $assignment))
            ->assertSessionHas('success');

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $attachment = $submission->media()->firstOrFail();

        $this->assertSame('homework-answer.pdf', $attachment->original_name);
        $this->assertSame('application/pdf', $attachment->mime_type);
        $this->assertSame('private', $attachment->visibility);
        $this->assertSame('submission', $attachment->collection);
        Storage::disk('local')->assertExists($attachment->path);

        $this->actingAs($student)
            ->get(route('student.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('homework-answer.pdf')
            ->assertSee(route('media.view', $attachment), false);

        $teacher = User::query()->findOrFail($assignment->teacher_id);

        $this->actingAs($teacher)
            ->get(route('teacher.assignments.submissions', $assignment))
            ->assertOk()
            ->assertSee('teacher-submission-files', false)
            ->assertSee('homework-answer.pdf')
            ->assertSee(route('media.view', $attachment), false);

        $this->actingAs($teacher)
            ->get(route('media.view', $attachment))
            ->assertRedirect(route('protected-learning-media.view', $attachment));
    }
}
