<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        $members = $academy->users()
            ->select('users.id', 'users.name', 'users.email')
            ->whereIn('academy_user.status', ['active', 'archived'])
            ->get()
            ->groupBy(fn (User $user) => $user->pivot->role);

        $teachers = $members->get('teacher', collect());
        $activeTeachers = $teachers->filter(fn (User $teacher) => $teacher->pivot->status === 'active')->values();
        $archivedTeachers = $teachers->filter(fn (User $teacher) => $teacher->pivot->status === 'archived')->values();

        $teachers->load('teacherProfile:id,user_id,is_verified,is_public');

        return [
            'teachers' => $activeTeachers,
            'archivedTeachers' => $archivedTeachers,
            'students' => $members->get('student', collect())->filter(fn (User $student) => $student->pivot->status === 'active')->values(),
            'parents' => $members->get('parent', collect())->filter(fn (User $parent) => $parent->pivot->status === 'active')->values(),
            'memberStats' => [
                'activeTeachers' => $activeTeachers->count(),
                'archivedTeachers' => $archivedTeachers->count(),
                'publicTeachers' => $teachers->filter(fn (User $teacher) => (bool) $teacher->teacherProfile?->is_public)->count(),
                'students' => $members->get('student', collect())->filter(fn (User $student) => $student->pivot->status === 'active')->count(),
                'parents' => $members->get('parent', collect())->filter(fn (User $parent) => $parent->pivot->status === 'active')->count(),
            ],
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

        return compact('teachers', 'courses');
    }

    public function createTeacher(User $owner, Academy $academy, array $data): User
    {
        abort_unless(
            $this->canManageAcademy($owner, $academy)
            && $owner->hasPermission('teachers.manage'),
            403
        );

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
                'is_public' => (bool) ($data['is_public'] ?? true),
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

    public function assignTeacher(User $owner, Academy $academy, int $teacherId, int $courseId): void
    {
        abort_unless(
            $this->canManageAcademy($owner, $academy)
            && $owner->hasPermission('teachers.manage'),
            403
        );

        $isTeacher = $academy->users()
            ->whereKey($teacherId)
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->exists();

        abort_unless($isTeacher, 422, 'این کاربر مدرس فعال این آموزشگاه نیست.');

        $course = $academy->courses()->findOrFail($courseId);

        $course->teachers()->syncWithoutDetaching([
            $teacherId => ['is_primary' => false],
        ]);
    }

    public function archiveTeacher(User $owner, Academy $academy, User $teacher): void
    {
        abort_unless(
            $this->canManageAcademy($owner, $academy)
            && $owner->hasPermission('teachers.manage'),
            403
        );

        DB::transaction(function () use ($academy, $teacher): void {
            $membership = DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $teacher->id)
                ->where('role', 'teacher')
                ->lockForUpdate()
                ->first();

            abort_unless($membership, 404, 'این مدرس عضو این آموزشگاه نیست.');

            if ($membership->status === 'archived') {
                return;
            }

            DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $teacher->id)
                ->where('role', 'teacher')
                ->update([
                    'status' => 'archived',
                    'updated_at' => now(),
                ]);
        });
    }

    public function restoreTeacher(User $owner, Academy $academy, User $teacher): void
    {
        abort_unless(
            $this->canManageAcademy($owner, $academy)
            && $owner->hasPermission('teachers.manage'),
            403
        );

        DB::transaction(function () use ($academy, $teacher): void {
            $membership = DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $teacher->id)
                ->where('role', 'teacher')
                ->lockForUpdate()
                ->first();

            abort_unless($membership, 404, 'این مدرس عضو این آموزشگاه نیست.');

            if ($membership->status === 'active') {
                return;
            }

            DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $teacher->id)
                ->where('role', 'teacher')
                ->update([
                    'status' => 'active',
                    'joined_at' => $membership->joined_at ?: now(),
                    'updated_at' => now(),
                ]);
        });
    }

    public function updateTeacherPublicVisibility(
        User $owner,
        Academy $academy,
        User $teacher,
        bool $isPublic
    ): void {
        abort_unless(
            $this->canManageAcademy($owner, $academy)
            && $owner->hasPermission('teachers.manage'),
            403
        );

        abort_unless(
            $academy->users()
                ->whereKey($teacher->id)
                ->wherePivot('role', 'teacher')
                ->exists(),
            404,
            'این مدرس عضو این آموزشگاه نیست.'
        );

        $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            ['is_public' => $isPublic]
        );
    }
}
