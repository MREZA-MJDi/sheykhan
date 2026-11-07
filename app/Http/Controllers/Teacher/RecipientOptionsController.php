<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RecipientOptionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $teacher = $request->user();

        abort_unless($teacher && $teacher->hasRole('teacher') && $teacher->hasPermission('parent_messages.manage'), 403);

        $filters = $request->validate([
            'context' => ['required', 'string', 'in:announcement,direct'],
            'type' => ['required', 'string', 'in:students,parents,classrooms'],
            'q' => ['required', 'string', 'min:2', 'max:80'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ]);

        abort_if($filters['context'] === 'direct' && $filters['type'] === 'classrooms', 422);

        $like = trim($filters['q']) . '%';
        $query = match ($filters['type']) {
            'students' => $this->studentsQuery((int) $teacher->id, $like),
            'parents' => $this->parentsQuery((int) $teacher->id, $like),
            'classrooms' => $this->classroomsQuery((int) $teacher->id, $like),
        };

        $paginator = $query->simplePaginate(20)->appends($filters);
        $data = $paginator->getCollection()->map(function (object $row) use ($filters): array {
            return match ($filters['type']) {
                'students' => [
                    'value' => 'student:' . $row->classroom_id . ':' . $row->student_id,
                    'label' => 'دانش‌آموز: ' . $row->student_name . ' · ' . $row->classroom_title,
                ],
                'parents' => [
                    'value' => 'parent:' . $row->classroom_id . ':' . $row->student_id . ':' . $row->parent_id,
                    'label' => 'ولی: ' . $row->parent_name . ' · فرزند: ' . $row->student_name . ' · ' . $row->classroom_title,
                ],
                default => [
                    'value' => 'classroom:' . $row->classroom_id . ':parents',
                    'label' => 'خانواده‌های کلاس: ' . $row->classroom_title . ' · ' . $row->course_title,
                ],
            };
        })->values();

        return response()->json([
            'data' => $data,
            'has_more' => $paginator->hasMorePages(),
        ])->header('Cache-Control', 'private, no-store');
    }

    private function studentsQuery(int $teacherId, string $like)
    {
        return $this->activeRosterQuery($teacherId)
            ->where('students.name', 'like', $like)
            ->select([
                'classrooms.id as classroom_id',
                'classrooms.title as classroom_title',
                'students.id as student_id',
                'students.name as student_name',
            ])
            ->orderBy('classrooms.title')
            ->orderBy('students.name')
            ->orderBy('students.id')
            ->distinct();
    }

    private function parentsQuery(int $teacherId, string $like)
    {
        return $this->activeRosterQuery($teacherId)
            ->join('parent_student as parent_links', 'parent_links.student_id', '=', 'students.id')
            ->join('users as parents', 'parents.id', '=', 'parent_links.parent_id')
            ->join('academy_user as parent_membership', function ($join): void {
                $join->on('parent_membership.user_id', '=', 'parents.id')
                    ->on('parent_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('parent_membership.role', '=', 'parent')
                    ->where('parent_membership.status', '=', 'active');
            })
            ->where('parents.status', 'active')
            ->whereNull('parents.deleted_at')
            ->where(function ($filter) use ($like): void {
                $filter->where('parents.name', 'like', $like)
                    ->orWhere('students.name', 'like', $like);
            })
            ->select([
                'classrooms.id as classroom_id',
                'classrooms.title as classroom_title',
                'students.id as student_id',
                'students.name as student_name',
                'parents.id as parent_id',
                'parents.name as parent_name',
            ])
            ->orderBy('classrooms.title')
            ->orderBy('students.name')
            ->orderBy('parents.name')
            ->distinct();
    }

    private function classroomsQuery(int $teacherId, string $like)
    {
        return DB::table('classroom_teacher as assignments')
            ->join('classrooms', 'classrooms.id', '=', 'assignments.classroom_id')
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'classrooms.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('academy_user as teacher_membership', function ($join): void {
                $join->on('teacher_membership.user_id', '=', 'assignments.teacher_id')
                    ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('teacher_membership.role', '=', 'teacher')
                    ->where('teacher_membership.status', '=', 'active');
            })
            ->where('assignments.teacher_id', $teacherId)
            ->where('classrooms.status', 'active')
            ->where(function ($filter) use ($like): void {
                $filter->where('classrooms.title', 'like', $like)
                    ->orWhere('courses.title', 'like', $like);
            })
            ->whereExists(function ($parents): void {
                $parents->selectRaw('1')
                    ->from('classroom_student as roster')
                    ->join('parent_student as links', 'links.student_id', '=', 'roster.student_id')
                    ->join('users as parent_users', 'parent_users.id', '=', 'links.parent_id')
                    ->join('academy_user as parent_membership', function ($join): void {
                        $join->on('parent_membership.user_id', '=', 'parent_users.id')
                            ->whereColumn('parent_membership.academy_id', 'classrooms.academy_id')
                            ->where('parent_membership.role', '=', 'parent')
                            ->where('parent_membership.status', '=', 'active');
                    })
                    ->whereColumn('roster.classroom_id', 'classrooms.id')
                    ->where('roster.status', 'active')
                    ->where('parent_users.status', 'active')
                    ->whereNull('parent_users.deleted_at');
            })
            ->select([
                'classrooms.id as classroom_id',
                'classrooms.title as classroom_title',
                'courses.title as course_title',
            ])
            ->orderBy('classrooms.title')
            ->orderBy('classrooms.id')
            ->distinct();
    }

    private function activeRosterQuery(int $teacherId)
    {
        return DB::table('classroom_student as roster')
            ->join('classrooms', 'classrooms.id', '=', 'roster.classroom_id')
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'classrooms.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('classroom_teacher as assignments', function ($join) use ($teacherId): void {
                $join->on('assignments.classroom_id', '=', 'classrooms.id')
                    ->where('assignments.teacher_id', '=', $teacherId);
            })
            ->join('academy_user as teacher_membership', function ($join): void {
                $join->on('teacher_membership.user_id', '=', 'assignments.teacher_id')
                    ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('teacher_membership.role', '=', 'teacher')
                    ->where('teacher_membership.status', '=', 'active');
            })
            ->join('users as students', function ($join): void {
                $join->on('students.id', '=', 'roster.student_id')
                    ->where('students.status', '=', 'active')
                    ->whereNull('students.deleted_at');
            })
            ->join('academy_user as student_membership', function ($join): void {
                $join->on('student_membership.user_id', '=', 'students.id')
                    ->on('student_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('student_membership.role', '=', 'student')
                    ->where('student_membership.status', '=', 'active');
            })
            ->where('classrooms.status', 'active')
            ->where('roster.status', 'active');
    }
}
