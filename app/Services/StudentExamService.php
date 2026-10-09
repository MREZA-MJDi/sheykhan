<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StudentExamService
{
    public function query(User $student): Builder
    {
        $courseIds = app(StudentAccessService::class)->enrolledCourseIds($student);

        return Exam::query()
            ->whereIn('course_id', $courseIds)
            ->where('status', 'published')
            ->where(function (Builder $query) use ($student): void {
                $query->whereNull('classroom_id')
                    ->orWhereExists(function ($membership) use ($student): void {
                        $membership->selectRaw('1')
                            ->from('classroom_student')
                            ->whereColumn('classroom_student.classroom_id', 'exams.classroom_id')
                            ->where('classroom_student.student_id', $student->id)
                            ->where('classroom_student.status', 'active');
                    });
            })
            ->with([
                'course:id,title,slug',
                'classroom:id,title,code',
                'questions:id,exam_id,type,question,options,score,sort_order',
            ])
            ->orderByRaw('CASE WHEN starts_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('starts_at')
            ->orderBy('id');
    }

    public function find(User $student, Exam $exam): Exam
    {
        abort_unless(app(StudentAccessService::class)->exam($student, $exam), 404);

        return $exam->load([
            'course:id,academy_id,title,slug',
            'classroom:id,academy_id,title,code,course_id',
            'questions:id,exam_id,type,question,options,score,sort_order',
            'attempts' => fn ($query) => $query->where('student_id', $student->id)->latest('attempt_number'),
        ]);
    }

    public function start(User $student, Exam $exam): ExamAttempt
    {
        return DB::transaction(function () use ($student, $exam): ExamAttempt {
            $exam = $exam->newQuery()->lockForUpdate()->findOrFail($exam->id);
            abort_unless(app(StudentAccessService::class)->exam($student, $exam), 404);

            $existing = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->latest('attempt_number')
                ->first();

            if ($existing) {
                if ($this->hasTimedOut($existing, $exam)) {
                    $existing->status = 'submitted';
                    $existing->submitted_at = now();
                    $existing->save();
                } else {
                    return $existing;
                }
            }

            if (!$this->isOpen($exam)) {
                throw ValidationException::withMessages(['exam' => 'این آزمون در حال حاضر باز نیست.']);
            }

            $completedAttempts = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['graded', 'submitted', 'pending_review'])
                ->count();

            if ($completedAttempts >= max(1, (int) $exam->attempts_allowed)) {
                throw ValidationException::withMessages(['exam' => 'تعداد دفعات مجاز این آزمون برای شما به پایان رسیده است.']);
            }

            return ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'attempt_number' => $completedAttempts + 1,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
        });
    }

    public function submit(User $student, ExamAttempt $attempt, array $answers): ExamAttempt
    {
        $result = DB::transaction(function () use ($student, $attempt, $answers): array {
            $attempt = ExamAttempt::query()
                ->with(['exam.questions'])
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            abort_unless($attempt->student_id === $student->id, 404);
            abort_unless($attempt->status === 'in_progress', 422);

            $exam = $attempt->exam;
            abort_unless(app(StudentAccessService::class)->exam($student, $exam), 404);

            $timedOut = $this->hasTimedOut($attempt, $exam);
            $examIsOpen = $this->isOpen($exam);

            if (!$examIsOpen || $timedOut) {
                $attempt->status = 'submitted';
                $attempt->submitted_at = now();
                $attempt->save();

                // Commit the final state before surfacing a validation error.
                // Throwing inside the transaction would roll this state change back.
                $message = $exam->status !== 'published'
                    ? 'این آزمون توسط مدرس بسته شده است. پاسخ‌ها ثبت نشدند.'
                    : ($timedOut
                        ? 'زمان آزمون شما به پایان رسیده است. پاسخ‌ها ثبت نشدند.'
                        : 'این آزمون در حال حاضر باز نیست.');

                return ['failure' => $message, 'attempt' => $attempt];
            }

            $questionById = $exam->questions->keyBy('id');
            $totalScore = 0.0;
            $hasManualQuestions = false;

            foreach ($answers as $questionId => $answer) {
                $question = $questionById->get((int) $questionId);

                if (!$question) {
                    continue;
                }

                [$isCorrect, $earned] = $this->gradeQuestion($question, $answer);
                $hasManualQuestions = $hasManualQuestions || $question->type === 'text';

                $attempt->answers()->updateOrCreate(
                    ['question_id' => $question->id],
                    [
                        'answer' => $this->serializeAnswer($answer),
                        'is_correct' => $isCorrect,
                        'score' => $earned,
                    ],
                );

                if ($earned !== null) {
                    $totalScore += $earned;
                }
            }

            foreach ($exam->questions as $question) {
                if (!$attempt->answers()->where('question_id', $question->id)->exists()) {
                    $attempt->answers()->create([
                        'question_id' => $question->id,
                        'answer' => null,
                        'is_correct' => false,
                        'score' => 0,
                    ]);
                }

                $hasManualQuestions = $hasManualQuestions || $question->type === 'text';
            }

            $attempt->score = $hasManualQuestions ? null : round($totalScore, 2);
            $attempt->status = $hasManualQuestions ? 'pending_review' : 'graded';
            $attempt->submitted_at = now();
            $attempt->save();

            return [
                'failure' => null,
                'attempt' => $attempt->load(['exam.questions', 'answers']),
            ];
        });

        if ($result['failure'] !== null) {
            throw ValidationException::withMessages([
                'exam' => $result['failure'],
            ]);
        }

        return $result['attempt'];
    }

    public function isOpen(Exam $exam): bool
    {
        if ($exam->status !== 'published') {
            return false;
        }

        $now = now();

        return (!$exam->starts_at || $now->gte($exam->starts_at))
            && (!$exam->ends_at || $now->lte($exam->ends_at));
    }

    private function hasTimedOut(ExamAttempt $attempt, Exam $exam): bool
    {
        if ($exam->ends_at && now()->gte($exam->ends_at)) {
            return true;
        }

        return (int) $exam->duration_minutes > 0
            && now()->gte($attempt->started_at->copy()->addMinutes($exam->duration_minutes));
    }

    private function gradeQuestion(Question $question, mixed $answer): array
    {
        if ($question->type === 'text') {
            return [null, null];
        }

        $expected = $this->normalizeComparable($question->correct_answer, $question->type);
        $actual = $this->normalizeComparable($answer, $question->type);
        $correct = $expected !== null && $expected === $actual;

        return [$correct, $correct ? (float) $question->score : 0.0];
    }

    private function normalizeComparable(mixed $value, string $type): mixed
    {
        if (in_array($type, ['multiple', 'checkbox'], true)) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $decoded : preg_split('/\s*,\s*/', $value);
            }

            $value = array_map('strval', is_array($value) ? $value : [$value]);
            sort($value);

            return array_values(array_unique($value));
        }

        if (is_array($value)) {
            $value = reset($value);
        }

        return $value === null ? null : trim((string) $value);
    }

    private function serializeAnswer(mixed $answer): ?string
    {
        if ($answer === null || $answer === '') {
            return null;
        }

        return is_array($answer) ? json_encode(array_values($answer), JSON_UNESCAPED_UNICODE) : (string) $answer;
    }
}
