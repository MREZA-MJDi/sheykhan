<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreClassroomRequest;
use App\Http\Requests\Owner\UpdateClassroomRequest;
use App\Models\Academy;
use App\Models\Classroom;
use App\Services\OwnerClassroomService;
use App\Services\ClassroomCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Academy $academy, OwnerClassroomService $service): View
    {
        $classrooms = $service->index(request()->user(), $academy);
        $summary = $service->indexSummary(request()->user(), $academy);
        $selectedCourseId = filter_var(request()->query('course_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $courses = $selectedCourseId
            ? $academy->courses()->whereKey($selectedCourseId)->get(['courses.id', 'courses.title'])
            : collect();

        return view('owner.classrooms.index', compact('academy', 'classrooms', 'summary', 'courses'));
    }

    public function create(Academy $academy, OwnerClassroomService $service): View
    {
        $selectedCourseId = request()->old('course_id');
        $selectedTeacherIds = (array) request()->old('teacher_ids', []);

        return view('owner.classrooms.form', [
            'academy' => $academy,
            'classroom' => new Classroom(['status' => 'active', 'code' => app(ClassroomCodeGenerator::class)->generate()]),
            ...$service->formData(
                request()->user(),
                $academy,
                $selectedCourseId ? (int) $selectedCourseId : null,
                $selectedTeacherIds,
            ),
            'selectedTeacherIds' => array_map('intval', $selectedTeacherIds),
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

        $item->load('teachers:id,name');
        $selectedCourseId = request()->old('course_id', $item->course_id);
        $selectedTeacherIds = (array) request()->old('teacher_ids', $item->teachers->pluck('id')->all());

        return view('owner.classrooms.form', [
            'academy' => $academy,
            'classroom' => $item,
            ...$service->formData(
                request()->user(),
                $academy,
                $selectedCourseId ? (int) $selectedCourseId : null,
                $selectedTeacherIds,
            ),
            'selectedTeacherIds' => array_map('intval', $selectedTeacherIds),
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

    public function archive(Academy $academy, int $classroom, OwnerClassroomService $service): RedirectResponse
    {
        $item = Classroom::query()->findOrFail($classroom);
        $service->archive(request()->user(), $academy, $item);

        return redirect()->route('owner.classrooms.index', ['academy' => $academy, 'status' => 'archived'])
            ->with('success', 'کلاس آرشیو شد؛ دانش‌آموزان، حضور و غیاب و سوابق آموزشی حفظ شده‌اند.');
    }

    public function restore(Academy $academy, int $classroom, OwnerClassroomService $service): RedirectResponse
    {
        $item = Classroom::query()->findOrFail($classroom);
        $service->restore(request()->user(), $academy, $item);

        return redirect()->route('owner.classrooms.index', $academy)
            ->with('success', 'کلاس دوباره فعال شد؛ همهٔ سوابق قبلی حفظ شده‌اند.');
    }
}
