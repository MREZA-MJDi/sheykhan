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

        return view('teacher.students.index', [
            'students' => $workspace->studentsPaginated(
                $teacher,
                self::PAGE_SIZE,
                $sort,
                $direction,
                $search,
            ),
            'studentSearch' => $search,
            'studentSort' => $sort,
            'studentDirection' => $direction,
        ]);
    }
}
