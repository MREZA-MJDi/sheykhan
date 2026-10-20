<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class OwnerAssignmentService
{
    public function index(User $owner, Academy $academy, array $filters = []): array
    {
        $this->authorizeAcademy($owner, $academy);

        $query = Assignment::query()
            ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
            ->with([
                'course:id,academy_id,title',
                'classroom:id,title,course_id',
                'teacher:id,name',
            ])
            ->withCount([
                'submissions as submitted_count' => fn ($submissions) => $submissions->whereNotNull('submitted_at'),
                'submissions as pending_review_count' => fn ($submissions) => $submissions
                    ->whereNotNull('submitted_at')
                    ->whereNull('graded_at'),
            ]);

        if (filled($filters['q'] ?? null)) {
            $term = trim((string) $filters['q']);
            $query->where(function (Builder $query) use ($term): void {
                $query->where('title', 'like', '%' . $term . '%')
                    ->orWhereHas('course', fn ($course) => $course->where('title', 'like', '%' . $term . '%'))
                    ->orWhereHas('classroom', fn ($classroom) => $classroom->where('title', 'like', '%' . $term . '%'));
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['classroom_id'] ?? null)) {
            $query->where('classroom_id', (int) $filters['classroom_id']);
        }

        $assignments = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (int) Assignment::query()
                ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
                ->count(),
            'published' => (int) Assignment::query()
                ->where('status', 'published')
                ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
                ->count(),
            'pending_reviews' => (int) DB::table('assignment_submissions as submissions')
                ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
                ->join('courses', 'courses.id', '=', 'assignments.course_id')
                ->where('courses.academy_id', $academy->id)
                ->whereNotNull('submissions.submitted_at')
                ->whereNull('submissions.graded_at')
                ->count(),
        ];

        return compact('academy', 'assignments', 'stats', 'filters');
    }

    public function formData(User $owner, Academy $academy): array
    {
        $this->authorizeAcademy($owner, $academy);

        $teacherMembership = function ($query) use ($academy): void {
            $query->selectRaw('1')
                ->from('classroom_teacher as assigned')
                ->join('users as teachers', 'teachers.id', '=', 'assigned.teacher_id')
                ->join('academy_user as membership', function ($join) use ($academy): void {
                    $join->on('membership.user_id', '=', 'assigned.teacher_id')
                        ->where('membership.academy_id', '=', $academy->id)
                        ->where('membership.role', '=', 'teacher')
                        ->where('membership.status', '=', 'active');
                })
                ->whereColumn('assigned.classroom_id', 'classrooms.id')
                ->where('teachers.status', 'active')
                ->whereNull('teachers.deleted_at');
        };

        $classrooms = $academy->classrooms()
            ->where('status', 'active')
            ->whereExists($teacherMembership)
            ->with(['course:id,title', 'teachers:id,name'])
            ->orderBy('title')
            ->get(['id', 'academy_id', 'course_id', 'title', 'code']);

        return compact('academy', 'classrooms');
    }

    public function create(
        User $owner,
        Academy $academy,
        array $data,
        ?UploadedFile $handout,
        MediaService $media
    ): Assignment {
        $this->authorizeAcademy($owner, $academy);

        $assignment = DB::transaction(function () use ($academy, $data): Assignment {
            $classroom = $academy->classrooms()
                ->where('status', 'active')
                ->whereKey((int) $data['classroom_id'])
                ->lockForUpdate()
                ->first();

            if (! $classroom) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'کلاس انتخاب‌شده فعال نیست یا متعلق به این آموزشگاه نیست.',
                ]);
            }

            $teachers = DB::table('classroom_teacher as assigned')
                ->join('users as teachers', 'teachers.id', '=', 'assigned.teacher_id')
                ->join('academy_user as membership', function ($join) use ($academy): void {
                    $join->on('membership.user_id', '=', 'assigned.teacher_id')
                        ->where('membership.academy_id', '=', $academy->id)
                        ->where('membership.role', '=', 'teacher')
                        ->where('membership.status', '=', 'active');
                })
                ->where('assigned.classroom_id', $classroom->id)
                ->where('teachers.status', 'active')
                ->whereNull('teachers.deleted_at')
                ->distinct()
                ->pluck('assigned.teacher_id');

            if ($teachers->count() !== 1) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'برای این کلاس باید دقیقاً یک مدرس فعال تعیین شده باشد.',
                ]);
            }

            $course = $academy->courses()->whereKey($classroom->course_id)->firstOrFail();

            return $course->assignments()->create([
                'classroom_id' => $classroom->id,
                'teacher_id' => (int) $teachers->first(),
                'title' => trim($data['title']),
                'instructions' => filled($data['instructions'] ?? null) ? trim($data['instructions']) : null,
                'due_at' => $data['due_at'] ?? null,
                'max_score' => $data['max_score'] ?? 20,
                'status' => $data['status'],
            ]);
        }, 3);

        if ($handout) {
            try {
                $media->upload($handout, $assignment, [
                    'disk' => 'local',
                    'directory' => 'assignments/' . $assignment->id,
                    'collection' => 'assignment-handout',
                    'visibility' => 'private',
                ]);
            } catch (Throwable $exception) {
                $assignment->delete();
                throw $exception;
            }
        }

        return $assignment;
    }

    public function show(User $owner, Academy $academy, Assignment $assignment): array
    {
        $this->authorizeAcademy($owner, $academy);

        abort_unless(
            (int) $assignment->course?->academy_id === (int) $academy->id,
            404
        );

        $assignment->load([
            'course:id,academy_id,title',
            'classroom:id,title,course_id',
            'teacher:id,name',
            'media:id,original_name,mime_type,size,status',
        ]);

        $submissions = $assignment->submissions()
            ->whereNotNull('submitted_at')
            ->with([
                'student:id,name,email',
                'media:id,original_name,mime_type,size,status',
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return compact('academy', 'assignment', 'submissions');
    }

    public function assertAssignmentOwned(User $owner, Academy $academy, Assignment $assignment): void
    {
        $this->authorizeAcademy($owner, $academy);
        abort_unless((int) $assignment->course?->academy_id === (int) $academy->id, 404);
    }

    private function authorizeAcademy(User $owner, Academy $academy): void
    {
        abort_unless(
            $owner->hasRole('academy-owner') && (int) $academy->owner_id === (int) $owner->id,
            403
        );
    }
}
