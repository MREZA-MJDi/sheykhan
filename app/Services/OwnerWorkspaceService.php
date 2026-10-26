<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademicGrade;
use App\Models\StudentOnboarding;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
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
            ->where('users.status', 'active')
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->paginate(25, ['*'], $pageName);

        $activeTeachers = $base('teacher', 'teachers_page');

        $archivedTeachers = $academy->users()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'archived')
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->paginate(25, ['*'], 'archived_teachers_page');

        // These are supporting modules. Keep the compatibility fallback, but do
        // not ask information_schema about every optional column on every request.
        // Production deployments should clear the application cache after migrations;
        // the short TTL also bounds stale capability data if deployment tooling misses it.
        $schemaCapabilities = $this->schemaCapabilities();
        $academicGradesAvailable = $schemaCapabilities['academic_grades'];
        $studentOnboardingAvailable = $schemaCapabilities['student_onboardings'];

        // Onboarding history eagerly loads requestedGrade. If a deployment has
        // the onboarding table but has not applied the academic-grade migration,
        // that relationship query would turn the entire Members page into a 500.
        // Treat the history panel as optional until both parts of its schema exist.
        $studentOnboardingHistoryAvailable = $studentOnboardingAvailable && $academicGradesAvailable;

        $onboardings = $studentOnboardingHistoryAvailable
            ? StudentOnboarding::query()
                ->where('academy_id', $academy->id)
                ->with([
                    'student:id,name,email,mobile',
                    'requestedGrade:id,title',
                    'admin:id,name',
                ])
                ->latest('activated_at')
                ->paginate(15, ['*'], 'onboardings_page')
            : new LengthAwarePaginator(
                [],
                0,
                15,
                LengthAwarePaginator::resolveCurrentPage('onboardings_page'),
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'onboardings_page',
                ],
            );

        return [
            'teachers' => $activeTeachers,
            'archivedTeachers' => $archivedTeachers,
            'students' => $base('student', 'students_page'),
            'parents' => $base('parent', 'parents_page'),
            'grades' => $academicGradesAvailable
                ? AcademicGrade::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get(['id', 'title'])
                : collect(),
            'academicGradesAvailable' => $academicGradesAvailable,
            'studentOnboardingAvailable' => $studentOnboardingAvailable,
            'studentOnboardingHistoryAvailable' => $studentOnboardingHistoryAvailable,
            'onboardings' => $onboardings,
        ];
    }

    /**
     * @return array{academic_grades: bool, student_onboardings: bool}
     */
    private function schemaCapabilities(): array
    {
        $detect = static function (): array {
            $academicGradesAvailable = Schema::hasTable('academic_grades')
                && Schema::hasColumn('academic_grades', 'title')
                && Schema::hasColumn('academic_grades', 'sort_order')
                && Schema::hasColumn('academic_grades', 'is_active');

            $studentOnboardingColumns = [
                'academy_id',
                'admin_id',
                'student_id',
                'entered_name',
                'requested_grade_id',
                'status',
                'activated_at',
            ];

            $studentOnboardingAvailable = Schema::hasTable('student_onboardings');
            if ($studentOnboardingAvailable) {
                foreach ($studentOnboardingColumns as $column) {
                    if (! Schema::hasColumn('student_onboardings', $column)) {
                        $studentOnboardingAvailable = false;
                        break;
                    }
                }
            }

            return [
                'academic_grades' => $academicGradesAvailable,
                'student_onboardings' => $studentOnboardingAvailable,
            ];
        };

        if (! app()->environment('production')) {
            return $detect();
        }

        return Cache::remember(
            'owner-workspace:schema-capabilities:v1',
            now()->addMinutes(5),
            $detect,
        );
    }

    public function courseTeacherOptions(User $owner, Academy $academy): array
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $teachers = $academy->users()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name']);

        $courses = $academy->courses()
            ->select('id', 'title', 'access_type', 'price')
            ->orderBy('title')
            ->get();

        $classrooms = $academy->classrooms()
            ->where('status', 'active')
            ->with('course:id,title')
            ->orderBy('title')
            ->get(['id', 'academy_id', 'course_id', 'title', 'code', 'capacity']);

        return [
            'teacherOptions' => $teachers,
            'courseOptions' => $courses,
            'classroomOptions' => $classrooms,
        ];
    }

    public function createTeacher(User $owner, Academy $academy, array $data): User
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $email = mb_strtolower(trim((string) $data['email']), 'UTF-8');

        try {
            return DB::transaction(function () use ($academy, $data, $email): User {
                $teacher = User::create([
                    'name' => trim((string) $data['name']),
                    'email' => $email,
                    'status' => 'active',
                    // User::casts() hashes raw passwords through the "hashed" cast.
                    'password' => $data['password'],
                ]);

                if (Schema::hasColumn('users', 'must_change_password')) {
                    $teacher->forceFill(['must_change_password' => true])->save();
                }

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
        } catch (UniqueConstraintViolationException $exception) {
            // The request's "unique" rule is only a preflight check. A concurrent
            // create can still lose the database race; turn only an email conflict
            // into a validation response and preserve unrelated integrity failures.
            if (User::withTrashed()->where('email', $email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'این ایمیل قبلاً استفاده شده است.',
                ]);
            }

            throw $exception;
        }
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
            $updated = DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $teacher->id)
                ->where('role', 'teacher')
                ->update([
                    'status' => 'archived',
                    'updated_at' => now(),
                ]);

            abort_unless($updated === 1, 404);

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
            $updated = DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $teacher->id)
                ->where('role', 'teacher')
                ->update([
                    'status' => 'active',
                    'joined_at' => $membership->pivot->joined_at ?? now(),
                    'updated_at' => now(),
                ]);

            abort_unless($updated === 1, 404);

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

        DB::transaction(function () use ($academy, $teacherId, $courseId): void {
            $course = $academy->courses()->whereKey($courseId)->lockForUpdate()->first();

            if (! $course) {
                throw ValidationException::withMessages([
                    'course_id' => 'دوره انتخاب‌شده متعلق به این آموزشگاه نیست.',
                ]);
            }

            $teacher = $academy->users()
                ->whereKey($teacherId)
                ->wherePivot('role', 'teacher')
                ->wherePivot('status', 'active')
                ->where('users.status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $teacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'این کاربر مدرس فعال این آموزشگاه نیست.',
                ]);
            }

            // Empty pivot attributes mean "attach if absent". Existing course_teacher
            // metadata (especially is_primary=true) is deliberately left unchanged.
            $course->teachers()->syncWithoutDetaching([$teacherId]);
        }, 3);
    }

    public function assignTeacherToClass(User $owner, Academy $academy, int $teacherId, int $classroomId): void
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        $initialClassroom = $academy->classrooms()->whereKey($classroomId)->first();

        if (!$initialClassroom) {
            throw ValidationException::withMessages([
                'classroom_id' => 'کلاس انتخاب‌شده متعلق به این آموزشگاه نیست.',
            ]);
        }

        $courseId = (int) $initialClassroom->course_id;

        DB::transaction(function () use ($academy, $teacherId, $classroomId, $courseId): void {
            // Use a consistent course -> classroom lock order for registration and reassignment.
            $course = $academy->courses()->whereKey($courseId)->lockForUpdate()->first();
            $classroom = $academy->classrooms()->whereKey($classroomId)->lockForUpdate()->first();

            if (!$course || !$classroom || (int) $classroom->course_id !== $courseId) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'کلاس در این آموزشگاه تغییر کرده است؛ صفحه را تازه‌سازی کنید.',
                ]);
            }

            if ($classroom->status !== 'active') {
                throw ValidationException::withMessages([
                    'classroom_id' => 'فقط می‌توان به کلاس فعال مدرس اختصاص داد.',
                ]);
            }

            $teacher = $academy->users()
                ->whereKey($teacherId)
                ->wherePivot('role', 'teacher')
                ->wherePivot('status', 'active')
                ->where('users.status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$teacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'مدرس انتخاب‌شده عضو فعال این آموزشگاه نیست.',
                ]);
            }

            // Keep the class-to-course-teacher relationship consistent atomically.
            // Do not rewrite existing course pivot metadata: a teacher may already
            // be the course's primary teacher before being assigned to this class.
            $course->teachers()->syncWithoutDetaching([$teacherId]);

            // The classroom form contract requires exactly one responsible teacher;
            // sync is intentionally replacement semantics for the classroom pivot only.
            $classroom->teachers()->sync([$teacherId]);
        }, 3);
    }
}
