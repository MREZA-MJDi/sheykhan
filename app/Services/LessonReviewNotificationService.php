<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Notifications\TeacherLessonSubmittedForReview;

final class LessonReviewNotificationService
{
    public function notifyOwner(User $teacher, Course $course, Lesson $lesson): void
    {
        $course->loadMissing('academy.owner');
        $owner = $course->academy?->owner;

        if (! $owner || (int) $owner->id === (int) $teacher->id || $owner->status !== 'active') {
            return;
        }

        $alreadyUnread = $owner->unreadNotifications()
            ->where('type', TeacherLessonSubmittedForReview::class)
            ->where('data->lesson_id', $lesson->id)
            ->exists();

        if ($alreadyUnread) {
            return;
        }

        $owner->notify(new TeacherLessonSubmittedForReview(
            courseId: (int) $course->id,
            lessonId: (int) $lesson->id,
            teacherId: (int) $teacher->id,
            courseTitle: (string) $course->title,
            lessonTitle: (string) $lesson->title,
        ));
    }
}
