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

    public function assignTeacher(
        User $owner,
        Academy $academy,
        int $teacherId,
        int $courseId,
        bool $isPrimary = false
    ): void {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $isTeacher = $academy->users()
            ->whereKey($teacherId)
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->exists();

        $course = $academy->courses()->findOrFail($courseId);

        abort_unless($isTeacher, 422, 'این کاربر مدرس فعال این آموزشگاه نیست.');

        DB::transaction(function () use ($course, $teacherId, $isPrimary): void {
            if ($isPrimary) {
                $course->teachers()->updateExistingPivot(
                    $course->teachers()->pluck('users.id')->all(),
                    ['is_primary' => false]
                );
            }

            $course->teachers()->syncWithoutDetaching([
                $teacherId => ['is_primary' => $isPrimary],
            ]);
        });
    }

    public function detachTeacher(User $owner, Academy $academy, int $teacherId, int $courseId): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $isTeacher = $academy->users()
            ->whereKey($teacherId)
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->exists();

        $course = $academy->courses()->findOrFail($courseId);

        abort_unless($isTeacher, 422, 'این کاربر مدرس فعال این آموزشگاه نیست.');

        $course->teachers()->detach($teacherId);
    }

    public function createClassroom(User $owner, Academy $academy, array $data): App\Models\Classroom
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $course = $academy->courses()->findOrFail((int) $data['course_id']);
        $teacherIds = $this->scopedTeacherIds($academy, $data['teacher_ids'] ?? []);

        $classroom = DB::transaction(function () use ($academy, $course, $teacherIds, $data) {
            $classroom = $course->classrooms()->create([
                'academy_id' => $academy->id,
                'course_id' => $course->id,
                'title' => $data['title'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'status' => $data['status'] ?? 'active',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            if ($teacherIds !== []) {
                $classroom->teachers()->sync($teacherIds);
            }

            return $classroom;
        });

        return $classroom;
    }

    public function updateClassroom(User $owner, Academy $academy, int $classroomId, array $data): App\Models\Classroom
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $classroom = $academy->classrooms()->findOrFail($classroomId);
        $course = $academy->courses()->findOrFail((int) ($data['course_id'] ?? $classroom->course_id));
        $teacherIds = $this->scopedTeacherIds($academy, $data['teacher_ids'] ?? []);
        $activeStudentCount = $classroom->students()->wherePivot('status', 'active')->count();

        if ($course->id !== $classroom->course_id && $activeStudentCount > 0) {
            abort(422, 'کلاسی که دانش‌آموز فعال دارد، نمی‌تواند به دوره دیگری منتقل شود.');
        }

        if (($data['capacity'] ?? null) !== null && (int) $data['capacity'] < $activeStudentCount) {
            abort(422, 'ظرفیت جدید کلاس نمی‌تواند کمتر از تعداد دانش‌آموزان فعال باشد.');
        }

        DB::transaction(function () use ($classroom, $course, $teacherIds, $data): void {
            $classroom->update([
                'academy_id' => $course->academy_id,
                'course_id' => $course->id,
                'title' => $data['title'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'status' => $data['status'] ?? $classroom->status,
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            $classroom->teachers()->sync($teacherIds);
        });

        return $classroom->refresh();
    }

    public function enrollStudent(User $owner, Academy $academy, array $data): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $student = $academy->users()
            ->whereKey((int) $data['student_id'])
            ->wherePivot('role', 'student')
            ->wherePivot('status', 'active')
            ->firstOrFail();

        $course = $academy->courses()->findOrFail((int) $data['course_id']);

        DB::transaction(function () use ($student, $course, $data): void {
            $existing = $course->enrollments()
                ->where('student_id', $student->id)
                ->first();

            $classroom = null;
            if (!empty($data['classroom_id'])) {
                $classroom = $course->classrooms()
                    ->where('status', 'active')
                    ->findOrFail((int) $data['classroom_id']);

                if ($classroom->capacity !== null) {
                    $current = $classroom->students()
                        ->wherePivot('status', 'active')
                        ->where('users.id', '<>', $student->id)
                        ->count();

                    abort_unless(
                        $current < (int) $classroom->capacity,
                        422,
                        'ظرفیت این کلاس تکمیل شده است.'
                    );
                }
            }

            $paidAmount = $course->isFree()
                ? 0
                : (float) ($data['paid_amount'] ?? $course->price);

            abort_unless(
                $course->isFree() || $paidAmount > 0,
                422,
                'برای دوره پولی، مبلغ ثبت‌نام باید بیشتر از صفر باشد.'
            );

            if ($existing?->classroom_id && $existing->classroom_id !== $classroom?->id) {
                $previousClassroom = \App\Models\Classroom::find($existing->classroom_id);

                $previousClassroom?->students()->updateExistingPivot(
                    $student->id,
                    ['status' => 'inactive']
                );
            }

            $course->enrollments()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'classroom_id' => $classroom?->id,
                    'status' => 'active',
                    'paid_amount' => $paidAmount,
                    'started_at' => $existing?->started_at ?? now(),
                    'completed_at' => null,
                ]
            );

            if ($classroom) {
                $classroom->students()->syncWithoutDetaching([
                    $student->id => [
                        'status' => 'active',
                        'enrolled_at' => now(),
                        'completed_at' => null,
                    ],
                ]);
            }
        });
    }

    private function scopedTeacherIds(Academy $academy, array $teacherIds): array
    {
        $teacherIds = array_values(array_unique(array_map('intval', $teacherIds)));

        if ($teacherIds === []) {
            return [];
        }

        $resolved = $academy->users()
            ->whereIn('users.id', $teacherIds)
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        abort_unless(
            count($resolved) === count($teacherIds),
            422,
            'یکی از مدرس‌های انتخاب‌شده عضو فعال این آموزشگاه نیست.'
        );

        return $resolved;
    }
}
