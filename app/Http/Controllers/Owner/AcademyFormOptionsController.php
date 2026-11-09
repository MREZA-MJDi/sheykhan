<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AcademyFormOptionsController extends Controller
{
    public function __invoke(Academy $academy, Request $request, OwnerWorkspaceService $workspace): JsonResponse
    {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        $filters = $request->validate([
            'type' => ['required', 'string', 'in:resource-courses,resource-classrooms,resource-lessons,exam-courses,exam-classrooms,classroom-courses,classroom-filter-courses,classroom-teachers,attendance-classrooms'],
            'q' => ['required', 'string', 'min:2', 'max:80'],
            'course_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $requiredPermission = match (true) {
            str_starts_with($filters['type'], 'resource-') => 'resources.manage',
            str_starts_with($filters['type'], 'exam-') => 'exams.manage',
            in_array($filters['type'], ['classroom-courses', 'classroom-teachers'], true) => 'classrooms.manage',
            $filters['type'] === 'classroom-filter-courses' => 'classrooms.view',
            $filters['type'] === 'attendance-classrooms' => 'attendance.view',
            default => 'classrooms.view',
        };
        abort_unless($request->user()->hasPermission($requiredPermission), 403);

        $like = trim($filters['q']) . '%';
        $courseId = isset($filters['course_id']) ? (int) $filters['course_id'] : null;

        $paginator = match ($filters['type']) {
            'attendance-classrooms' => $academy->classrooms()
                ->where('title', 'like', $like)
                ->with('course:id,title')
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),

            'classroom-courses' => Course::query()
                ->where('academy_id', $academy->id)
                ->where('title', 'like', $like)
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),

            'classroom-filter-courses' => Course::query()
                ->where('academy_id', $academy->id)
                ->where('title', 'like', $like)
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),


            'classroom-teachers' => (function () use ($academy, $courseId, $like) {
                abort_unless($courseId, 422, 'ابتدا یک دوره انتخاب کن.');
                $course = $academy->courses()->whereKey($courseId)->firstOrFail();

                return $course->teachers()
                    ->where('users.status', 'active')
                    ->where('users.name', 'like', $like)
                    ->whereExists(function ($membership) use ($academy): void {
                        $membership->selectRaw('1')
                            ->from('academy_user as active_teacher_membership')
                            ->whereColumn('active_teacher_membership.user_id', 'users.id')
                            ->where('active_teacher_membership.academy_id', $academy->id)
                            ->where('active_teacher_membership.role', 'teacher')
                            ->where('active_teacher_membership.status', 'active');
                    })
                    ->select('users.id', 'users.name')
                    ->orderBy('users.name')->orderBy('users.id')
                    ->simplePaginate(20);
            })(),

            'resource-courses' => Course::query()
                ->where('academy_id', $academy->id)
                ->where('title', 'like', $like)
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),

            'exam-courses' => Course::query()
                ->where('academy_id', $academy->id)
                ->whereHas('teachers')
                ->where('title', 'like', $like)
                ->with(['teachers' => fn ($teachers) => $teachers->where('users.status', 'active')->orderByPivot('is_primary', 'desc')->orderBy('users.name')])
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),

            'resource-classrooms' => Classroom::query()
                ->where('academy_id', $academy->id)
                ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
                ->where('title', 'like', $like)
                ->with('course:id,title')
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),

            'exam-classrooms' => Classroom::query()
                ->where('academy_id', $academy->id)
                ->where('status', 'active')
                ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
                ->where(function ($query) use ($like): void {
                    $query->where('title', 'like', $like)
                        ->orWhereHas('course', fn ($course) => $course->where('title', 'like', $like));
                })
                ->with('course:id,title')
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),

            default => Lesson::query()
                ->whereHas('section.course', function ($course) use ($academy, $courseId): void {
                    $course->where('academy_id', $academy->id)
                        ->when($courseId, fn ($query) => $query->whereKey($courseId));
                })
                ->where('title', 'like', $like)
                ->with('section.course:id,title')
                ->orderBy('title')->orderBy('id')
                ->simplePaginate(20),
        };

        $data = $paginator->getCollection()->map(function ($item) use ($filters): array {
            if ($filters['type'] === 'classroom-teachers') {
                return [
                    'id' => (int) $item->id,
                    'label' => $item->name,
                ];
            }

            if (str_contains($filters['type'], 'courses')) {
                return [
                    'id' => (int) $item->id,
                    'label' => $item->title . (str_starts_with($filters['type'], 'exam-') && $item->teachers->first()
                        ? ' · مدرس: ' . $item->teachers->first()->name
                        : ''),
                ];
            }

            if (str_contains($filters['type'], 'classrooms')) {
                return [
                    'id' => (int) $item->id,
                    'label' => $item->title . ($item->course?->title ? ' · ' . $item->course->title : ''),
                    'course_id' => (int) $item->course_id,
                ];
            }

            return [
                'id' => (int) $item->id,
                'label' => ($item->section?->course?->title ? $item->section->course->title . ' · ' : '') . $item->title,
                'course_id' => (int) ($item->section?->course_id ?? 0),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'has_more' => $paginator->hasMorePages(),
        ])->header('Cache-Control', 'private, no-store');
    }
}
