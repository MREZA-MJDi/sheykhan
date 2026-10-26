<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\OwnerReportService;
use App\Services\OwnerWorkspaceService;
use App\Services\PanelExportService;
use App\Services\StudentAssignmentService;
use App\Services\StudentAccessService;
use App\Services\StudentExamService;
use App\Services\StudentResultService;
use App\Services\TeacherAccessService;
use App\Services\TeacherWorkspaceService;
use App\Support\PersianUi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PanelExportController extends Controller
{
    public function teacherStudents(Request $request, TeacherWorkspaceService $workspace, PanelExportService $exports)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['name', 'progress', 'assignments', 'exams'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'format' => ['nullable', Rule::in(['csv', 'word'])],
        ]);
        $teacher = $request->user();
        $rows = $workspace->studentsPaginated(
            $teacher,
            100000,
            $filters['sort'] ?? 'name',
            $filters['direction'] ?? 'asc',
            $filters['q'] ?? '',
        )->getCollection()->map(fn ($student): array => [
            $student->name,
            $student->email,
            $student->studentProfile?->student_number,
            $student->studentProfile?->gradeRelation?->title ?: $student->studentProfile?->grade,
            (int) $student->progress_items_count,
            (int) $student->assignment_submissions_count,
            (int) $student->exam_attempts_count,
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'teacher-student-roster',
            'فهرست دانش‌آموزان مدرس',
            ['نام دانش‌آموز', 'ایمیل', 'شماره دانش‌آموزی', 'پایه', 'رکوردهای پیشرفت درس', 'ارسال تکلیف', 'تلاش آزمون'],
            $rows,
            ['مدرس' => $teacher->name, 'تعداد رکورد خروجی' => $rows->count(), 'جست‌وجو' => $filters['q'] ?? 'همه'],
        );
    }

    public function teacherAssignmentSubmissions(
        Request $request,
        Assignment $assignment,
        TeacherAccessService $access,
        PanelExportService $exports
    ) {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        abort_unless($request->user()->hasRole('teacher') && (int) $assignment->teacher_id === (int) $request->user()->id
            && $access->canManageAssignment($request->user(), $assignment), 403);

        $submissions = $assignment->submissions()
            ->with(['student:id,name,email', 'media:id,original_name,extension,size'])
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->orderBy('student_id')
            ->get();

        $rows = $submissions->map(fn (AssignmentSubmission $submission): array => [
            $submission->student?->name,
            $submission->student?->email,
            $submission->submitted_at?->format('Y-m-d H:i'),
            $submission->graded_at ? 'تصحیح‌شده' : 'در انتظار تصحیح',
            $submission->score,
            $assignment->max_score,
            $submission->feedback,
            $submission->content,
            $submission->media->pluck('original_name')->implode('، '),
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'assignment-submissions-' . $assignment->id,
            'خروجی پاسخ‌های تکلیف: ' . $assignment->title,
            ['دانش‌آموز', 'ایمیل', 'زمان تحویل', 'وضعیت', 'نمره', 'نمره کل', 'بازخورد', 'پاسخ متنی', 'فایل‌های پیوست'],
            $rows,
            ['تکلیف' => $assignment->title, 'تعداد پاسخ‌ها' => $rows->count(), 'حداکثر نمره' => $assignment->max_score],
        );
    }

    public function teacherExamPaper(
        Request $request,
        Exam $exam,
        TeacherAccessService $access,
        PanelExportService $exports
    ) {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        abort_unless($request->user()->hasRole('teacher') && $access->canManageExam($request->user(), $exam), 403);

        $exam->load(['course:id,title', 'classroom:id,title', 'questions.media']);
        $questions = $exam->questions->sortBy('sort_order')->values();
        $rows = $questions->map(function ($question, int $index): array {
            $options = is_array($question->options) ? implode(' | ', array_map(
                static fn ($option) => is_array($option) ? (string) ($option['text'] ?? $option['label'] ?? '') : (string) $option,
                $question->options
            )) : '';

            return [
                $index + 1,
                match ($question->type) { 'single' => 'چهارگزینه‌ای', 'multiple', 'checkbox' => 'چندپاسخی', default => 'تشریحی' },
                $question->question,
                $options,
                $question->score,
                $question->media->where('pivot.collection', 'exam-question-prompt')->pluck('original_name')->implode('، '),
            ];
        });

        return $exports->download(
            $filters['format'] ?? 'word',
            'exam-paper-' . $exam->id,
            'برگهٔ آزمون: ' . $exam->title,
            ['شماره', 'نوع سؤال', 'صورت سؤال', 'گزینه‌ها', 'نمره', 'فایل‌های صورت سؤال'],
            $rows,
            [
                'دوره' => $exam->course?->title,
                'کلاس' => $exam->classroom?->title ?? 'همهٔ کلاس‌های مجاز دوره',
                'مدت (دقیقه)' => $exam->duration_minutes,
                'تعداد سؤال' => $questions->count(),
                'نمره کل' => $questions->sum(fn ($question) => (float) $question->score),
                'شروع' => $exam->starts_at?->format('Y-m-d H:i'),
                'پایان' => $exam->ends_at?->format('Y-m-d H:i'),
            ],
        );
    }

    public function teacherExamAttempts(
        Request $request,
        Exam $exam,
        TeacherAccessService $access,
        PanelExportService $exports
    ) {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        abort_unless($request->user()->hasRole('teacher') && $access->canManageExam($request->user(), $exam), 403);

        $attempts = $exam->attempts()
            ->with(['student:id,name,email', 'answers.question:id,exam_id,question,score', 'answers.media'])
            ->orderByDesc('submitted_at')
            ->orderBy('student_id')
            ->orderBy('attempt_number')
            ->get();
        $rows = $attempts->map(function (ExamAttempt $attempt): array {
            $answerDetails = $attempt->answers->map(function ($answer): string {
                $questionText = $answer->question?->question ?? 'سؤال حذف‌شده';
                $answerText = $answer->answer === null || $answer->answer === '' ? 'بدون پاسخ متنی' : (string) $answer->answer;
                $files = $answer->media->pluck('original_name')->implode('، ');
                $score = $answer->score === null ? 'در انتظار تصحیح' : (string) $answer->score;
                return $questionText . ' | پاسخ: ' . $answerText . ' | فایل: ' . ($files ?: 'ندارد') . ' | نمره: ' . $score;
            })->implode("\n");

            return [
                $attempt->student?->name,
                $attempt->student?->email,
                $attempt->attempt_number,
                match ($attempt->status) {
                    'graded' => 'تصحیح‌شده',
                    'pending_review', 'needs_review' => 'نیازمند بررسی مدرس',
                    'submitted' => 'ارسال‌شده',
                    'in_progress' => 'در حال پاسخ‌گویی',
                    default => $attempt->status,
                },
                $attempt->submitted_at?->format('Y-m-d H:i'),
                $attempt->score,
                $answerDetails,
            ];
        });

        return $exports->download(
            $filters['format'] ?? 'csv',
            'exam-attempts-' . $exam->id,
            'نتایج آزمون: ' . $exam->title,
            ['دانش‌آموز', 'ایمیل', 'شماره تلاش', 'وضعیت', 'زمان تحویل', 'نمره', 'جزئیات پاسخ‌ها و فایل‌ها'],
            $rows,
            ['آزمون' => $exam->title, 'تعداد تلاش‌ها' => $rows->count(), 'ارسال‌شده' => $attempts->whereNotNull('submitted_at')->count()],
        );
    }

    public function teacherAttendanceSheet(
        Request $request,
        \App\Models\Classroom $classroom,
        TeacherWorkspaceService $workspace,
        PanelExportService $exports
    ) {
        $filters = $request->validate([
            'attendance_date' => ['nullable', 'date'],
            'format' => ['nullable', Rule::in(['csv', 'word'])],
        ]);
        $classroom = $workspace->classroomWithStudents($request->user(), (int) $classroom->id);
        $date = PersianUi::normalizeDate($filters['attendance_date'] ?? null) ?? today()->toDateString();
        $records = Attendance::query()->where('classroom_id', $classroom->id)->whereDate('attendance_date', $date)
            ->get(['student_id', 'status', 'scheduled_minutes', 'attended_minutes', 'note'])->keyBy('student_id');
        $labels = ['present' => 'حاضر', 'absent' => 'غایب', 'late' => 'با تأخیر', 'excused' => 'موجه'];
        $students = $classroom->students;
        $rows = $students->map(function ($student) use ($records, $labels): array {
            $record = $records->get($student->id);
            return [
                $student->name,
                $student->email,
                $record ? ($labels[$record->status] ?? $record->status) : 'ثبت نشده',
                $record?->attended_minutes,
                $record?->scheduled_minutes,
                $record?->note,
            ];
        });

        return $exports->download(
            $filters['format'] ?? 'csv',
            'attendance-' . $classroom->id . '-' . $date,
            'برگهٔ حضور و غیاب: ' . $classroom->title,
            ['نام دانش‌آموز', 'ایمیل', 'وضعیت', 'مدت حضور (دقیقه)', 'مدت برنامه (دقیقه)', 'توضیح'],
            $rows,
            ['کلاس' => $classroom->title, 'تاریخ' => $date, 'تعداد دانش‌آموزان' => $students->count(), 'ثبت‌شده' => $records->count()],
        );
    }

    public function studentAssignments(Request $request, StudentAssignmentService $assignments, PanelExportService $exports)
    {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        $student = $request->user();
        $items = $assignments->query($student)->get();
        $rows = $items->map(function ($assignment): array {
            $submission = $assignment->submissions->first();
            return [
                $assignment->title,
                $assignment->course?->title,
                $assignment->due_at?->format('Y-m-d H:i'),
                $submission?->submitted_at?->format('Y-m-d H:i'),
                $submission?->graded_at ? 'تصحیح‌شده' : ($submission ? 'در انتظار تصحیح' : 'ارسال نشده'),
                $submission?->score,
                $assignment->max_score,
                $assignment->media->pluck('original_name')->implode('، '),
            ];
        });

        return $exports->download(
            $filters['format'] ?? 'csv',
            'student-assignments',
            'فهرست تکالیف دانش‌آموز',
            ['تکلیف', 'دوره', 'مهلت', 'زمان ارسال', 'وضعیت', 'نمره', 'نمره کل', 'فایل‌های راهنما'],
            $rows,
            ['دانش‌آموز' => $student->name, 'تعداد تکالیف قابل مشاهده' => $rows->count()],
        );
    }

    public function studentResults(Request $request, StudentResultService $results, PanelExportService $exports)
    {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        $student = $request->user();
        $items = $results->paginate($student, 100000)->getCollection();
        $rows = $items->map(fn ($row): array => [
            $row->title,
            $row->result_type === 'exam' ? 'آزمون' : 'تکلیف',
            $row->occurred_at,
            $row->status_label,
            $row->score,
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'student-results',
            'کارنامهٔ دانش‌آموز',
            ['عنوان', 'نوع', 'تاریخ ثبت', 'وضعیت', 'نمره'],
            $rows,
            ['دانش‌آموز' => $student->name, 'تعداد نتیجه‌ها' => $rows->count()],
        );
    }

    public function studentAttendance(Request $request, PanelExportService $exports)
    {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        $student = $request->user();
        $classroomIds = $student->classroomsAsStudent()->wherePivot('status', 'active')->pluck('classrooms.id');
        $items = Attendance::query()->where('student_id', $student->id)->whereIn('classroom_id', $classroomIds)
            ->with('classroom:id,title,code')->orderByDesc('attendance_date')->orderByDesc('id')->get();
        $labels = ['present' => 'حاضر', 'absent' => 'غایب', 'late' => 'با تأخیر', 'excused' => 'موجه'];
        $rows = $items->map(fn (Attendance $row): array => [
            $row->attendance_date?->format('Y-m-d') ?? $row->attendance_date,
            $row->classroom?->title,
            $labels[$row->status] ?? $row->status,
            $row->attended_minutes,
            $row->scheduled_minutes,
            $row->note,
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'student-attendance',
            'سوابق حضور و غیاب دانش‌آموز',
            ['تاریخ', 'کلاس', 'وضعیت', 'مدت حضور (دقیقه)', 'مدت برنامه (دقیقه)', 'توضیح'],
            $rows,
            ['دانش‌آموز' => $student->name, 'تعداد رکوردها' => $rows->count()],
        );
    }

    public function studentExamResult(Request $request, Exam $exam, StudentExamService $exams, PanelExportService $exports)
    {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        $exam = $exams->findForResults($request->user(), $exam);
        $exam->loadMissing(['attempts.answers.question', 'attempts.answers.media']);
        $attempts = $exam->attempts;
        $rows = $attempts->map(function (ExamAttempt $attempt): array {
            $answerDetails = $attempt->answers->map(function ($answer): string {
                $questionText = $answer->question?->question ?? 'سؤال حذف‌شده';
                $answerText = $answer->answer === null || $answer->answer === '' ? 'بدون پاسخ متنی' : (string) $answer->answer;
                $files = $answer->media->pluck('original_name')->implode('، ');
                return $questionText . ' | پاسخ: ' . $answerText . ' | فایل: ' . ($files ?: 'ندارد') . ' | نمره: ' . ($answer->score ?? 'در انتظار تصحیح');
            })->implode("\n");

            return [
                $attempt->attempt_number,
                $attempt->submitted_at?->format('Y-m-d H:i'),
                match ($attempt->status) {
                    'graded' => 'تصحیح‌شده',
                    'needs_review' => 'در انتظار بررسی تشریحی',
                    'pending_review' => 'در صف تصحیح مدرس',
                    'submitted' => 'ارسال‌شده',
                    default => $attempt->status,
                },
                $attempt->score,
                $answerDetails,
            ];
        });

        return $exports->download(
            $filters['format'] ?? 'word',
            'student-exam-result-' . $exam->id,
            'نتیجهٔ آزمون: ' . $exam->title,
            ['شماره تلاش', 'زمان تحویل', 'وضعیت', 'نمره', 'جزئیات پاسخ‌ها و فایل‌ها'],
            $rows,
            ['آزمون' => $exam->title, 'دوره' => $exam->course?->title, 'تعداد تلاش‌های قابل مشاهده' => $rows->count()],
        );
    }

    public function ownerPeople(
        Request $request,
        Academy $academy,
        OwnerWorkspaceService $workspace,
        PanelExportService $exports
    ) {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);
        $items = $academy->users()
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->select('users.id', 'users.name', 'users.email', 'users.mobile')
            ->orderBy('academy_user.role')->orderBy('users.name')->get();
        $memberships = \Illuminate\Support\Facades\DB::table('academy_user')
            ->where('academy_id', $academy->id)->where('status', 'active')
            ->get(['user_id', 'role'])->keyBy('user_id');
        $roles = ['academy-owner' => 'مالک آموزشگاه', 'teacher' => 'مدرس', 'student' => 'دانش‌آموز', 'parent' => 'والد', 'seo-specialist' => 'متخصص SEO'];
        $rows = $items->map(fn ($user): array => [
            $user->name, $user->email, $user->mobile,
            $roles[$memberships->get($user->id)?->role ?? ''] ?? ($memberships->get($user->id)?->role ?? 'عضو'),
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'academy-members-' . $academy->id,
            'فهرست اعضای آموزشگاه: ' . $academy->name,
            ['نام', 'ایمیل', 'موبایل', 'نقش'],
            $rows,
            ['آموزشگاه' => $academy->name, 'تعداد اعضای فعال' => $rows->count()],
        );
    }

    public function ownerAttendance(
        Request $request,
        Academy $academy,
        OwnerWorkspaceService $workspace,
        PanelExportService $exports
    ) {
        abort_unless($workspace->canManageAcademy($request->user(), $academy)
            && $request->user()->hasPermission('attendance.view'), 403);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')->where('academy_id', $academy->id)],
            'attendance_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['present', 'late', 'absent', 'excused'])],
            'format' => ['nullable', Rule::in(['csv', 'word'])],
        ]);
        $query = Attendance::query()->whereHas('classroom', fn ($classrooms) => $classrooms->where('academy_id', $academy->id))
            ->with(['student:id,name,email', 'classroom:id,academy_id,title', 'classroom.course:id,title', 'marker:id,name']);
        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $query->whereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%')->orWhere('email', 'like', '%' . $term . '%'));
        }
        if (filled($filters['classroom_id'] ?? null)) $query->where('classroom_id', (int) $filters['classroom_id']);
        if (filled($filters['attendance_date'] ?? null)) $query->whereDate('attendance_date', $filters['attendance_date']);
        if (filled($filters['status'] ?? null)) $query->where('status', $filters['status']);
        $items = $query->orderByDesc('attendance_date')->orderBy('classroom_id')->orderBy('student_id')->get();
        $labels = ['present' => 'حاضر', 'late' => 'با تأخیر', 'absent' => 'غایب', 'excused' => 'موجه'];
        $rows = $items->map(fn (Attendance $row): array => [
            $row->student?->name, $row->student?->email, $row->classroom?->title, $row->classroom?->course?->title,
            $row->attendance_date?->format('Y-m-d') ?? $row->attendance_date,
            $labels[$row->status] ?? $row->status, $row->attended_minutes, $row->scheduled_minutes, $row->note, $row->marker?->name,
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'academy-attendance-' . $academy->id,
            'گزارش حضور و غیاب: ' . $academy->name,
            ['دانش‌آموز', 'ایمیل', 'کلاس', 'دوره', 'تاریخ', 'وضعیت', 'مدت حضور', 'مدت برنامه', 'یادداشت', 'ثبت‌کننده'],
            $rows,
            ['آموزشگاه' => $academy->name, 'تعداد رکوردهای منطبق' => $rows->count(), 'فیلتر تاریخ' => $filters['attendance_date'] ?? 'همه', 'فیلتر وضعیت' => isset($filters['status']) ? ($labels[$filters['status']] ?? $filters['status']) : 'همه'],
        );
    }

    public function ownerExamAttempts(
        Request $request,
        Academy $academy,
        int $exam,
        OwnerWorkspaceService $workspace,
        PanelExportService $exports
    ) {
        abort_unless($workspace->canManageAcademy($request->user(), $academy)
            && $request->user()->hasPermission('exams.view'), 403);
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);

        $record = Exam::query()
            ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
            ->with(['course:id,title', 'classroom:id,title'])
            ->findOrFail($exam);
        $attempts = $record->attempts()
            ->with(['student:id,name,email', 'answers.question:id,exam_id,question,score', 'answers.media'])
            ->orderByDesc('submitted_at')
            ->orderBy('student_id')
            ->orderBy('attempt_number')
            ->get();

        $rows = $attempts->map(function (ExamAttempt $attempt): array {
            $answerDetails = $attempt->answers->map(function ($answer): string {
                $question = $answer->question?->question ?? 'سؤال حذف‌شده';
                $answerText = $answer->answer === null || $answer->answer === '' ? 'بدون پاسخ متنی' : (string) $answer->answer;
                $files = $answer->media->pluck('original_name')->implode('، ');

                return $question . ' | پاسخ: ' . $answerText . ' | فایل: ' . ($files ?: 'ندارد')
                    . ' | نمره: ' . ($answer->score ?? 'در انتظار تصحیح');
            })->implode("\n");

            return [
                $attempt->student?->name,
                $attempt->student?->email,
                $attempt->attempt_number,
                match ($attempt->status) {
                    'graded' => 'تصحیح‌شده',
                    'pending_review', 'needs_review' => 'نیازمند بررسی مدرس',
                    'submitted' => 'ارسال‌شده',
                    'in_progress' => 'در حال پاسخ‌گویی',
                    default => $attempt->status,
                },
                $attempt->submitted_at?->format('Y-m-d H:i'),
                $attempt->score,
                $answerDetails,
            ];
        });

        return $exports->download(
            $filters['format'] ?? 'csv',
            'academy-exam-attempts-' . $record->id,
            'نتایج آزمون: ' . $record->title,
            ['دانش‌آموز', 'ایمیل', 'شماره تلاش', 'وضعیت', 'زمان تحویل', 'نمره', 'پاسخ‌ها و فایل‌های پیوست'],
            $rows,
            [
                'آموزشگاه' => $academy->name,
                'آزمون' => $record->title,
                'دوره' => $record->course?->title,
                'تعداد تلاش‌ها' => $rows->count(),
            ],
        );
    }

    public function ownerReportSummary(Request $request, OwnerReportService $reports, PanelExportService $exports)
    {
        $filters = $request->validate(['format' => ['nullable', Rule::in(['csv', 'word'])]]);
        $data = $reports->build($request->user());
        $metrics = $data['metrics'] ?? [];
        $labels = [
            'students' => 'دانش‌آموزان فعال',
            'teachers' => 'مدرس‌های فعال',
            'published' => 'دوره‌های منتشرشده',
            'attendance_rate' => 'نرخ حضور (%)',
            'exam_average' => 'میانگین آزمون (%)',
            'enrollments' => 'ثبت‌نام فعال',
            'revenue' => 'درآمد ثبت‌شده',
            'refunds' => 'بازپرداخت ثبت‌شده',
            'classrooms' => 'تعداد کلاس‌ها',
            'pending_reviews' => 'تکالیف نیازمند بررسی',
        ];
        $rows = collect($labels)->map(fn ($label, $key): array => [
            $label,
            array_key_exists($key, $metrics) && $metrics[$key] !== null ? $metrics[$key] : '—',
        ]);

        return $exports->download(
            $filters['format'] ?? 'word',
            'academy-report-summary',
            'خلاصه گزارش آموزشگاه',
            ['شاخص', 'مقدار'],
            $rows,
            ['تعداد آموزشگاه‌های قابل مدیریت' => is_countable($data['academies'] ?? null) ? count($data['academies']) : '—', 'تهیه‌شده برای' => $request->user()->name],
        );
    }
}
