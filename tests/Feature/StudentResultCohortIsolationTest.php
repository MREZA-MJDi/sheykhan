<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Exam;
use App\Models\User;
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
}
