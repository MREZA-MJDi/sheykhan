<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $student = request()->user()->loadMissing('studentProfile.gradeRelation');
        return view('student.profile.edit', ['student' => $student]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $student = $request->user();
        $student->fill($request->validated());
        $student->save();

        return redirect()->route('student.profile.edit')
            ->with('success', 'اطلاعات حساب شما به‌روزرسانی شد.');
    }
}
