<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CourseEnrollmentService
{
    public function enrollFree(User $student, Course $course): CourseEnrollment
    {
        abort_unless($student->hasRole('student'), 403);
        abort_unless($course->isPublished() && $course->isFree(), 422);

        return CourseEnrollment::updateOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'active',
                'paid_amount' => 0,
                'started_at' => now(),
            ]
        );
    }

    public function grantPaidAccess(
        User $student,
        Course $course,
        float|int|string $paidAmount,
    ): CourseEnrollment {
        abort_unless($student->hasRole('student'), 403);
        abort_unless($course->isPublished() && $course->requiresPayment(), 422);

        return DB::transaction(function () use ($student, $course, $paidAmount): CourseEnrollment {
            return CourseEnrollment::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => 'active',
                    'paid_amount' => $paidAmount,
                    'started_at' => now(),
                ]
            );
        });
    }
}
