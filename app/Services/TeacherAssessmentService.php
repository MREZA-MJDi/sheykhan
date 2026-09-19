<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class TeacherAssessmentService
{
    public function gradeAssignment(
        Assignment $assignment,
        AssignmentSubmission $submission,
        float $score,
        ?string $feedback,
        int $teacherId
    ): AssignmentSubmission {
        if ($assignment->teacher_id !== $teacherId || $submission->assignment_id !== $assignment->id) {
            throw new AccessDeniedHttpException();
        }

        $submission->update([
            'score' => $score,
            'feedback' => $feedback,
            'graded_at' => now(),
            'graded_by' => $teacherId,
        ]);

        return $submission->refresh();
    }

    public function gradeExamAttemptAutomatically(ExamAttempt $attempt, int $teacherId): ExamAttempt
    {
        $attempt->loadMissing(['exam.questions', 'answers.question']);

        if ($attempt->exam?->teacher_id !== $teacherId) {
            throw new AccessDeniedHttpException();
        }

        return DB::transaction(function () use ($attempt): ExamAttempt {
            $total = 0.0;
            $requiresManualReview = false;

            foreach ($attempt->answers as $answer) {
                $question = $answer->question;

                if (!$question || $question->correct_answer === null) {
                    $requiresManualReview = true;
                    continue;
                }

                $correct = $this->matches($question->correct_answer, $answer->answer, $question->type);
                $score = $correct ? (float) $question->score : 0.0;

                $answer->update([
                    'is_correct' => $correct,
                    'score' => $score,
                ]);

                $total += $score;
            }

            $attempt->update([
                'score' => $total,
                'status' => $requiresManualReview ? 'needs_review' : 'graded',
            ]);

            return $attempt->refresh();
        });
    }

    public function gradeExamAttemptManually(
        ExamAttempt $attempt,
        array $scores,
        int $teacherId
    ): ExamAttempt {
        $attempt->loadMissing(['exam.questions', 'answers.question']);

        if ($attempt->exam?->teacher_id !== $teacherId) {
            throw new AccessDeniedHttpException();
        }

        return DB::transaction(function () use ($attempt, $scores): ExamAttempt {
            $total = 0.0;

            foreach ($attempt->answers as $answer) {
                if (!array_key_exists($answer->id, $scores) || $scores[$answer->id] === null || $scores[$answer->id] === '') {
                    $total += (float) ($answer->score ?? 0);
                    continue;
                }

                $question = $answer->question;
                $score = min(
                    (float) $scores[$answer->id],
                    (float) ($question?->score ?? $scores[$answer->id])
                );

                $answer->update([
                    'score' => $score,
                    'is_correct' => null,
                ]);

                $total += $score;
            }

            $attempt->update([
                'score' => $total,
                'status' => 'graded',
            ]);

            return $attempt->refresh();
        });
    }

    private function matches($expected, $actual, ?string $type): bool
    {
        if (in_array($type, ['multiple', 'checkbox'], true)) {
            $expected = array_values(array_unique((array) $expected));
            $actual = array_values(array_unique((array) $actual));

            sort($expected);
            sort($actual);

            return $expected === $actual;
        }

        return mb_strtolower(trim((string) $expected)) === mb_strtolower(trim((string) $actual));
    }
}
