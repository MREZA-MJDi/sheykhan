<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * «کار بعدی» بالای هر داشبورد: یک جمله‌ی ساده + یک دکمه‌ی بزرگ.
 *
 * منطق انتخاب اینجاست، نه در Blade. کنترلر هم می‌تواند خودش $nextAction بدهد
 * (NextAction یا هر شیء/آرایه‌ای با text, url, label)؛ ویوها از ?? استفاده می‌کنند.
 */
final class NextAction
{
    public function __construct(
        public string $text,
        public string $url,
        public string $label,
    ) {
    }

    public static function student($nextLesson, $liveClass, $pendingAssignment): self
    {
        if (! empty($nextLesson)) {
            return new self('درس بعدی تو آماده است؛ از همان‌جایی که مانده‌ای ادامه بده.', route('student.lessons.show', data_get($nextLesson, 'id')), 'ادامه یادگیری');
        }

        if ($liveClass) {
            return new self('برنامه کلاس‌های پیش‌رو را بررسی کن و برای جلسه بعدی آماده شو.', route('student.live-classes.index'), 'دیدن برنامه کلاس‌ها');
        }

        if ($pendingAssignment) {
            return new self('یک تکلیف در انتظار ارسال داری؛ قبل از پایان مهلت آن را بررسی کن.', route('student.assignments.show', data_get($pendingAssignment, 'id')), 'بررسی تکلیف');
        }

        return new self('از دوره‌های فعال خودت شروع کن و قدم بعدی مسیر یادگیری را بردار.', route('student.courses.index'), 'رفتن به دوره‌ها');
    }

    public static function teacher($user, array $metrics, Collection $todaySessions): ?self
    {
        $can = fn (string $permission): bool => (bool) $user?->hasPermission($permission);

        $joinable = $todaySessions->first(fn ($session) => ! empty(data_get($session, 'can_join')) && filled(data_get($session, 'meeting_url')));
        if ($joinable && $can('live_classes.view')) {
            return new self('جلسه «' . (data_get($joinable, 'title') ?: 'آموزشی') . '» الان قابل ورود است.', (string) data_get($joinable, 'meeting_url'), 'ورود به جلسه');
        }

        $assignments = max(0, (int) ($metrics['pendingAssignmentReviews'] ?? 0));
        if ($assignments > 0 && $can('assignments.view')) {
            return new self(PersianUi::digits($assignments) . ' تکلیف منتظر بررسی توست.', route('teacher.assignments.index'), 'بررسی تکالیف');
        }

        $exams = max(0, (int) ($metrics['pendingExamReviews'] ?? 0));
        if ($exams > 0 && $can('exams.view')) {
            return new self(PersianUi::digits($exams) . ' آزمون منتظر بررسی توست.', route('teacher.exams.index'), 'بررسی آزمون‌ها');
        }

        if (max(0, (int) ($metrics['activeClasses'] ?? 0)) > 0 && $can('attendance.view')) {
            return new self('حضور و غیاب کلاس‌هایت را ثبت کن.', route('teacher.attendance.index'), 'ثبت حضور و غیاب');
        }

        if ($can('classrooms.view')) {
            return new self('کلاس‌هایت را ببین و برای جلسه بعدی آماده شو.', route('teacher.classrooms.index'), 'دیدن کلاس‌ها');
        }

        return null;
    }

    public static function parentDashboard(Collection $pendingTasks, Collection $upcomingClasses, Collection $recentResults): self
    {
        if ($pendingTasks->isNotEmpty()) {
            return new self(PersianUi::digits($pendingTasks->count()) . ' تکلیف باز دارد؛ مهلت‌ها را بررسی کن.', '#parent-tasks-title', 'دیدن تکلیف‌های باز');
        }

        if ($upcomingClasses->isNotEmpty()) {
            return new self('جلسه‌ی آنلاین پیش‌رو داریم؛ زمان آن را ببین.', '#parent-live-title', 'دیدن جلسه‌ها');
        }

        if ($recentResults->isNotEmpty()) {
            return new self('نتیجه‌های تازه ثبت شده؛ یک نگاه به آن‌ها بینداز.', route('parent.results.index'), 'دیدن نتیجه‌ها');
        }

        return new self('اگر سؤالی داری، مستقیم به آموزشگاه پیام بده.', route('parent.messages.index'), 'ارسال پیام');
    }

    public static function owner(int $attentionCount, ?string $attentionUrl, $nextLiveClass, ?string $scheduleUrl): ?self
    {
        if ($attentionCount > 0 && filled($attentionUrl)) {
            return new self(PersianUi::digits($attentionCount) . ' مورد نیاز به پیگیری تو دارد.', (string) $attentionUrl, 'رفتن به موارد');
        }

        if ($nextLiveClass && filled($scheduleUrl)) {
            return new self('کلاس آنلاین بعدی ساعت ' . PersianUi::time(data_get($nextLiveClass, 'scheduled_at')) . ' شروع می‌شود.', (string) $scheduleUrl, 'دیدن برنامه کلاس‌ها');
        }

        return null;
    }
}
