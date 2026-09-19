<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Academy\UpdateAcademyRequest;
use App\Models\Academy;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademyController extends Controller
{
    public function edit(Academy $academy, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        return view('owner.academy.form', compact('academy'));
    }

    public function update(
        UpdateAcademyRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $academy->update($request->validated());

        return back()->with('success', 'اطلاعات آموزشگاه به‌روزرسانی شد.');
    }
}
