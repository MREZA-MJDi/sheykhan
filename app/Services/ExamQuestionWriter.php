<?php

namespace App\Services;

use App\Models\Exam;

final class ExamQuestionWriter
{
    /** @param array<int, array<string, mixed>> $questions */
    public function storeQuestions(Exam $exam, array $questions): void
    {
        foreach (array_values($questions) as $index => $question) {
            $type = $question['type'] ?? 'text';
            $options = $type === 'text'
                ? null
                : array_values(array_filter((array) ($question['options'] ?? []), static fn ($option) => is_string($option) && trim($option) !== ''));
            $correctAnswer = $type === 'text' ? null : ($question['correct_answer'] ?? null);
            if (is_array($correctAnswer)) {
                $correctAnswer = json_encode(array_values($correctAnswer), JSON_UNESCAPED_UNICODE);
            }
            $exam->questions()->create([
                'type' => $type,
                'question' => $question['question'],
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'expected_answer' => isset($question['expected_answer']) && is_string($question['expected_answer'])
                    ? trim($question['expected_answer'])
                    : null,
                'score' => $question['score'] ?? 1,
                'sort_order' => $index,
            ]);
        }
    }
}
