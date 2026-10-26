<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Media;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

final class ExamQuestionWriter
{
    /**
     * Preserve question identity on edits so existing answer records and prompt
     * attachments remain linked to the correct question after reordering.
     *
     * @param array<int, array<string, mixed>> $questions
     */
    public function storeQuestions(Exam $exam, array $questions, MediaService $media): void
    {
        $existingQuestions = $exam->questions()->get()->keyBy('id');
        $keptIds = [];
        $uploadedMedia = [];

        try {
            foreach (array_values($questions) as $index => $data) {
                $type = $data['type'] ?? 'text';
                $options = $type === 'text'
                    ? null
                    : array_values(array_filter(
                        (array) ($data['options'] ?? []),
                        static fn ($option) => is_string($option) && trim($option) !== ''
                    ));
                $correctAnswer = $type === 'text' ? null : ($data['correct_answer'] ?? null);

                if (is_array($correctAnswer)) {
                    $correctAnswer = json_encode(array_values($correctAnswer), JSON_UNESCAPED_UNICODE);
                }

                $payload = [
                    'type' => $type,
                    'question' => trim((string) ($data['question'] ?? '')),
                    'options' => $options,
                    'correct_answer' => $correctAnswer,
                    'expected_answer' => isset($data['expected_answer']) && is_string($data['expected_answer'])
                        ? trim($data['expected_answer'])
                        : null,
                    'score' => $data['score'] ?? 1,
                    'sort_order' => $index,
                ];

                $questionId = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
                if ($questionId) {
                    /** @var Question|null $question */
                    $question = $existingQuestions->get((int) $questionId);
                    if (! $question) {
                        throw ValidationException::withMessages([
                            "questions.{$index}.id" => 'شناسهٔ سؤال متعلق به همین آزمون نیست؛ برگه را تازه‌سازی و دوباره تلاش کن.',
                        ]);
                    }

                    $question->fill($payload)->save();
                } else {
                    $question = $exam->questions()->create($payload);
                }

                $keptIds[] = (int) $question->id;

                $uploadedFile = $data['attachment'] ?? null;
                if (! $uploadedFile instanceof UploadedFile) {
                    continue;
                }

                $previousFiles = $question->media()
                    ->wherePivot('collection', 'exam-question-prompt')
                    ->get();

                $newMedia = $media->upload($uploadedFile, $question, [
                    'collection' => 'exam-question-prompt',
                    'directory' => 'exams/' . $exam->id . '/questions/' . $question->id,
                    'visibility' => 'private',
                    'sort_order' => 0,
                ]);
                $uploadedMedia[] = $newMedia;

                foreach ($previousFiles as $previousFile) {
                    $media->detach($previousFile, $question);
                    if ($previousFile->attachments()->doesntExist()) {
                        $this->deleteAfterCommit($previousFile, $media);
                    }
                }
            }

            foreach ($existingQuestions as $question) {
                if (in_array((int) $question->id, $keptIds, true)) {
                    continue;
                }

                foreach ($question->media()->get() as $attachedMedia) {
                    $media->detach($attachedMedia, $question);
                    if ($attachedMedia->attachments()->doesntExist()) {
                        $this->deleteAfterCommit($attachedMedia, $media);
                    }
                }

                $question->delete();
            }
        } catch (Throwable $exception) {
            // Controller callers wrap this operation in a database transaction.
            // Delete stored bytes immediately; the transaction rollback removes
            // the corresponding Media rows and pivot records.
            foreach ($uploadedMedia as $uploaded) {
                try {
                    if (DB::transactionLevel() > 0) {
                        Storage::disk($uploaded->disk)->delete($uploaded->path);
                    } else {
                        $media->delete($uploaded);
                    }
                } catch (Throwable) {
                    // Best-effort cleanup must not hide the original exception.
                }
            }

            throw $exception;
        }
    }

    private function deleteAfterCommit(Media $mediaItem, MediaService $media): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(static function () use ($media, $mediaItem): void {
                if ($mediaItem->attachments()->doesntExist()) {
                    $media->delete($mediaItem);
                }
            });

            return;
        }

        if ($mediaItem->attachments()->doesntExist()) {
            $media->delete($mediaItem);
        }
    }
}
