<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ParentPortalService
{
    /**
     * Children that this parent may contact through an active academy membership.
     *
     * @return Collection<int, User>
     */
    public function contactableChildren(User $parent): Collection
    {
        if (! $parent->hasRole('parent') || $parent->status !== 'active' || $parent->trashed()) {
            return collect();
        }

        $activeParentAcademyIds = fn () => DB::table('academy_user as parent_membership')
            ->join('academies', 'academies.id', '=', 'parent_membership.academy_id')
            ->where('parent_membership.user_id', $parent->id)
            ->where('parent_membership.role', 'parent')
            ->where('parent_membership.status', 'active')
            ->where('academies.status', 'active')
            ->select('parent_membership.academy_id');

        return $parent->children()
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->whereHas('roles', fn (Builder $roles) => $roles->where('roles.slug', 'student'))
            ->whereHas('academies', fn ($academies) => $academies
                ->whereIn('academies.id', $activeParentAcademyIds())
                ->where('academies.status', 'active')
                ->where('academy_user.role', 'student')
                ->where('academy_user.status', 'active'))
            ->with(['academies' => fn ($academies) => $academies
                ->whereIn('academies.id', $activeParentAcademyIds())
                ->where('academies.status', 'active')
                ->wherePivot('role', 'student')
                ->wherePivot('status', 'active')
                ->orderBy('academies.name')])
            ->orderBy('users.name')
            ->get();
    }

    /**
     * Teachers are selectable only when they teach an active classroom the
     * linked child is currently enrolled in and the parent is a member of
     * the same active academy.
     *
     * @return Collection<int, object>
     */
    public function contactableTeacherTargets(User $parent): Collection
    {
        return DB::table('parent_student as parent_link')
            ->join('users as students', 'students.id', '=', 'parent_link.student_id')
            ->join('academy_user as student_membership', function ($join): void {
                $join->on('student_membership.user_id', '=', 'students.id')
                    ->where('student_membership.role', '=', 'student')
                    ->where('student_membership.status', '=', 'active');
            })
            ->join('classroom_student as student_classroom_membership', function ($join): void {
                $join->on('student_classroom_membership.student_id', '=', 'students.id')
                    ->where('student_classroom_membership.status', '=', 'active');
            })
            ->join('classrooms', function ($join): void {
                $join->on('classrooms.id', '=', 'student_classroom_membership.classroom_id')
                    ->on('classrooms.academy_id', '=', 'student_membership.academy_id')
                    ->where('classrooms.status', '=', 'active');
            })
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'classrooms.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'classrooms.id')
            ->join('users as teachers', 'teachers.id', '=', 'teaching_assignment.teacher_id')
            ->join('academy_user as teacher_membership', function ($join): void {
                $join->on('teacher_membership.user_id', '=', 'teachers.id')
                    ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('teacher_membership.role', '=', 'teacher')
                    ->where('teacher_membership.status', '=', 'active');
            })
            ->join('academy_user as parent_membership', function ($join) use ($parent): void {
                $join->on('parent_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('parent_membership.user_id', '=', $parent->id)
                    ->where('parent_membership.role', '=', 'parent')
                    ->where('parent_membership.status', '=', 'active');
            })
            ->where('parent_link.parent_id', $parent->id)
            ->where('students.status', 'active')
            ->whereNull('students.deleted_at')
            ->where('teachers.status', 'active')
            ->whereNull('teachers.deleted_at')
            ->where('courses.status', 'published')
            ->whereExists(function ($enrollment): void {
                $enrollment->selectRaw('1')
                    ->from('course_enrollments as eligible_enrollments')
                    ->whereColumn('eligible_enrollments.student_id', 'students.id')
                    ->whereColumn('eligible_enrollments.course_id', 'courses.id')
                    ->where('eligible_enrollments.status', 'active')
                    ->where(function ($classroom): void {
                        $classroom->whereNull('eligible_enrollments.classroom_id')
                            ->orWhereColumn('eligible_enrollments.classroom_id', 'classrooms.id');
                    })
                    ->where(function ($access): void {
                        $access->where('courses.access_type', 'free')
                            ->orWhere(function ($paid): void {
                                $paid->where('eligible_enrollments.payment_status', 'paid')
                                    ->where('eligible_enrollments.paid_amount', '>', 0)
                                    ->where(function ($amount): void {
                                        $amount->whereNull('eligible_enrollments.price_amount')
                                            ->orWhereColumn('eligible_enrollments.paid_amount', '>=', 'eligible_enrollments.price_amount');
                                    })
                                    ->whereExists(function ($transaction): void {
                                        $transaction->selectRaw('1')
                                            ->from('financial_transactions')
                                            ->whereColumn('financial_transactions.enrollment_id', 'eligible_enrollments.id')
                                            ->where('financial_transactions.status', 'completed')
                                            ->where('financial_transactions.amount', '>', 0);
                                    });
                            });
                    });
            })
            ->select([
                'classrooms.id as classroom_id', 'classrooms.title as classroom_title',
                'classrooms.academy_id', 'courses.id as course_id', 'courses.title as course_title',
                'students.id as student_id', 'students.name as student_name',
                'teachers.id as teacher_id', 'teachers.name as teacher_name',
            ])
            ->distinct()
            ->orderBy('classrooms.title')
            ->orderBy('students.name')
            ->orderBy('teachers.name')
            ->get()
            ->map(fn (object $row): object => (object) [
                'key' => 'classroom:' . $row->classroom_id . ':' . $row->student_id . ':' . $row->teacher_id,
                'classroom_id' => (int) $row->classroom_id,
                'classroom_title' => $row->classroom_title,
                'academy_id' => (int) $row->academy_id,
                'course_id' => (int) $row->course_id,
                'course_title' => $row->course_title,
                'student_id' => (int) $row->student_id,
                'student_name' => $row->student_name,
                'teacher_id' => (int) $row->teacher_id,
                'teacher_name' => $row->teacher_name,
            ])
            ->unique('key')
            ->values();
    }

    /**
     * Resolve a child/academy target from the parent's own relationships.
     * Submitted IDs are never trusted on their own.
     *
     * @return array{0: User, 1: Academy}
     */
    public function resolveTarget(User $parent, int $studentId, int $academyId): array
    {
        $student = $parent->children()
            ->where('users.id', $studentId)
            ->where('users.status', 'active')
            ->whereHas('roles', fn (Builder $roles) => $roles->where('roles.slug', 'student'))
            ->firstOrFail();

        $academy = $student->academies()
            ->where('academies.id', $academyId)
            ->where('academies.status', 'active')
            ->wherePivot('role', 'student')
            ->wherePivot('status', 'active')
            ->firstOrFail();

        return [$student, $academy];
    }
}
