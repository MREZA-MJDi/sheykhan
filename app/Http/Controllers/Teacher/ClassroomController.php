<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreClassroomRequest;
use App\Http\Requests\Teacher\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Services\TeacherWorkspaceService;
use App\Services\ClassroomCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.classrooms.index', [
            'classrooms' => $workspace->classroomsPaginated(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.classrooms.form', [
            'courses' => $workspace->courses(request()->user()),
            'classroom' => new Classroom(['code' => app(ClassroomCodeGenerator::class)->generate()]),
        ]);
    }

    public function store(
        StoreClassroomRequest $request,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        DB::transaction(function () use ($request, $course): void {
            $classroom = $course->classrooms()->create([
                'academy_id' => $course->academy_id,
                'course_id' => $course->id,
                'title' => $request->validated('title'),
                'code' => app(ClassroomCodeGenerator::class)->resolve($request->validated('code') ?: null),
                'description' => $request->validated('description'),
                'capacity' => $request->validated('capacity'),
                'status' => 'active',
                'starts_at' => $request->validated('starts_at'),
                'ends_at' => $request->validated('ends_at'),
            ]);

            $classroom->teachers()->syncWithoutDetaching([$request->user()->id]);
            $this->syncGeneratedSchedule($classroom);
        });

        return redirect()->route('teacher.classrooms.index')
            ->with('success', 'کلاس با موفقیت ساخته شد.');
    }

    public function edit(Classroom $classroom, TeacherWorkspaceService $workspace): View
    {
        $classroomModel = $workspace->classroomOwnedBy(request()->user(), $classroom->id);

        return view('teacher.classrooms.form', [
            'courses' => $workspace->courses(request()->user()),
            'classroom' => $classroomModel,
        ]);
    }

    public function update(
        UpdateClassroomRequest $request,
        Classroom $classroom,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $classroomModel = $workspace->classroomOwnedBy($request->user(), $classroom->id);
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        DB::transaction(function () use ($request, $classroomModel, $course): void {
            $lockedClassroom = Classroom::query()->lockForUpdate()->findOrFail($classroomModel->id);
            $courseChanged = (int) $lockedClassroom->course_id !== (int) $course->id;

            if ($courseChanged) {
                $hasStudents = $lockedClassroom->students()->exists()
                    || $lockedClassroom->enrollments()->exists()
                    || $lockedClassroom->attendance()->exists();

                if ($hasStudents) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'course_id' => 'دورهٔ کلاس دارای سابقهٔ ثبت‌نام یا حضور و غیاب است؛ برای حفظ سوابق، تغییر دوره مجاز نیست.',
                    ]);
                }
            }

            $activeStudents = $lockedClassroom->students()
                ->wherePivot('status', 'active')
                ->count();

            $capacity = $request->validated('capacity');
            if ($capacity !== null && (int) $capacity < $activeStudents) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'capacity' => 'ظرفیت کلاس نمی‌تواند از تعداد دانش‌آموزان فعال کمتر باشد.',
                ]);
            }

            $lockedClassroom->update([
                'academy_id' => $course->academy_id,
                'course_id' => $course->id,
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'capacity' => $capacity,
                'status' => $request->validated('status'),
                'starts_at' => $request->validated('starts_at'),
                'ends_at' => $request->validated('ends_at'),
            ]);

            $this->syncGeneratedSchedule($lockedClassroom);
        });

        return redirect()->route('teacher.classrooms.index')
            ->with('success', 'اطلاعات کلاس با موفقیت به‌روزرسانی شد.');
    }

    /**
     * Keep the single generated weekly schedule aligned with the classroom dates.
     * Multiple schedule rows are treated as deliberate/manual exceptions.
     */
    private function syncGeneratedSchedule(Classroom $classroom): void
    {
        if (! $classroom->starts_at) {
            return;
        }

        $schedules = $classroom->schedules()->lockForUpdate()->get();

        $starts = $classroom->starts_at;
        $ends = $classroom->ends_at;
        $startTime = $starts->format('H:i');
        $endTime = ($ends && $ends->format('H:i') > $startTime)
            ? $ends->format('H:i')
            : $starts->copy()->addMinutes(90)->format('H:i');

        $attributes = [
            'weekday' => $starts->dayOfWeek,
            'start_time' => $startTime . ':00',
            'end_time' => $endTime . ':00',
        ];

        if ($schedules->isEmpty()) {
            $classroom->schedules()->create($attributes + [
                'provider' => \App\Services\MeetingLinkService::JITSI,
                'meeting_url' => app(\App\Services\MeetingLinkService::class)->generateJitsiUrl(),
            ]);

            return;
        }

        if ($schedules->count() === 1) {
            $schedules->first()->update($attributes);
        }
    }

    public function destroy(Classroom $classroom, TeacherWorkspaceService $workspace): RedirectResponse
    {
        $classroomModel = $workspace->classroomOwnedBy(request()->user(), $classroom->id);

        DB::transaction(function () use ($classroomModel): void {
            $classroomModel->delete();
        });

        return redirect()->route('teacher.classrooms.index')
            ->with('success', 'کلاس با موفقیت حذف شد.');
    }
}
