<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class ForcePasswordChangeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password-change');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->must_change_password, 403);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ], [
            'password.required' => 'رمز عبور جدید را وارد کنید.',
            'password.min' => 'رمز عبور جدید باید حداقل ۱۲ کاراکتر داشته باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز جدید مطابقت ندارد.',
        ]);

        if (Hash::check($validated['password'], (string) $user->password)) {
            return back()->withErrors([
                'password' => 'رمز جدید نباید همان رمز موقت فعلی باشد.',
            ])->withInput();
        }

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید از شیخان استفاده کنید.');
    }
}
