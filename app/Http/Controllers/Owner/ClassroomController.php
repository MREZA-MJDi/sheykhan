<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\\Controller;
use App\Http\Requests\Owner\\UpdateClassroomStatusRequest;
use App\Models\\Academy;
use App\Services\\OwnerWorkspaceService;
use Illuminate\Http\\RedirectResponse;
use Illuminate\View\\View;

class ClassroomController extends Controller
{
    public function index(Academy $academy, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        $classrooms = $academy->classrooms()
            ->with(['course:id,title', 'teachers:id,name'])
            ->withCount([
                'students as active_students_count' => fn ($query) => $query->where('classroom_student.status', 'active'),
            ])
            ->latest()
            ->get();

        return view('owner.classrooms.index', compact('academy', 'classrooms'));
    }

    public function update(
        UpdateClassroomStatusRequest $request,
        Academy $academy,
        int $classroom,
        OwnerWorkspaceService $workspace,
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $item = $academy->classrooms()->findOrFail($classroom);
        $item->update(['status' => $request->validated('status')]);

        return back()->with(
            'success',
            $item->status === 'active' ? 'کلاس فعال شد.' : 'کلاس آرشیو شد.'
        );
    }
}