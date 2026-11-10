<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademicGrade;
use App\Models\StudentOnboarding;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\StudentProfile;
use App\Models\ParentProfile;
use App\Models\User;
use App\Support\MobileNumber;
use Illuminate\Support\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\CursorPaginator;
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

    /**
     * Authorize workspace pages that aggregate across every academy owned by the user.
     */
    public function authorizeOwnedAcademies(User $owner, string $permission): Collection
    {
        abort_unless($owner->hasRole('academy-owner') && $owner->hasPermission($permission), 404);

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

    /**
     * Single authorization boundary for Owner workspace requests.
     * Keep academy ownership and permission checks consistent across modules.
     */
    public function canAccessAcademy(User $owner, Academy $academy, ?string $permission = null): bool
    {
        return $this->canManageAcademy($owner, $academy)
            && ($permission === null || $owner->hasPermission($permission));
    }

    public function authorizeAcademy(User $owner, Academy $academy, ?string $permission = null): void
    {
        abort_unless($this->canAccessAcademy($owner, $academy, $permission), 404);
    }

    /**
     * Update only editable profile fields for a member of this academy.
     * Passwords, private identity data, student numbers and family links are intentionally untouched.
     */
    public function updateAcademyMember(
        User $owner,
        Academy $academy,
        string $memberRole,
        User $member,
        array $data
    ): User {
        $permission = match ($memberRole) {
            'student' => 'onboarding.manage',
            'parent' => 'parents.manage',
            'teacher' => 'teachers.manage',
            default => null,
        };

        abort_unless($permission !== null, 404);
        $this->authorizeAcademy($owner, $academy, $permission);

        return DB::transaction(function () use ($owner, $academy, $memberRole, $member, $data): User {
            $membership = DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $member->id)
                ->where('role', $memberRole)
                ->lockForUpdate()
                ->first();

            $allowedStatuses = $memberRole === 'teacher' ? ['active', 'archived'] : ['active'];
            abort_unless($membership && in_array($membership->status, $allowedStatuses, true), 404);

            $lockedMember = User::query()->whereKey($member->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedMember->hasRole($memberRole), 404);

            $lockedMember->forceFill([
                'name' => trim((string) $data['name']),
                'mobile' => MobileNumber::normalize((string) $data['mobile']),
            ])->save();

            if ($memberRole === 'student') {
                $profile = StudentProfile::query()
                    ->where('user_id', $lockedMember->id)
                    ->lockForUpdate()
                    ->first();

                if (! $profile) {
                    throw ValidationException::withMessages([
                        'member' => 'برای این دانش‌آموز پروفایل تحصیلی ثبت نشده است؛ ویرایش امن انجام نشد.',
                    ]);
                }

                $profileData = [
                    'birth_date' => $data['birth_date'] ?? null,
                    'school_name' => filled($data['school_name'] ?? null) ? trim((string) $data['school_name']) : null,
                ];

                if (filled($data['grade_id'] ?? null)) {
                    $grade = AcademicGrade::query()
                        ->where('is_active', true)
                        ->whereKey((int) $data['grade_id'])
                        ->first();

                    if (! $grade) {
                        throw ValidationException::withMessages([
                            'grade_id' => 'پایهٔ انتخاب‌شده دیگر فعال نیست؛ صفحه را تازه کن.',
                        ]);
                    }

                    $profileData['grade_id'] = $grade->id;
                    $profileData['grade'] = $grade->title;
                }

                $profile->forceFill($profileData)->save();
            } elseif ($memberRole === 'parent') {
                $profile = ParentProfile::query()->firstOrNew(['user_id' => $lockedMember->id]);
                $profile->forceFill([
                    'occupation' => filled($data['occupation'] ?? null) ? trim((string) $data['occupation']) : null,
                    'relation_default' => $data['relation_default'] ?? $profile->relation_default,
                ])->save();
            } else {
                $profile = TeacherProfile::query()->firstOrNew(['user_id' => $lockedMember->id]);
                $profile->forceFill([
                    'specialization' => filled($data['specialization'] ?? null) ? trim((string) $data['specialization']) : null,
                    'experience_years' => $data['experience_years'] ?? null,
                    'is_public' => (bool) ($data['is_public'] ?? false),
                ])->save();
            }

            return $lockedMember->fresh();
        }, 3);
    }

    public function people(User $owner, Academy $academy): array
    {
        $this->authorizeAcademy($owner, $academy);

        $base = fn (string $role, string $pageName) => $academy->users()
            ->wherePivot('status', 'active')
            ->wherePivot('role', $role)
            ->where('users.status', 'active')
            ->select('users.id', 'users.name', 'users.mobile')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->paginate(10, ['*'], $pageName);

        $activeTeachers = $base('teacher', 'teachers_page');

        $archivedTeachers = $academy->users()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'archived')
            ->select('users.id', 'users.name', 'users.mobile')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->paginate(10, ['*'], 'archived_teachers_page');

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
                    'student:id,name,mobile',
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

    /**
     * Server-side member directory used by the owner panel API.
     *
     * Cursor pagination is used by default for non-null stable sort keys. Email
     * and membership joined_at can be null in legacy rows, so those sort modes
     * deliberately use length-aware page pagination instead.
     *
     * @return CursorPaginator|LengthAwarePaginator
     */
    public function peopleDirectory(User $owner, Academy $academy, string $tab, array $filters): CursorPaginator|LengthAwarePaginator
    {
        $this->authorizeAcademy($owner, $academy);

        $tabs = [
            'active-teachers' => ['teacher', 'active', true],
            'archived-teachers' => ['teacher', 'archived', false],
            'students' => ['student', 'active', true],
            'parents' => ['parent', 'active', true],
        ];

        if (! isset($tabs[$tab])) {
            throw ValidationException::withMessages([
                'role' => 'نوع فهرست اعضا معتبر نیست.',
            ]);
        }

        [$role, $membershipStatus, $activeAccountsOnly] = $tabs[$tab];
        $perPage = (int) ($filters['per_page'] ?? 25);
        $sort = (string) ($filters['sort'] ?? 'name');
        $direction = (string) ($filters['direction'] ?? 'asc');
        $term = trim((string) ($filters['q'] ?? ''));
        $mobileTerm = MobileNumber::normalize($term) ?: $term;

        $query = $academy->users()
            ->wherePivot('role', $role)
            ->wherePivot('status', $membershipStatus)
            ->select('users.id', 'users.name', 'users.mobile', 'users.status', 'users.created_at');

        if ($activeAccountsOnly) {
            $query->where('users.status', 'active');
        }

        if ($term !== '') {
            $query->where(function ($builder) use ($term, $mobileTerm): void {
                $builder->where('users.name', 'like', '%' . $term . '%')
                    ->orWhere('users.mobile', 'like', '%' . $mobileTerm . '%');

                if (preg_match('/^[0-9۰-۹٠-٩]{10}$/u', $term) === 1) {
                    $lookup = \App\Support\NationalIdLookup::make($term);
                    $builder->orWhereHas('studentProfile', fn ($profile) => $profile->where('national_id_lookup', $lookup));
                }
            });
        }

        $sortColumn = match ($sort) {
            'mobile' => 'users.mobile',
            'joined_at' => 'academy_user.joined_at',
            'created_at' => 'users.created_at',
            default => 'users.name',
        };

        // Always add a unique tie-breaker so ordering stays deterministic across pages.
        $query->orderBy($sortColumn, $direction)->orderBy('users.id', $direction);

        $queryString = array_intersect_key($filters, array_flip([
            'q', 'sort', 'direction', 'per_page', 'page', 'cursor',
        ]));

        // Cursor pagination cannot reliably traverse nullable order values on all
        // supported databases. Use cursor mode for the default non-null sort keys;
        // keep page mode for nullable mobile/joined_at sort options.
        $usesCursor = ! array_key_exists('page', $filters)
            && ! in_array($sort, ['mobile', 'joined_at'], true);

        $paginator = $usesCursor
            ? $query->cursorPaginate($perPage, ['*'], 'cursor')
            : $query->paginate($perPage, ['*'], 'page');

        return $paginator->appends($queryString);
    }

    // Form option lists are served by PeopleController::lookupOptions so
    // large academies never hydrate every teacher, course or classroom here.

    public function createTeacher(User $owner, Academy $academy, array $data): array
    {
        $this->authorizeAcademy($owner, $academy);

        $mobile = \App\Support\MobileNumber::normalize($data['mobile'] ?? null);
        $temporaryPassword = \Illuminate\Support\Str::password(16, true, true, true, false);

        try {
            return DB::transaction(function () use ($academy, $data, $mobile, $temporaryPassword): array {
                $teacher = User::create([
                    'name' => trim((string) $data['name']),
                    'email' => null,
                    'mobile' => $mobile,
                    'status' => 'active',
                    'password' => $temporaryPassword,
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
                    $teacher->id => ['role' => 'teacher', 'status' => 'active', 'joined_at' => now()],
                ]);

                return ['teacher' => $teacher, 'temporary_password' => $temporaryPassword];
            });
        } catch (UniqueConstraintViolationException $exception) {
            if (User::withTrashed()->where('mobile', $mobile)->exists()) {
                throw ValidationException::withMessages([
                    'mobile' => 'این شماره موبایل قبلاً استفاده شده است.',
                ]);
            }
            throw $exception;
        }
    }

    public function archiveTeacher(User $owner, Academy $academy, User $teacher): void
    {
        $this->authorizeAcademy($owner, $academy);

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
        $this->authorizeAcademy($owner, $academy);

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
        $this->authorizeAcademy($owner, $academy);

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
        $this->authorizeAcademy($owner, $academy);

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
        $this->authorizeAcademy($owner, $academy);

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
