<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Exam\StoreExamRequest;
use App\Models\Academy;
use App\Models\Course;
use App\Models\Exam;
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
            ->orderByDesc('created_at')->paginate(20)->withQueryString();
        $stats = [
            'all' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->count(),
            'published' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->where('status', 'published')->count(),
            'attempts' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->withCount('attempts')->get()->sum('attempts_count'),
        ];
        return view('owner.exams.index', compact('academy', 'exams', 'filters', 'stats'));
    }

    public function create(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy, 'exams.manage');
        return view('owner.exams.form', [
            'academy' => $academy,
            'courses' => $this->assignableCourses($academy),
            'classrooms' => $academy->classrooms()->where('status', 'active')->with('course:id,title')->orderBy('title')->get(),
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
        $record->load(['course', 'classroom', 'questions']);
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
                'score' => $question->score,
            ];
        })->values()->all();
        return view('owner.exams.form', [
            'academy' => $academy,
            'courses' => $this->assignableCourses($academy),
            'classrooms' => $academy->classrooms()->where('status', 'active')->with('course:id,title')->orderBy('title')->get(),
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
        $attempts = $record->attempts()->with('student:id,name')->orderByDesc('submitted_at')->orderByDesc('id')->paginate(25)->withQueryString();
        $summary = [
            'submitted' => $record->attempts()->whereNotNull('submitted_at')->count(),
            'graded' => $record->attempts()->whereNotNull('score')->count(),
            'average' => $record->attempts()->whereNotNull('score')->avg('score'),
        ];
        return view('owner.exams.show', compact('academy', 'record') + [
            'exam' => $record, 'attempts' => $attempts, 'summary' => $summary,
        ]);
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

    private function ownedExam(Academy $academy, int $exam): Exam
    {
        return Exam::query()->whereKey($exam)
            ->whereHas('course', fn (Builder $course) => $course->where('academy_id', $academy->id))
            ->firstOrFail();
    }

    private function assignableCourses(Academy $academy)
    {
        return $academy->courses()->whereHas('teachers')
            ->with(['teachers' => fn ($teachers) => $teachers->orderByPivot('is_primary', 'desc')->orderBy('users.name')])
            ->orderBy('title')->get();
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
        abort_unless($request->user()->hasRole('academy-owner') && $request->user()->hasPermission($permission) && (int) $academy->owner_id === (int) $request->user()->id, 404);
    }
}
