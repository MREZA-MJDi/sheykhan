<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class StudentAssignmentService
{
    public function query(User $student): Builder
    {
        $courseIds = app(StudentAccessService::class)->enrolledCourseIds($student);

        return Assignment::query()
            ->whereIn('course_id', $courseIds)
            ->where('status', 'published')
            ->where(function (Builder $query) use ($student): void {
                $query->whereNull('classroom_id')
                    ->orWhereExists(function ($membership) use ($student): void {
                        $membership->selectRaw('1')
                            ->from('classroom_student')
                            ->whereColumn('classroom_student.classroom_id', 'assignments.classroom_id')
                            ->where('classroom_student.student_id', $student->id)
                            ->where('classroom_student.status', 'active');
                    });
            })
            ->with([
                'course:id,title,slug',
                'classroom:id,title,code',
                'submissions' => fn ($query) => $query
                    ->where('student_id', $student->id)
                    ->with(['media:id,original_name,mime_type,size,status']),
            ])
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->orderByDesc('id');
    }

    public function find(User $student, Assignment $assignment): Assignment
    {
        abort_unless(app(StudentAccessService::class)->assignment($student, $assignment), 404);

        return $assignment->load([
            'course:id,academy_id,title,slug',
            'classroom:id,academy_id,title,code,course_id',
            'submissions' => fn ($query) => $query
                ->where('student_id', $student->id)
                ->with(['media:id,original_name,mime_type,size,status'])
                ->latest('created_at'),
        ]);
    }

    public function submit(
        User $student,
        Assignment $assignment,
        array $data,
        array $files,
        MediaService $media,
    ): AssignmentSubmission {
        $uploaded = [];

        try {
            return DB::transaction(function () use ($student, $assignment, $data, $files, $media, &$uploaded): AssignmentSubmission {
                $assignment = $assignment->newQuery()->lockForUpdate()->findOrFail($assignment->id);

                abort_unless(app(StudentAccessService::class)->assignment($student, $assignment), 404);

                if ($assignment->status !== 'published') {
                    throw ValidationException::withMessages(['assignment' => 'این تکلیف دیگر قابل ارسال نیست.']);
                }

                if ($assignment->due_at && now()->gt($assignment->due_at)) {
                    throw ValidationException::withMessages(['assignment' => 'مهلت ارسال این تکلیف به پایان رسیده است.']);
                }

                $submission = AssignmentSubmission::query()->firstOrNew([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                ]);

                if ($submission->exists && $submission->graded_at) {
                    throw ValidationException::withMessages(['assignment' => 'این تکلیف قبلاً ارزیابی شده و امکان ارسال مجدد آن بسته شده است.']);
                }

                $submission->fill([
                    'content' => $data['content'] ?? null,
                    'submitted_at' => now(),
                ]);
                $submission->score = null;
                $submission->feedback = null;
                $submission->graded_at = null;
                $submission->graded_by = null;
                $submission->save();

                foreach ($files as $file) {
                    $uploaded[] = $media->upload($file, $submission, [
                        'collection' => 'submission',
                        'directory' => 'student/submissions',
                        'visibility' => 'private',
                    ]);
                }

                return $submission->load('media');
            });
        } catch (Throwable $exception) {
            foreach ($uploaded as $mediaItem) {
                $media->delete($mediaItem);
            }

            throw $exception;
        }
    }

    public function canSubmit(User $student, Assignment $assignment): bool
    {
        return app(StudentAccessService::class)->assignment($student, $assignment)
            && $assignment->status === 'published'
            && (!$assignment->due_at || now()->lte($assignment->due_at))
            && !AssignmentSubmission::query()
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->whereNotNull('graded_at')
                ->exists();
    }
}
