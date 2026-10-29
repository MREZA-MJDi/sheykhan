<?php

namespace App\Policies;

use App\Models\Academy;
use App\Models\Achievement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\LearningResource;
use App\Models\Media;
use App\Models\ProductFile;
use App\Models\User;
use App\Services\CourseAccessService;
use App\Services\StudentAccessService;
use App\Services\TeacherAccessService;

class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        if ($this->mustUseSpotPlayer($user, $media)) {
            return false;
        }

        // "public" is not an override for course/lesson/assessment permissions.
        // An accidentally public PDF must still stay behind its learning access rule.
        if ($this->requiresProtectedDelivery($media)) {
            return $this->canAccessProtectedMedia($user, $media);
        }

        if ($media->visibility === 'public' || $media->uploaded_by === $user->id) {
            return true;
        }

        return $this->canAccessProtectedMedia($user, $media);
    }

    public function download(User $user, Media $media): bool
    {
        // Purchased non-preview product sources must be delivered only through
        // the order-scoped viewer, even when the current account uploaded them.
        if (ProductFile::query()->where('media_id', $media->id)->where('is_preview', false)->exists()) {
            return false;
        }

        if ($this->mustUseSpotPlayer($user, $media)) {
            return false;
        }

        $protectedLearningMedia = $this->requiresProtectedDelivery($media);

        // Students download course materials only through the explicit resource
        // route, which checks LearningResource.downloadable and the free-course
        // entitlement. This generic route must never bypass that switch.
        if ($user->hasRole('student') && $protectedLearningMedia) {
            return false;
        }

        if ($protectedLearningMedia) {
            return $this->canAccessProtectedMedia($user, $media)
                || $media->uploaded_by === $user->id;
        }

        if ($media->visibility === 'public') {
            return true;
        }

        if ($user->hasRole('student')) {
            return false;
        }

        if ($media->uploaded_by === $user->id) {
            return true;
        }

        return $this->canAccessProtectedMedia($user, $media);
    }

    /**
     * Learning files must use authenticated routes that enforce course/class
     * access. Public images (course covers, public profiles) remain public;
     * public lesson attachments, PDFs, assessment files, resources, and
     * recordings are treated as protected regardless of a mistaken visibility.
     */
    public function requiresProtectedDelivery(Media $media): bool
    {
        // A purchased product source is never a public file. Even if a product
        // manager accidentally marks the underlying media public, non-preview
        // files are delivered through the authenticated, watermarked viewer.
        if (ProductFile::query()->where('media_id', $media->id)->where('is_preview', false)->exists()) {
            return true;
        }

        if (
            LearningResource::query()->where('media_id', $media->id)->exists()
            || LiveClass::query()->where('recording_media_id', $media->id)->exists()
        ) {
            return true;
        }

        foreach ($media->attachments()->with('mediable')->get() as $attachment) {
            $model = $attachment->mediable;

            if (
                $model instanceof Lesson
                || $model instanceof Classroom
                || $model instanceof Assignment
                || $model instanceof AssignmentSubmission
                || $model instanceof Exam
                || $model instanceof LiveClass
            ) {
                return true;
            }

            if ($model instanceof Course && !str_starts_with((string) $media->mime_type, 'image/')) {
                return true;
            }
        }

        return false;
    }

    public function canViewThroughSpotPlayer(User $user, Media $media): bool
    {
        return $this->mustUseSpotPlayer($user, $media)
            && $this->canAccessProtectedMedia($user, $media);
    }

    private function mustUseSpotPlayer(User $user, Media $media): bool
    {
        return ($user->hasRole('student') || $user->hasRole('parent'))
            && app(\App\Services\SpotPlayerMediaDeliveryService::class)->requiresProtectedPlayer($media);
    }

    private function canAccessProtectedMedia(User $user, Media $media): bool
    {
        if (Achievement::query()
            ->where('media_id', $media->id)
            ->where('student_id', $user->id)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->exists()
        ) {
            return true;
        }

        if (LiveClass::query()
            ->where('recording_media_id', $media->id)
            ->whereIn('status', ['completed', 'published'])
            ->whereNotNull('recording_released_at')
            ->where('recording_released_at', '<=', now())
            ->with(['course', 'classroom'])
            ->get()
            ->contains(function (LiveClass $liveClass) use ($user, $media): bool {
                if (!$liveClass->isRecordingAvailable()) {
                    return false;
                }

                return $this->canAccessLiveClassRecording($user, $liveClass);
            })
        ) {
            return true;
        }

        foreach ($media->attachments()->with('mediable')->get() as $attachment) {
            $model = $attachment->mediable;

            if ($model instanceof Academy && $this->academyMember($user, $model)) {
                return true;
            }

            if ($model instanceof Course && $this->courseAccess($user, $model)) {
                return true;
            }

            if ($model instanceof Lesson) {
                $model->loadMissing('section.course');

                if ($model->section?->course && $this->courseAccess($user, $model->section->course)) {
                    return true;
                }
            }

            if ($model instanceof Classroom && $this->classroomAccess($user, $model)) {
                return true;
            }

            if ($model instanceof Assignment) {
                $model->loadMissing(['course', 'classroom']);

                // Direct attachment URLs must use the same cohort policy as the
                // assignment page; a shared public course is not a student roster.
                if ($user->hasRole('student')) {
                    if (app(StudentAccessService::class)->assignment($user, $model)) {
                        return true;
                    }

                    continue;
                }

                if ($user->hasRole('teacher')) {
                    if (app(TeacherAccessService::class)->canManageAssignment($user, $model)) {
                        return true;
                    }

                    continue;
                }

                if ($user->hasRole('parent')) {
                    $canAccessChildWork = $user->children()->get()->contains(
                        fn (User $child): bool => app(StudentAccessService::class)->assignment($child, $model)
                    );

                    if ($canAccessChildWork) {
                        return true;
                    }

                    continue;
                }

                if ($model->course && (
                    $this->academyOwner($user, $model->course->academy)
                    || $this->courseAccess($user, $model->course)
                )) {
                    return true;
                }
            }

            if ($model instanceof AssignmentSubmission) {
                $model->loadMissing(['assignment.course.academy', 'assignment.classroom']);

                $assignment = $model->assignment;
                if (! $assignment) {
                    continue;
                }

                if ($user->hasRole('student')) {
                    if (
                        (int) $model->student_id === (int) $user->id
                        && app(StudentAccessService::class)->assignment($user, $assignment)
                    ) {
                        return true;
                    }

                    continue;
                }

                if ($user->hasRole('teacher')) {
                    if (app(TeacherAccessService::class)->canManageAssignment($user, $assignment)) {
                        return true;
                    }

                    continue;
                }

                if ($user->hasRole('parent')) {
                    $canAccessChildWork = $user->children()->get()->contains(
                        fn (User $child): bool => (int) $model->student_id === (int) $child->id
                            && app(StudentAccessService::class)->assignment($child, $assignment)
                    );

                    if ($canAccessChildWork) {
                        return true;
                    }

                    continue;
                }

                if ($model->graded_by === $user->id) {
                    return true;
                }

                if ($assignment->course && (
                    $this->academyOwner($user, $assignment->course->academy)
                    || $this->courseAccess($user, $assignment->course)
                )) {
                    return true;
                }
            }

            if ($model instanceof Exam) {
                $model->loadMissing(['course', 'classroom']);

                // Assessment attachments follow the exact class/teacher rule.
                if ($user->hasRole('student')) {
                    if (app(StudentAccessService::class)->exam($user, $model)) {
                        return true;
                    }

                    continue;
                }

                if ($user->hasRole('teacher')) {
                    if (app(TeacherAccessService::class)->canManageExam($user, $model)) {
                        return true;
                    }

                    continue;
                }

                if ($user->hasRole('parent')) {
                    $canAccessChildWork = $user->children()->get()->contains(
                        fn (User $child): bool => app(StudentAccessService::class)->exam($child, $model)
                    );

                    if ($canAccessChildWork) {
                        return true;
                    }

                    continue;
                }

                if ($model->course && (
                    $this->academyOwner($user, $model->course->academy)
                    || $this->courseAccess($user, $model->course)
                )) {
                    return true;
                }
            }

            if ($model instanceof ExamAttempt) {
                if (
                    $model->student_id === $user->id
                    || $this->isParentOf($user, $model->student_id)
                ) {
                    return true;
                }
            }

            if ($model instanceof LiveClass) {
                $model->loadMissing(['course', 'classroom']);

                if (
                    $model->recording_media_id !== $media->id
                    || !$model->isRecordingAvailable()
                ) {
                    continue;
                }

                if ($this->canAccessLiveClassRecording($user, $model)) {
                    return true;
                }

                continue;
            }
        }

        return false;
    }

    private function academyMember(User $user, Academy $academy): bool
    {
        return $academy->owner_id === $user->id
            || $user->academies()->whereKey($academy->id)->wherePivot('status', 'active')->exists();
    }

    private function academyOwner(User $user, Academy $academy): bool
    {
        return $academy->owner_id === $user->id
            || $user->academies()
                ->whereKey($academy->id)
                ->wherePivot('role', 'owner')
                ->wherePivot('status', 'active')
                ->exists();
    }

    /**
     * Recordings use the exact teacher-cohort enrollment rule enforced by
     * StudentAccessService for session access. Course access plus a classroom
     * pivot alone is not enough when the same course has multiple cohorts.
     */
    private function canAccessLiveClassRecording(User $user, LiveClass $liveClass): bool
    {
        $liveClass->loadMissing(['course', 'classroom']);

        if (! $liveClass->isRecordingAvailable()) {
            return false;
        }

        if ($user->hasRole('student')) {
            return app(StudentAccessService::class)->liveClass($user, $liveClass);
        }

        if ($user->hasRole('parent')) {
            return $user->children()
                ->where('users.status', 'active')
                ->get()
                ->contains(fn (User $child): bool => app(StudentAccessService::class)->liveClass($child, $liveClass));
        }

        if ($liveClass->classroom) {
            return $this->classroomAccess($user, $liveClass->classroom);
        }

        return $liveClass->course && $this->courseAccess($user, $liveClass->course);
    }

    private function classroomAccess(User $user, Classroom $classroom): bool
    {
        $classroom->loadMissing(['academy', 'course']);

        if ($user->hasRole('student')) {
            if (!$classroom->course) {
                return false;
            }

            return $this->courseAccess($user, $classroom->course)
                && $user->classroomsAsStudent()
                    ->whereKey($classroom->id)
                    ->wherePivot('status', 'active')
                    ->exists();
        }

        if ($this->academyOwner($user, $classroom->academy)) {
            return true;
        }

        if ($user->classroomsAsTeacher()->whereKey($classroom->id)->exists()) {
            return true;
        }

        if (
            $user->classroomsAsStudent()
                ->whereKey($classroom->id)
                ->wherePivot('status', 'active')
                ->exists()
        ) {
            return true;
        }

        return $user->children()
            ->with('classroomsAsStudent')
            ->get()
            ->contains(
                fn (User $child) => $child->classroomsAsStudent->contains(
                    fn (Classroom $childClassroom) => $childClassroom->id === $classroom->id
                )
            );
    }

    private function courseAccess(User $user, Course $course): bool
    {
        if ($user->hasRole('student')) {
            return app(StudentAccessService::class)->course($user, $course);
        }

        return app(CourseAccessService::class)->canAccess($user, $course);
    }

    private function isParentOf(User $user, int $studentId): bool
    {
        return $user->children()->whereKey($studentId)->exists();
    }
}
