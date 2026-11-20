<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class TeacherLessonSubmittedForReview extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $courseId,
        public readonly int $lessonId,
        public readonly int $teacherId,
        public readonly string $courseTitle,
        public readonly string $lessonTitle,
    ) {
    }

    /**
     * This is an in-app owner notification. Email delivery is deliberately
     * not enabled until the academy's notification preferences are wired.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => 'teacher_lesson_review_requested',
            'course_id' => $this->courseId,
            'lesson_id' => $this->lessonId,
            'teacher_id' => $this->teacherId,
            'course_title' => $this->courseTitle,
            'lesson_title' => $this->lessonTitle,
            'message' => 'یک درس جدید یا به‌روزرسانی‌شده برای بازبینی و تأیید ارسال شده است.',
            'url' => route('owner.courses.show', $this->courseId),
        ];
    }
}
