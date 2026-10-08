<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;

class SecurityController extends Controller
{
    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->password = $request->validated('password');
        $user->save();

        $request->session()->regenerate();

        return redirect()->route('student.profile.edit')
            ->with('success', 'رمز عبور شما با موفقیت تغییر کرد.');
    }
}
