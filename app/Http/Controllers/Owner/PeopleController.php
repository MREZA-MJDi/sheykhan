<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AssignTeacherCourseRequest;
use App\Http\Requests\Owner\EnrollStudentRequest;
use App\Http\Requests\Owner\StoreTeacherRequest;
use App\Models\Academy;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PeopleController extends Controller
{
    public function index(Academy $academy, OwnerWorkspaceService $workspace): View
    {
        $people = $workspace->people(request()->user(), $academy);
        $options = $workspace->courseTeacherOptions(request()->user(), $academy);

        return view('owner.people.index', [
            'academy' => $academy,
            ...$people,
            ...$options,
        ]);
    }

    public function storeTeacher(
        StoreTeacherRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->createTeacher(
            $request->user(),
            $academy,
            $request->validated()
        );

        return back()->with('success', 'حساب مدرس ساخته شد و به آموزشگاه اضافه شد.');
    }

    public function assignTeacher(
        AssignTeacherCourseRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->assignTeacher(
            $request->user(),
            $academy,
            (int) $request->validated('teacher_id'),
            (int) $request->validated('course_id'),
        );

        return back()->with('success', 'مدرس به دوره اختصاص داده شد.');
    }
}
