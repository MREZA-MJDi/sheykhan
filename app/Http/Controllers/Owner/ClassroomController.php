<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Academy $academy, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        $classrooms = $academy->classrooms()
            ->with(['course:id,title', 'teachers:id,name', 'students:id,name'])
            ->withCount('students')
            ->latest()
            ->get();

        return view('owner.classrooms.index', compact('academy', 'classrooms'));
    }

    public function update(Academy $academy, int $classroom, OwnerWorkspaceService $workspace): RedirectResponse
    {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        $item = $academy->classrooms()->findOrFail($classroom);
        $status = request()->string('status')->toString();

        abort_unless(in_array($status, ['active', 'archived'], true), 422);

        $item->update(['status' => $status]);

        return back()->with('success', 'وضعیت کلاس به‌روزرسانی شد.');
    }
}
