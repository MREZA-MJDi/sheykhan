<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreClassroomRequest;
use App\Http\Requests\Owner\UpdateClassroomRequest;
use App\Models\Academy;
use App\Models\Classroom;
use App\Services\OwnerClassroomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Academy $academy, OwnerClassroomService $service): View
    {
        $classrooms = $service->index(request()->user(), $academy);

        return view('owner.classrooms.index', compact('academy', 'classrooms'));
    }

    public function create(Academy $academy, OwnerClassroomService $service): View
    {
        return view('owner.classrooms.form', [
            'academy' => $academy,
            'classroom' => new Classroom(['status' => 'active']),
            ...$service->formData(request()->user(), $academy),
            'selectedTeacherIds' => [],
        ]);
    }

    public function store(
        StoreClassroomRequest $request,
        Academy $academy,
        OwnerClassroomService $service
    ): RedirectResponse {
        $classroom = $service->create(
            $request->user(),
            $academy,
            $request->validated()
        );

        return redirect()
            ->route('owner.classrooms.show', [$academy, $classroom])
            ->with('success', 'کلاس با موفقیت ساخته شد.');
    }

    public function show(
        Academy $academy,
        int $classroom,
        OwnerClassroomService $service
    ): View {
        $item = Classroom::query()->findOrFail($classroom);

        return view(
            'owner.classrooms.show',
            $service->show(request()->user(), $academy, $item) + ['academy' => $academy]
        );
    }

    public function edit(
        Academy $academy,
        int $classroom,
        OwnerClassroomService $service
    ): View {
        abort_unless($service->canManageAcademy(request()->user(), $academy), 403);

        $item = Classroom::query()->findOrFail($classroom);
        abort_if((int) $item->academy_id !== (int) $academy->id, 404);

        return view('owner.classrooms.form', [
            'academy' => $academy,
            'classroom' => $item->load('teachers:id,name'),
            ...$service->formData(request()->user(), $academy),
            'selectedTeacherIds' => $item->teachers->pluck('id')->all(),
        ]);
    }

    public function update(
        UpdateClassroomRequest $request,
        Academy $academy,
        int $classroom,
        OwnerClassroomService $service
    ): RedirectResponse {
        $item = Classroom::query()->findOrFail($classroom);

        $service->update(
            $request->user(),
            $academy,
            $item,
            $request->validated()
        );

        return redirect()
            ->route('owner.classrooms.show', [$academy, $item])
            ->with('success', 'اطلاعات کلاس به‌روزرسانی شد.');
    }
}
