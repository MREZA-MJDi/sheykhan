<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreClassroomRequest;
use App\Http\Requests\Owner\UpdateClassroomRequest;
use App\Http\Requests\Owner\UpdateClassroomStatusRequest;
use App\Models\Academy;
use App\Models\Classroom;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Academy $academy, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        $classrooms = $academy->classrooms()
            ->with(['course:id,title', 'teachers:id,name'])
            ->withCount('students')
            ->latest()
            ->get();

        return view('owner.classrooms.index', compact('academy', 'classrooms'));
    }

    public function create(Academy $academy, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        $options = $workspace->courseTeacherOptions(request()->user(), $academy);

        return view('owner.classrooms.form', [
            'academy' => $academy,
            'classroom' => new Classroom(['status' => 'active']),
            ...$options,
        ]);
    }

    public function store(
        StoreClassroomRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->createClassroom(
            $request->user(),
            $academy,
            $request->validated(),
        );

        return redirect()
            ->route('owner.classrooms.index', $academy)
            ->with('success', 'کلاس با موفقیت ساخته شد.');
    }

    public function edit(
        Academy $academy,
        int $classroom,
        OwnerWorkspaceService $workspace
    ): View {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        $item = $academy->classrooms()
            ->with('teachers:id,name')
            ->findOrFail($classroom);

        $options = $workspace->courseTeacherOptions(request()->user(), $academy);

        return view('owner.classrooms.form', [
            'academy' => $academy,
            'classroom' => $item,
            ...$options,
        ]);
    }

    public function update(
        UpdateClassroomRequest $request,
        Academy $academy,
        int $classroom,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->updateClassroom(
            $request->user(),
            $academy,
            $classroom,
            $request->validated(),
        );

        return redirect()
            ->route('owner.classrooms.index', $academy)
            ->with('success', 'کلاس با موفقیت به‌روزرسانی شد.');
    }

    public function updateStatus(
        UpdateClassroomStatusRequest $request,
        Academy $academy,
        int $classroom,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $item = $academy->classrooms()->findOrFail($classroom);
        $item->update(['status' => $request->validated('status')]);

        return back()->with('success', 'وضعیت کلاس به‌روزرسانی شد.');
    }
}
