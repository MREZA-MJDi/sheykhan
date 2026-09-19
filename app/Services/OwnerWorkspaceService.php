<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Course;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class OwnerWorkspaceService
{
    public function academies(User $owner): Collection
    {
        return Academy::query()
            ->where('owner_id', $owner->id)
            ->withCount([
                'users as member_count',
                'courses as course_count',
                'users as teacher_count' => fn ($query) => $query
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active'),
                'users as student_count' => fn ($query) => $query
                    ->where('academy_user.role', 'student')
                    ->where('academy_user.status', 'active'),
            ])
            ->latest()
            ->get();
    }

    public function canManageAcademy(User $owner, Academy $academy): bool
    {
        return $academy->owner_id === $owner->id;
    }

    public function people(User $owner, Academy $academy): array
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $members = $academy->users()
            ->wherePivot('status', 'active')
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->groupBy(fn (User $user) => $user->pivot->role);

        return [
            'teachers' => $members->get('teacher', collect()),
            'students' => $members->get('student', collect()),
            'parents' => $members->get('parent', collect()),
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
            ->with('classrooms:id,course_id,title')
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return compact('teachers', 'courses');
    }

    public function createTeacher(User $owner, Academy $academy, array $data): User
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        return DB::transaction(function () use ($academy, $data): User {
            $teacher = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $roleId = Role::where('slug', 'teacher')->value('id');
            abort_unless($roleId, 500, 'نقش مدرس در سیستم تعریف نشده است.');

            $teacher->roles()->attach($roleId);

            TeacherProfile::create([
                'user_id' => $teacher->id,
                'bio' => null,
                'specialization' => $data['specialization'] ?? null,
                'education' => null,
                'experience_years' => $data['experience_years'] ?? 0,
                'is_verified' => true,
            ]);

            $academy->users()->attach($teacher->id, [
                'role' => 'teacher',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $teacher;
        });
    }

    public function assignTeacher(User $owner, Academy $academy, int $teacherId, int $courseId): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $isTeacher = $academy->users()
            ->whereKey($teacherId)
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->exists();

        $course = $academy->courses()->findOrFail($courseId);

        if (!$isTeacher) {
            abort(422, 'این کاربر مدرس فعال این آموزشگاه نیست.');
        }

        $course->teachers()->syncWithoutDetaching([
            $teacherId => ['is_primary' => false],
        ]);
    }
}
