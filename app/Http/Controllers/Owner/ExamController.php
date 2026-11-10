<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Exam\StoreExamRequest;
use App\Models\Academy;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\MediaService;
use App\Services\TeacherAssessmentService;
use App\Models\User;
use App\Services\ExamQuestionWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class ExamController extends Controller
{
    public function index(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy, 'exams.view');
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:draft,published,closed'],
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'string', 'in:recent,oldest,title,attempts'],
        ]);
        $exams = Exam::query()
            ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
            ->with(['course:id,academy_id,title', 'classroom:id,title', 'teacher:id,name'])
            ->withCount('attempts')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);
                $query->where(fn ($nested) => $nested->where('title', 'like', '%' . $term . '%')
                    ->orWhereHas('course', fn ($course) => $course->where('title', 'like', '%' . $term . '%')));
            })
            ->when(($filters['sort'] ?? 'recent') === 'oldest', fn ($query) => $query->orderBy('created_at')->orderBy('id'))
            ->when(($filters['sort'] ?? 'recent') === 'title', fn ($query) => $query->orderBy('title')->orderByDesc('id'))
            ->when(($filters['sort'] ?? 'recent') === 'attempts', fn ($query) => $query->orderByDesc('attempts_count')->orderByDesc('created_at'))
            ->when(! in_array(($filters['sort'] ?? 'recent'), ['oldest', 'title', 'attempts'], true), fn ($query) => $query->orderByDesc('created_at')->orderByDesc('id'))
            ->paginate(20)->withQueryString();
        $stats = [
            'all' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->count(),
            'published' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->where('status', 'published')->count(),
            'attempts' => (int) DB::table('exam_attempts')
                ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
                ->join('courses', 'courses.id', '=', 'exams.course_id')
                ->where('courses.academy_id', $academy->id)
                ->count('exam_attempts.id'),
        ];
        return view('owner.exams.index', compact('academy', 'exams', 'filters', 'stats'));
    }

    public function create(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        return view('owner.exams.form', [
            'academy' => $academy,
            'hasAssignableCourses' => $this->assignableCourses($academy)->exists(),
            'courses' => collect(),
            'classrooms' => collect(),
            'exam' => new Exam(['status' => 'draft', 'duration_minutes' => 60, 'attempts_allowed' => 1]),
            'initialQuestions' => [],
        ]);
    }

    public function store(StoreExamRequest $request, Academy $academy, ExamQuestionWriter $questionWriter): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        $course = $academy->courses()->whereKey((int) $request->validated('course_id'))->firstOrFail();
        $classroomId = $request->validated('classroom_id');
        $this->assertClassroomInCourse($academy, $classroomId ? (int) $classroomId : null, $course->id);
        $teacher = $this->assignedTeacher($course, $classroomId ? (int) $classroomId : null);
        $payload = $request->safe()->except(['course_id', 'questions']);
        $payload['classroom_id'] = $classroomId ?: null;
        $payload['teacher_id'] = $teacher->id;
        $exam = DB::transaction(function () use ($course, $payload, $request, $questionWriter): Exam {
            $exam = $course->exams()->create($payload);
            $questionWriter->storeQuestions($exam, $request->validated('questions', []));
            return $exam;
        });
        return redirect()->route('owner.exams.show', [$academy, $exam->id])->with('success', 'آزمون و سؤال‌های آن با موفقیت ثبت شدند.');
    }

    public function edit(Academy $academy, int $exam, Request $request): View
    {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        $record = $this->ownedExam($academy, $exam);
        abort_if($record->attempts()->exists(), 409, 'پس از اولین تلاش دانش‌آموز، ویرایش سؤال‌ها برای حفظ تاریخچه قفل می‌شود.');
        $record->load([
            'course.teachers' => fn ($teachers) => $teachers
                ->where('users.status', 'active')
                ->orderByPivot('is_primary', 'desc')
                ->orderBy('users.name'),
            'classroom',
            'questions',
        ]);
        $initialQuestions = $record->questions->map(function ($question): array {
            $correctAnswer = $question->correct_answer;
            if (in_array($question->type, ['multiple', 'checkbox'], true)) {
                $decoded = json_decode((string) $correctAnswer, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $correctAnswer = $decoded;
            }
            return [
                'type' => $question->type,
                'question' => $question->question,
                'options' => $question->options ?? [],
                'correct_answer' => $correctAnswer,
                'expected_answer' => $question->expected_answer,
                'score' => $question->score,
            ];
        })->values()->all();
        return view('owner.exams.form', [
            'academy' => $academy,
            'hasAssignableCourses' => true,
            'courses' => collect(),
            'classrooms' => collect(),
            'exam' => $record,
            'initialQuestions' => $initialQuestions,
        ]);
    }

    public function update(StoreExamRequest $request, Academy $academy, int $exam, ExamQuestionWriter $questionWriter): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        $record = $this->ownedExam($academy, $exam);
        if ($record->attempts()->exists()) {
            throw ValidationException::withMessages(['title' => 'پس از ثبت اولین تلاش، سؤال‌ها قفل می‌شوند تا سابقه پاسخ و نمره تغییر نکند.']);
        }
        $course = $academy->courses()->whereKey((int) $request->validated('course_id'))->firstOrFail();
        $teacher = $this->assignedTeacher($course);
        $classroomId = $request->validated('classroom_id');
        $this->assertClassroomInCourse($academy, $classroomId ? (int) $classroomId : null, $course->id);
        $payload = $request->safe()->except(['course_id', 'questions']);
        $payload['course_id'] = $course->id;
        $payload['classroom_id'] = $classroomId ?: null;
        $payload['teacher_id'] = $teacher->id;
        DB::transaction(function () use ($record, $payload, $request, $questionWriter): void {
            $record->fill($payload)->save();
            foreach ($record->questions()->get() as $question) {
                $question->media()->detach();
                $question->delete();
            }
            $questionWriter->storeQuestions($record, $request->validated('questions', []));
        });
        return redirect()->route('owner.exams.show', [$academy, $record->id])->with('success', 'مشخصات و سؤال‌های آزمون به‌روزرسانی شدند.');
    }

    public function show(Academy $academy, int $exam): View
    {
        $this->authorizeAcademy(request(), $academy, 'exams.view');
        $record = $this->ownedExam($academy, $exam);
        $record->load(['course:id,academy_id,title', 'classroom:id,title', 'teacher:id,name', 'questions:id,exam_id,type,question,score,sort_order']);
        $record->loadCount('attempts');
        $attempts = $record->attempts()->with(['student:id,name', 'answers.question'])->orderByDesc('submitted_at')->orderByDesc('id')->paginate(25)->withQueryString();
        $summary = [
            'submitted' => $record->attempts()->whereNotNull('submitted_at')->count(),
            'graded' => $record->attempts()->whereNotNull('score')->count(),
            'average' => $record->attempts()->whereNotNull('score')->avg('score'),
        ];
        return view('owner.exams.show', compact('academy', 'record') + [
            'exam' => $record, 'attempts' => $attempts, 'summary' => $summary,
        ]);
    }


    public function gradeAttempt(
        Request $request,
        Academy $academy,
        int $exam,
        int $attempt,
        TeacherAssessmentService $assessments
    ): RedirectResponse {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        $ownedExam = $this->ownedExam($academy, $exam);
        $record = ExamAttempt::query()
            ->where('exam_id', $ownedExam->id)
            ->with('answers.question')
            ->findOrFail($attempt);

        $data = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ], [
            'scores.required' => 'نمرهٔ هر پاسخ را بررسی و ثبت کنید.',
            'scores.*.numeric' => 'نمره باید عددی باشد.',
            'scores.*.min' => 'نمره نمی‌تواند منفی باشد.',
        ]);

        $assessments->gradeExamAttemptManually($record, $data['scores'], (int) $request->user()->id);

        return redirect()->route('owner.exams.show', [$academy, $ownedExam->id])
            ->with('success', 'نمره‌های این تلاش ذخیره شد و نتیجه در کارتابل دانش‌آموز به‌روزرسانی شد.');
    }

    public function updateStatus(Request $request, Academy $academy, int $exam): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        $data = $request->validate(['status' => ['required', 'string', 'in:draft,published,closed']]);
        $record = $this->ownedExam($academy, $exam);
        if ($data['status'] === 'published' && ! $record->questions()->exists()) {
            return back()->withErrors(['status' => 'آزمون بدون سؤال منتشر نمی‌شود.'])->withInput();
        }
        $record->forceFill(['status' => $data['status']])->save();
        return redirect()->route('owner.exams.show', [$academy, $record->id])->with('success', 'وضعیت آزمون ذخیره شد.');
    }

    public function destroy(
        Academy $academy,
        int $exam,
        Request $request,
        MediaService $media
    ): RedirectResponse {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        $record = $this->ownedExam($academy, $exam);
        $files = collect();

        DB::transaction(function () use ($academy, $record, $request, $media, &$files): void {
            $this->authorizeAcademy($request, $academy, 'exams.manage');
            $locked = Exam::query()->whereKey($record->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->course()->where('academy_id', $academy->id)->exists(), 404);

            if ($locked->attempts()->exists()) {
                throw ValidationException::withMessages([
                    'exam' => 'برای این آزمون تلاش دانش‌آموز ثبت شده است؛ حذف مجاز نیست. آزمون را ببند تا پاسخ‌ها و نمره‌ها حفظ شوند.',
                ]);
            }

            if (! in_array($locked->status, ['draft', 'closed'], true)) {
                throw ValidationException::withMessages([
                    'exam' => 'ابتدا آزمون منتشرشده را ببند؛ سپس در صورت نداشتن تلاش می‌توانی آن را حذف کنی.',
                ]);
            }

            $locked->load(['media', 'questions.media']);
            foreach ($locked->questions as $question) {
                foreach ($question->media as $file) {
                    $media->detach($file, $question);
                    $files->push($file);
                }
            }

            foreach ($locked->media as $file) {
                $media->detach($file, $locked);
                $files->push($file);
            }

            $locked->questions()->delete();
            $locked->delete();
        }, 3);

        foreach ($files->unique('id') as $file) {
            if ($file->attachments()->doesntExist()) {
                $media->delete($file);
            }
        }

        return redirect()->route('owner.exams.index', $academy)
            ->with('success', 'آزمون بدون تلاش ثبت‌شده و فایل‌های بدون استفاده حذف شد.');
    }

    private function ownedExam(Academy $academy, int $exam): Exam
    {
        return Exam::query()->whereKey($exam)
            ->whereHas('course', fn (Builder $course) => $course->where('academy_id', $academy->id))
            ->firstOrFail();
    }

    private function assignableCourses(Academy $academy): Builder
    {
        return $academy->courses()->whereHas('teachers');
    }

    private function assignedTeacher(Course $course, ?int $classroomId = null): User
    {
        $teachers = $course->teachers()
            ->where('users.status', 'active')
            ->whereHas('academies', fn ($academies) => $academies
                ->whereKey($course->academy_id)
                ->wherePivot('role', 'teacher')
                ->wherePivot('status', 'active'));

        if ($classroomId !== null) {
            $teachers->whereHas('classroomsAsTeacher', fn ($classrooms) => $classrooms
                ->whereKey($classroomId)
                ->where('classrooms.course_id', $course->id)
                ->where('classrooms.status', 'active'));

            $assigned = $teachers->orderBy('users.name')->get();
            if ($assigned->count() !== 1) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'برای این کلاس باید دقیقاً یک مدرس فعال عضو دوره و آموزشگاه باشد.',
                ]);
            }

            return $assigned->first();
        }

        $teacher = $teachers
            ->orderByPivot('is_primary', 'desc')
            ->orderBy('users.name')
            ->first();

        if (! $teacher) {
            throw ValidationException::withMessages([
                'course_id' => 'پیش از ساخت آزمون، دست‌کم یک مدرس فعال را به این دوره تخصیص دهید.',
            ]);
        }

        return $teacher;
    }

    private function assertClassroomInCourse(Academy $academy, ?int $classroomId, int $courseId): void
    {
        if (! $classroomId) return;
        $exists = $academy->classrooms()->whereKey($classroomId)->where('course_id', $courseId)->where('status', 'active')->exists();
        if (! $exists) {
            throw ValidationException::withMessages(['classroom_id' => 'کلاس باید فعال و متعلق به همین دوره و آموزشگاه باشد.']);
        }
    }

    private function authorizeAcademy(Request $request, Academy $academy, string $permission): void
    {
        app(\App\Services\OwnerWorkspaceService::class)
            ->authorizeAcademy($request->user(), $academy, $permission);
    }
}
