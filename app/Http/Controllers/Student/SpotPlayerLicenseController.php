<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\SpotPlayerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SpotPlayerLicenseController extends Controller
{
    public function store(Request $request, Course $course, SpotPlayerService $spotPlayer): RedirectResponse
    {
        $spotPlayer->licenseForStudent($request->user(), $course);

        return redirect()
            ->route('student.courses.show', $course)
            ->with('success', 'لایسنس اختصاصی SpotPlayer آماده است.');
    }
}
