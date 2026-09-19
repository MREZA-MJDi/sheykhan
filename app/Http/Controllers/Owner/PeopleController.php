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

        $assignments = $academy->courses()
            ->with(['teachers:id,name'])
            ->withCount([
                'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('title')
            ->get();

        return view('owner.people.index', [
            'academy' => $academy,
            ...$people,
            ...$options,
            'assignments' => $assignments,
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
            (bool) $request->boolean('is_primary'),
        );

        return back()->with('success', 'مدرس به دوره اختصاص داده شد.');
    }

    public function detachTeacher(
        Academy $academy,
        int $teacher,
        int $course,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->detachTeacher(
            request()->user(),
            $academy,
            $teacher,
            $course,
        );

        return back()->with('success', 'مدرس از این دوره حذف شد.');
    }

    public function enrollStudent(
        EnrollStudentRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->enrollStudent(
            $request->user(),
            $academy,
            $request->validated(),
        );

        return back()->with('success', 'ثبت‌نام دانش‌آموز با موفقیت به‌روزرسانی شد.');
    }
}
