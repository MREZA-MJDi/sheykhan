<?php

namespace AppHttpControllersTeacher;

use AppHttpControllersController;
use AppHttpRequestsTeacherUpdateProfileRequest;
use AppModelsTeacherProfile;
use AppServicesMediaService;
use IlluminateHttpRedirectResponse;
use IlluminateSupportFacadesDB;
use IlluminateViewView;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $teacher = request()->user()->load([
            'teacherProfile' => fn ($query) => $query->with([
                'media' => fn ($media) => $media
                    ->where('visibility', 'public')
                    ->where('collection', 'teacher-avatar')
                    ->orderByPivot('sort_order'),
            ]),
        ]);

        $profile = $teacher->teacherProfile ?? new TeacherProfile();

        return view('teacher.profile.edit', [
            'teacher' => $teacher,
            'profile' => $profile,
            'avatar' => $profile->media->first(),
        ]);
    }

    public function update(
        UpdateProfileRequest $request,
        MediaService $mediaService,
    ): RedirectResponse {
        $teacher = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($request, $teacher, $data, $mediaService): void {
            $teacher->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
            ]);

            $profile = TeacherProfile::query()->firstOrCreate(
                ['user_id' => $teacher->id],
                ['is_verified' => false, 'is_public' => true],
            );

            $profile->update([
                'bio' => $data['bio'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'education' => $data['education'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'is_public' => $request->boolean('is_public'),
            ]);

            if ($request->hasFile('avatar')) {
                $oldAvatar = $profile->media()
                    ->where('collection', 'teacher-avatar')
                    ->orderByPivot('sort_order')
                    ->first();

                $newAvatar = $mediaService->upload(
                    $request->file('avatar'),
                    $profile,
                    [
                        'disk' => 'local',
                        'directory' => 'teachers/' . $teacher->id,
                        'collection' => 'teacher-avatar',
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

        return redirect()->route('teacher.profile.edit')
            ->with('success', 'پروفایل استاد با موفقیت به‌روزرسانی شد.');
    }
}
