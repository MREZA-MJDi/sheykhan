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

        if ($submission->submitted_at === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'score' => 'پاسخی که هنوز ارسال نشده است قابل تصحیح نیست.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $submission, $score, $feedback, $teacherId): AssignmentSubmission {
            $lockedSubmission = AssignmentSubmission::query()
                ->whereKey($submission->id)
                ->where('assignment_id', $assignment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedSubmission->update([
                'score' => $score,
                'feedback' => $feedback,
                'graded_at' => now(),
                'graded_by' => $teacherId,
            ]);

            return $lockedSubmission->refresh();
        });
    }

    public function gradeExamAttemptAutomatically(ExamAttempt $attempt, int $teacherId): ExamAttempt
    {
        $attempt->loadMissing(['exam:id,course_id,classroom_id,teacher_id', 'answers.question']);

        if ((int) $attempt->exam?->teacher_id !== $teacherId) {
            throw new AccessDeniedHttpException();
        }

        return DB::transaction(function () use ($attempt): ExamAttempt {
            $attempt = ExamAttempt::query()
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->with(['exam.questions', 'answers.question'])
                ->firstOrFail();

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
        $attempt->loadMissing(['exam:id,course_id,classroom_id,teacher_id', 'answers.question']);

        if ((int) $attempt->exam?->teacher_id !== $teacherId) {
            throw new AccessDeniedHttpException();
        }

        return DB::transaction(function () use ($attempt, $scores): ExamAttempt {
            $attempt = ExamAttempt::query()
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->with(['exam.questions', 'answers.question'])
                ->firstOrFail();

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
            $expected = $this->normalizeChoiceList($expected);
            $actual = $this->normalizeChoiceList($actual);

            return $expected === $actual;
        }

        return mb_strtolower(trim((string) $expected)) === mb_strtolower(trim((string) $actual));
    }

    private function normalizeChoiceList(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = preg_split('/\s*[,،]\s*/u', $value, -1, PREG_SPLIT_NO_EMPTY);
            }
        }

        $value = is_array($value) ? $value : [$value];
        $value = array_map(
            static fn ($item) => trim((string) $item),
            $value
        );
        $value = array_values(array_unique(array_filter($value, static fn ($item) => $item !== '')));
        sort($value);

        return $value;
    }
}
