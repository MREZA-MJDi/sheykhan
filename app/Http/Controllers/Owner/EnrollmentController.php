<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\EnrollStudentRequest;
use App\Models\Academy;
use App\Services\OwnerEnrollmentService;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;

class EnrollmentController extends Controller
{
    public function store(
        EnrollStudentRequest $request,
        Academy $academy,
        OwnerEnrollmentService $enrollments,
        OwnerWorkspaceService $workspace,
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $enrollments->enroll(
            $request->user(),
            $academy,
            $request->validated(),
        );

        return back()->with('success', 'دانش‌آموز در دوره ثبت‌نام شد.');
    }
}
