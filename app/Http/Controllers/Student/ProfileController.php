<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Models\StudentProfile;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $student = request()->user()->load([
            'studentProfile.gradeRelation',
            'studentProfile.media' => fn ($query) => $query
                ->wherePivot('collection', 'student-avatar')
                ->orderByPivot('sort_order'),
        ]);

        return view('student.profile.edit', [
            'student' => $student,
            'avatar' => $student->studentProfile?->media?->first(),
        ]);
    }

    public function update(
        UpdateProfileRequest $request,
        MediaService $mediaService,
    ): RedirectResponse {
        $student = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($request, $student, $data, $mediaService): void {
            $userUpdates = ['name' => $data['name']];
            if (array_key_exists('email', $data)) {
                $userUpdates['email'] = $data['email'];
            }

            $student->update($userUpdates);

            if ($request->hasFile('avatar')) {
                $profile = StudentProfile::query()->firstOrCreate([
                    'user_id' => $student->id,
                ]);

                $oldAvatar = $profile->media()
                    ->wherePivot('collection', 'student-avatar')
                    ->orderByPivot('sort_order')
                    ->first();

                // Profile photos are public, non-learning images so they can
                // appear beside this student's name in authorized class rosters.
                $newAvatar = $mediaService->upload(
                    $request->file('avatar'),
                    $profile,
                    [
                        'disk' => 'local',
                        'directory' => 'students/' . $student->id,
                        'collection' => 'student-avatar',
                        'visibility' => 'public',
                        'sort_order' => 0,
                        'is_featured' => true,
                    ],
                );

                if ($oldAvatar && $oldAvatar->id !== $newAvatar->id) {
                    $mediaService->detach($oldAvatar, $profile);

                    if ($oldAvatar->attachments()->doesntExist()) {
                        $mediaService->delete($oldAvatar);
                    }
                }
            }
        });

        return redirect()->route('student.profile.edit')
            ->with('success', 'اطلاعات حساب و تصویر پروفایل شما به‌روزرسانی شد.');
    }
}
