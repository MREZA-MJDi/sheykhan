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
            ->whereExists(function ($group) use ($student): void {
                $group->selectRaw('1')
                    ->from('classroom_student as student_membership')
                    ->join('classrooms as student_classroom', 'student_classroom.id', '=', 'student_membership.classroom_id')
                    ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'student_classroom.id')
                    ->join('course_enrollments as student_enrollment', function ($join) use ($student): void {
                        $join->on('student_enrollment.classroom_id', '=', 'student_classroom.id')
                            ->on('student_enrollment.course_id', '=', 'student_classroom.course_id')
                            ->where('student_enrollment.student_id', '=', $student->id)
                            ->where('student_enrollment.status', '=', 'active');
                    })
                    ->join('courses as enrolled_courses', 'enrolled_courses.id', '=', 'student_enrollment.course_id')
                    ->where(function ($entitlement): void {
                        $entitlement->where('enrolled_courses.access_type', 'free')
                            ->orWhere(function ($paid): void {
                                $paid->where('student_enrollment.payment_status', 'paid')
                                    ->where('student_enrollment.paid_amount', '>', 0)
                                    ->where(function ($amount): void {
                                        $amount->whereNull('student_enrollment.price_amount')
                                            ->orWhereColumn('student_enrollment.paid_amount', '>=', 'student_enrollment.price_amount');
                                    })
                                    ->whereExists(function ($transaction): void {
                                        $transaction->selectRaw('1')
                                            ->from('financial_transactions')
                                            ->whereColumn('financial_transactions.enrollment_id', 'student_enrollment.id')
                                            ->where('financial_transactions.status', 'completed')
                                            ->where('financial_transactions.amount', '>', 0);
                                    });
                            });
                    })
                    ->whereColumn('student_classroom.course_id', 'assignments.course_id')
                    ->whereColumn('teaching_assignment.teacher_id', 'assignments.teacher_id')
                    ->where('student_classroom.status', 'active')
                    ->where('student_membership.student_id', $student->id)
                    ->where('student_membership.status', 'active')
                    ->where(function ($scope): void {
                        $scope->whereNull('assignments.classroom_id')
                            ->orWhereColumn('assignments.classroom_id', 'student_classroom.id');
                    });
            })
            ->with([
                'media:id,original_name,mime_type,size,status',
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
            'media:id,original_name,mime_type,size,status',
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
