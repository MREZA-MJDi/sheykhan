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
        // Non-preview purchased source files are accessible only through the
        // order-scoped protected viewer; generic media routes never reveal originals.
        if (ProductFile::query()->where('media_id', $media->id)->where('is_preview', false)->exists()) {
            return false;
        }

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

        // Protected educational materials are view-only for every role. Authorized
        // staff use the same logged, watermarked viewer, never a raw file URL.
        if ($this->requiresProtectedDelivery($media)) {
            return false;
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
                || $model instanceof ExamAttempt
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
        if ($this->canAccessLearningResourceMedia($user, $media)) {
            return true;
        }

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
                $course = $model->section?->course;

                if (! $course) {
                    continue;
                }

                if ($user->hasRole('student')) {
                    if (app(StudentAccessService::class)->lesson($user, $model)) {
                        return true;
                    }
                    continue;
                }

                if ($user->hasRole('parent')) {
                    $canAccessChildLesson = $this->courseAccess($user, $course)
                        && $user->children()
                            ->where('users.status', 'active')
                            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
                            ->get()
                            ->contains(fn (User $child): bool => app(StudentAccessService::class)->lesson($child, $model));

                    if ($canAccessChildLesson) {
                        return true;
                    }
                    continue;
                }

                if ($user->hasRole('teacher')) {
                    if (app(TeacherAccessService::class)->canTeachCourse($user, $course)) {
                        return true;
                    }
                    continue;
                }

                if ($this->academyOwner($user, $course->academy) || $this->courseAccess($user, $course)) {
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
                    $canAccessChildWork = $model->course
                        && $this->courseAccess($user, $model->course)
                        && $user->children()
                            ->where('users.status', 'active')
                            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
                            ->get()
                            ->contains(fn (User $child): bool => app(StudentAccessService::class)->assignment($child, $model));

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
                    $canAccessChildWork = $assignment->course
                        && $this->courseAccess($user, $assignment->course)
                        && $user->children()
                            ->where('users.status', 'active')
                            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
                            ->get()
                            ->contains(fn (User $child): bool => (int) $model->student_id === (int) $child->id
                                && app(StudentAccessService::class)->assignment($child, $assignment));

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
                    $canAccessChildWork = $model->course
                        && $this->courseAccess($user, $model->course)
                        && $user->children()
                            ->where('users.status', 'active')
                            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
                            ->get()
                            ->contains(fn (User $child): bool => app(StudentAccessService::class)->exam($child, $model));

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
                $model->loadMissing('exam.course.academy', 'exam.classroom');
                $exam = $model->exam;

                if (! $exam || ! $exam->course) {
                    continue;
                }

                if ($user->hasRole('student')) {
                    if ((int) $model->student_id === (int) $user->id) {
                        $canViewAttempt = $model->submitted_at
                            ? app(StudentAccessService::class)->canViewExamResult($user, $exam)
                            : app(StudentAccessService::class)->exam($user, $exam);

                        if ($canViewAttempt) {
                            return true;
                        }
                    }

                    continue;
                }

                if ($user->hasRole('parent')) {
                    if (! $model->submitted_at) {
                        continue;
                    }

                    $child = $user->children()
                        ->whereKey($model->student_id)
                        ->where('users.status', 'active')
                        ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
                        ->first();

                    if (
                        $child
                        && app(CourseAccessService::class)->canAccess($user, $exam->course)
                        && app(StudentAccessService::class)->canViewExamResult($child, $exam)
                    ) {
                        return true;
                    }

                    continue;
                }

                if ($user->hasRole('teacher')) {
                    if (app(TeacherAccessService::class)->canManageExam($user, $exam)) {
                        return true;
                    }

                    continue;
                }

                if ($this->academyOwner($user, $exam->course->academy)) {
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

    /**
     * LearningResource stores its media_id directly rather than through the
     * polymorphic media_attachments table. Keep generic legacy media links
     * aligned with the exact same student/parent/teacher/owner entitlement rules.
     */
    private function canAccessLearningResourceMedia(User $user, Media $media): bool
    {
        $resources = LearningResource::query()
            ->where('media_id', $media->id)
            ->whereIn('status', ['active', 'published'])
            ->with([
                'academy:id,owner_id,status',
                'course:id,academy_id,status,published_at',
                'classroom:id,academy_id,course_id,status',
                'classroom.course:id,academy_id,status,published_at',
                'lesson.section.course:id,academy_id,status,published_at',
            ])
            ->orderBy('id')
            ->get();

        if ($resources->isEmpty()) {
            return false;
        }

        $parentChildren = $user->hasRole('parent')
            ? app(\App\Services\ParentLearningResourceService::class)->children($user)
            : collect();

        foreach ($resources as $resource) {
            $academy = $resource->academy;

            if (! $academy || $academy->status !== 'active') {
                continue;
            }

            $course = $resource->course
                ?? $resource->classroom?->course
                ?? $resource->lesson?->section?->course;

            if (
                ($resource->course && (int) $resource->course->academy_id !== (int) $resource->academy_id)
                || ($resource->classroom && (int) $resource->classroom->academy_id !== (int) $resource->academy_id)
                || ($resource->lesson?->section?->course
                    && (int) $resource->lesson->section->course->academy_id !== (int) $resource->academy_id)
                || ($resource->course && $resource->classroom?->course_id
                    && (int) $resource->classroom->course_id !== (int) $resource->course->id)
                || ($resource->course && $resource->lesson?->section?->course
                    && (int) $resource->course->id !== (int) $resource->lesson->section->course->id)
            ) {
                continue;
            }

            if ($user->hasRole('student')) {
                if (app(\App\Services\StudentLearningResourceService::class)->canAccess($user, $resource)) {
                    return true;
                }

                continue;
            }

            if ($user->hasRole('parent')) {
                $parentResources = app(\App\Services\ParentLearningResourceService::class);

                if ($parentChildren->contains(
                    fn (User $child): bool => $parentResources->canAccess($user, (int) $child->id, $resource)
                )) {
                    return true;
                }

                continue;
            }

            if ($this->academyOwner($user, $academy)) {
                return true;
            }

            if (! $user->hasRole('teacher')) {
                continue;
            }

            $teacherAccess = app(TeacherAccessService::class);

            if ($resource->classroom) {
                if ($teacherAccess->canManageClassroom($user, $resource->classroom)) {
                    return true;
                }

                continue;
            }

            if ($course && $teacherAccess->canTeachCourse($user, $course)) {
                return true;
            }

            // Academy-wide resources have no course/class/lesson scope. A teacher
            // may open those only with an active teacher membership in that academy.
            if (! $resource->course_id && ! $resource->classroom_id && ! $resource->lesson_id) {
                $isActiveTeacherMember = $user->academies()
                    ->whereKey($academy->id)
                    ->where('academies.status', 'active')
                    ->wherePivot('role', 'teacher')
                    ->wherePivot('status', 'active')
                    ->exists();

                if ($isActiveTeacherMember) {
                    return true;
                }
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
            return $liveClass->course
                && $this->courseAccess($user, $liveClass->course)
                && $user->children()
                    ->where('users.status', 'active')
                    ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
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

        if (! $classroom->course || $classroom->status !== 'active') {
            return false;
        }

        if ($user->hasRole('student')) {
            return app(StudentAccessService::class)->course($user, $classroom->course)
                && $user->classroomsAsStudent()
                    ->whereKey($classroom->id)
                    ->wherePivot('status', 'active')
                    ->exists();
        }

        if ($this->academyOwner($user, $classroom->academy)) {
            return true;
        }

        // Do not trust a stale classroom_teacher pivot after academy membership
        // was archived. TeacherAccessService checks both cohort and membership.
        if (
            $user->hasRole('teacher')
            && app(TeacherAccessService::class)->canManageClassroom($user, $classroom)
        ) {
            return true;
        }

        if (! $user->hasRole('parent')) {
            return false;
        }

        // Parent access requires membership in this academy and active child access
        // to this specific cohort.
        if (! $this->courseAccess($user, $classroom->course)) {
            return false;
        }

        return $user->children()
            ->where('users.status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->get()
            ->contains(function (User $child) use ($classroom): bool {
                return app(StudentAccessService::class)->course($child, $classroom->course)
                    && $child->classroomsAsStudent()
                        ->whereKey($classroom->id)
                        ->wherePivot('status', 'active')
                        ->exists();
            });
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
