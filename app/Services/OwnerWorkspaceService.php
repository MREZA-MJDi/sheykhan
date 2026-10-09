<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademicGrade;
use App\Models\StudentOnboarding;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OwnerWorkspaceService
{
    public function academies(User $owner): Collection
    {
        abort_unless($owner->hasRole('academy-owner'), 403);

        return $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function canManageAcademy(User $owner, Academy $academy): bool
    {
        return $owner->hasRole('academy-owner')
            && (int) $academy->owner_id === (int) $owner->id;
    }

    public function people(User $owner, Academy $academy): array
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $base = fn (string $role, string $pageName) => $academy->users()
            ->wherePivot('status', 'active')
            ->wherePivot('role', $role)
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->paginate(25, ['*'], $pageName);

        $activeTeachers = $base('teacher', 'teachers_page');

        $archivedTeachers = $academy->users()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'archived')
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->paginate(25, ['*'], 'archived_teachers_page');

        return [
            'teachers' => $activeTeachers,
            'archivedTeachers' => $archivedTeachers,
            'students' => $base('student', 'students_page'),
            'parents' => $base('parent', 'parents_page'),
            'grades' => AcademicGrade::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(['id', 'title']),
            'onboardings' => StudentOnboarding::query()
                ->where('academy_id', $academy->id)
                ->with([
                    'student:id,name,email,mobile',
                    'requestedGrade:id,title',
                    'admin:id,name',
                ])
                ->latest('activated_at')
                ->paginate(15, ['*'], 'onboardings_page'),
        ];
    }

    public function courseTeacherOptions(User $owner, Academy $academy): array
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $teachers = $academy->users()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name']);

        $courses = $academy->courses()
            ->with('classrooms:id,academy_id,course_id,title,status,capacity')
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return [
            'teacherOptions' => $teachers,
            'courseOptions' => $courses,
        ];
    }

    public function createTeacher(User $owner, Academy $academy, array $data): User
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        return DB::transaction(function () use ($academy, $data): User {
            $teacher = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => $data['password'],
            ]);

            $roleId = Role::query()->where('slug', 'teacher')->value('id');
            abort_unless($roleId, 500, 'نقش مدرس در سیستم تعریف نشده است.');

            $teacher->roles()->syncWithoutDetaching([$roleId]);

            TeacherProfile::create([
                'user_id' => $teacher->id,
                'bio' => null,
                'specialization' => $data['specialization'] ?? null,
                'education' => null,
                'experience_years' => $data['experience_years'] ?? 0,
                'is_verified' => true,
            ]);

            $academy->users()->syncWithoutDetaching([
                $teacher->id => [
                    'role' => 'teacher',
                    'status' => 'active',
                    'joined_at' => now(),
                ],
            ]);

            return $teacher;
        });
    }

    public function archiveTeacher(User $owner, Academy $academy, User $teacher): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $membership = $academy->users()
            ->whereKey($teacher->id)
            ->wherePivot('role', 'teacher')
            ->first();

        abort_if(!$membership, 404);

        DB::transaction(function () use ($academy, $teacher): void {
            $academy->users()->updateExistingPivot($teacher->id, [
                'status' => 'archived',
            ]);

            app(TeacherDirectoryService::class)->clearPublicCache();
        });
    }

    public function restoreTeacher(User $owner, Academy $academy, User $teacher): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $membership = $academy->users()
            ->whereKey($teacher->id)
            ->wherePivot('role', 'teacher')
            ->first();

        abort_if(!$membership, 404);

        DB::transaction(function () use ($academy, $teacher, $membership): void {
            $academy->users()->updateExistingPivot($teacher->id, [
                'status' => 'active',
                'joined_at' => $membership->pivot->joined_at ?? now(),
            ]);

            app(TeacherDirectoryService::class)->clearPublicCache();
        });
    }

    public function updateTeacherPublicVisibility(User $owner, Academy $academy, User $teacher, bool $isPublic): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        abort_unless(
            $academy->users()
                ->whereKey($teacher->id)
                ->wherePivot('role', 'teacher')
                ->exists(),
            404
        );

        $teacher->loadMissing('teacherProfile');

        abort_unless($teacher->teacherProfile, 404);

        $teacher->teacherProfile->update([
            'is_public' => $isPublic,
        ]);

        app(TeacherDirectoryService::class)->clearPublicCache();
    }

    public function assignTeacher(User $owner, Academy $academy, int $teacherId, int $courseId): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $isTeacher = $academy->users()
            ->whereKey($teacherId)
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isTeacher) {
            throw ValidationException::withMessages([
                'teacher_id' => 'این کاربر مدرس فعال این آموزشگاه نیست.',
            ]);
        }

        $course = $academy->courses()->findOrFail($courseId);

        $course->teachers()->syncWithoutDetaching([
            $teacherId => ['is_primary' => false],
        ]);
    }
}
