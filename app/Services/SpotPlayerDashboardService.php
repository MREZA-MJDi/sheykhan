<?php

namespace App\Services;

use App\Models\Course;
use App\Models\SpotPlayerLicense;
use App\Models\SpotPlayerLicenseIssuance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class SpotPlayerDashboardService
{
    /**
     * Build a role-scoped SpotPlayer workspace. License secrets are deliberately
     * excluded from every query returned to a Blade view.
     */
    public function forUser(User $user, bool $owner): array
    {
        $courseQuery = fn (): Builder => $this->coursesFor($user, $owner);

        $courses = $courseQuery()
            ->with('academy:id,name')
            ->withCount([
                'spotplayerLicenses as active_licenses_count' => fn (Builder $query) => $query->where('status', 'active'),
                'spotplayerLicenses as total_licenses_count',
            ])
            ->orderBy('title')
            ->paginate(12, ['courses.*'], 'courses_page')
            ->withQueryString();

        $courseIds = fn () => $courseQuery()->select('courses.id');

        $licenseQuery = SpotPlayerLicense::query()
            ->select(['id', 'course_id', 'student_id', 'provider_course_id', 'provider_mode', 'status', 'issued_at', 'created_at'])
            ->whereIn('course_id', $courseIds())
            ->with([
                'student:id,name',
                'course:id,title,academy_id,spotplayer_course_id',
                'course.academy:id,name',
            ]);

        $search = mb_substr(trim(request()->query('q', '')), 0, 100);
        if ($search !== '') {
            $licenseQuery->where(function (Builder $query) use ($search): void {
                $query->whereHas('student', fn (Builder $student) => $student->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('course', fn (Builder $course) => $course->where('title', 'like', '%' . $search . '%'));
            });
        }

        $licenseStatus = request()->query('license_status');
        if (in_array($licenseStatus, ['active', 'revoked', 'inactive'], true)) {
            $licenseQuery->where('status', $licenseStatus);
        }

        $licenses = $licenseQuery
            ->latest('issued_at')
            ->paginate(12, ['spot_player_licenses.*'], 'licenses_page')
            ->withQueryString();

        $issuanceQuery = SpotPlayerLicenseIssuance::query()
            ->select(['id', 'student_id', 'course_id', 'provider_course_id', 'provider_mode', 'status', 'attempt_count', 'started_at', 'completed_at', 'created_at'])
            ->whereIn('course_id', $courseIds())
            ->whereIn('status', ['issuing', 'failed', 'review_required'])
            ->with(['student:id,name', 'course:id,title,academy_id', 'course.academy:id,name']);

        $issuances = $issuanceQuery
            ->latest('created_at')
            ->paginate(8, ['spot_player_license_issuances.*'], 'issuances_page')
            ->withQueryString();

        $courseTotal = $courseQuery()->count();
        $mappedCourses = $courseQuery()
            ->whereNotNull('spotplayer_course_id')
            ->where('spotplayer_course_id', '<>', '')
            ->count();

        $activeLicenses = SpotPlayerLicense::query()
            ->whereIn('course_id', $courseIds())
            ->where('status', 'active')
            ->count();

        $reviewRequired = SpotPlayerLicenseIssuance::query()
            ->whereIn('course_id', $courseIds())
            ->where('status', 'review_required')
            ->count();

        $issuing = SpotPlayerLicenseIssuance::query()
            ->whereIn('course_id', $courseIds())
            ->where('status', 'issuing')
            ->count();

        return [
            'courses' => $courses,
            'licenses' => $licenses,
            'issuances' => $issuances,
            'stats' => [
                'courses' => $courseTotal,
                'mapped_courses' => $mappedCourses,
                'active_licenses' => $activeLicenses,
                'review_required' => $reviewRequired,
                'issuing' => $issuing,
            ],
            'tab' => in_array(request()->query('tab'), ['overview', 'licenses', 'courses', 'guide', 'settings'], true)
                ? request()->query('tab')
                : 'overview',
            'search' => $search,
            'licenseStatus' => $licenseStatus,
        ];
    }

    private function coursesFor(User $user, bool $owner): Builder
    {
        return Course::query()->whereHas('academy', function (Builder $academy) use ($user, $owner): void {
            if ($owner) {
                $academy->where('owner_id', $user->id);

                return;
            }

            $academy
                ->where('status', 'active')
                ->whereHas('users', fn (Builder $membership) => $membership
                    ->whereKey($user->id)
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active'));
        })->when(! $owner, fn (Builder $query) => $query->whereHas(
            'teachers',
            fn (Builder $teacher) => $teacher->whereKey($user->id),
        ));
    }
}
