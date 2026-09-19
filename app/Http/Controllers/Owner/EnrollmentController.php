<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\EnrollStudentRequest;
use App\Models\Academy;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;

class EnrollmentController extends Controller
{
    public function store(
        EnrollStudentRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $student = $academy->users()
            ->whereKey($request->validated('student_id'))
            ->wherePivot('role', 'student')
            ->wherePivot('status', 'active')
            ->firstOrFail();

        $course = $academy->courses()->findOrFail((int) $request->validated('course_id'));

        $classroom = null;
        if ($classroomId = $request->validated('classroom_id')) {
            $classroom = $course->classrooms()->findOrFail((int) $classroomId);
        }

        $price = (float) $course->price;
        $paidAmount = $request->validated('paid_amount');

        if ($course->isFree()) {
            $paidAmount = 0;
        } else {
            $paidAmount = $paidAmount ?? $price;
            abort_unless($paidAmount > 0, 422, 'برای دوره پولی، مبلغ ثبت‌نام باید بیشتر از صفر باشد.');
        }

        $course->enrollments()->updateOrCreate(
            ['student_id' => $student->id],
            [
                'classroom_id' => $classroom?->id,
                'status' => 'active',
                'paid_amount' => $paidAmount ?? $price,
                'started_at' => now(),
            ]
        );

        if ($classroom) {
            $classroom->students()->syncWithoutDetaching([
                $student->id => [
                    'status' => 'active',
                    'enrolled_at' => now(),
                ],
            ]);
        }

        return back()->with('success', 'دانش‌آموز در دوره ثبت‌نام شد.');
    }
}
