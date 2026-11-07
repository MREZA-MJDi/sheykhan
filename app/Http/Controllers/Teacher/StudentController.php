<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StudentController extends Controller
{
    private const PAGE_SIZE = 20;

    private const SORT_OPTIONS = ['name', 'progress', 'assignments', 'exams'];

    public function show(User $student, TeacherWorkspaceService $workspace): View
    {
        return view('teacher.students.show', $workspace->studentProfileForTeacher(
            request()->user(),
            (int) $student->id,
        ));
    }

    public function index(Request $request, TeacherWorkspaceService $workspace): View
    {
        $requestedSort = $request->query('sort', 'name');
        $sort = is_string($requestedSort) && in_array($requestedSort, self::SORT_OPTIONS, true)
            ? $requestedSort
            : 'name';

        $requestedDirection = $request->query('direction', 'asc');
        $direction = is_string($requestedDirection) && strtolower($requestedDirection) === 'desc'
            ? 'desc'
            : 'asc';

        $requestedSearch = $request->query('q', '');
        $search = is_string($requestedSearch)
            ? mb_substr(trim($requestedSearch), 0, 100)
            : '';

        /** @var User $teacher */
        $teacher = $request->user();
        $requestedClassroomId = $request->query('classroom_id');
        $classroomId = is_scalar($requestedClassroomId) && ctype_digit((string) $requestedClassroomId)
            ? (int) $requestedClassroomId
            : null;
        $selectedClassroom = $classroomId
            ? $workspace->classroomOwnedBy($teacher, $classroomId)
            : null;

        return view('teacher.students.index', [
            'students' => $workspace->studentsPaginated(
                $teacher,
                self::PAGE_SIZE,
                $sort,
                $direction,
                $search,
                $classroomId,
            ),
            'classrooms' => $workspace->classrooms($teacher),
            'selectedClassroom' => $selectedClassroom,
            'selectedClassroomId' => $classroomId,
            'studentSearch' => $search,
            'studentSort' => $sort,
            'studentDirection' => $direction,
        ]);
    }
}
