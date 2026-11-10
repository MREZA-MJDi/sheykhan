<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Exam;
use App\Models\QuestionBankItem;
use App\Models\User;
use App\Services\ExamQuestionWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QuestionBankSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_questions_selected_from_bank_are_snapshotted_into_exam_and_do_not_change_later(): void
    {
        $owner = User::factory()->create(['status' => 'active']);
        $teacher = User::factory()->create(['status' => 'active']);
        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آموزشگاه بانک سؤال',
            'slug' => 'question-bank-' . uniqid(),
            'status' => 'active',
        ]);
        $course = $academy->courses()->create([
            'created_by' => $owner->id,
            'title' => 'دوره آزمون',
            'slug' => 'exam-course-' . uniqid(),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);
        $exam = Exam::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'آزمون نسخه‌دار',
            'duration_minutes' => 30,
            'attempts_allowed' => 1,
            'status' => 'draft',
        ]);
        $bankQuestion = QuestionBankItem::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'type' => 'single',
            'question' => 'دو به‌علاوه دو؟',
            'options' => ['سه', 'چهار', 'پنج', 'شش'],
            'correct_answers' => ['چهار'],
            'expected_answer' => 'چهار',
            'default_score' => 2,
            'topic' => 'ریاضی',
            'difficulty' => 'easy',
            'status' => 'active',
        ]);

        app(ExamQuestionWriter::class)->storeBankQuestions($exam, collect([$bankQuestion]));

        $snapshot = $exam->questions()->firstOrFail();
        $this->assertSame($bankQuestion->id, $snapshot->source_bank_question_id);
        $this->assertSame('دو به‌علاوه دو؟', $snapshot->question);
        $this->assertSame(['سه', 'چهار', 'پنج', 'شش'], $snapshot->options);
        $this->assertSame('چهار', $snapshot->correct_answer);

        $bankQuestion->update([
            'question' => 'نسخه جدید سؤال',
            'correct_answers' => ['پنج'],
            'default_score' => 5,
        ]);

        $this->assertSame('دو به‌علاوه دو؟', $snapshot->fresh()->question);
        $this->assertSame('چهار', $snapshot->fresh()->correct_answer);
        $this->assertSame('5.00', $bankQuestion->fresh()->default_score);
    }
}
