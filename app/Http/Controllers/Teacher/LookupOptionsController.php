<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LookupOptionsController extends Controller
{
    public function __invoke(Request $request, string $type, TeacherWorkspaceService $workspace): JsonResponse
    {
        abort_unless(in_array($type, ['courses', 'classrooms'], true), 404);

        $permission = $type === 'courses' ? 'courses.view' : 'classrooms.view';
        abort_unless($request->user()?->hasPermission($permission), 403);

        $filters = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'course_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $options = $workspace->lookupOptions(
            $request->user(),
            $type,
            $filters['q'],
            isset($filters['course_id']) ? (int) $filters['course_id'] : null,
            20,
        );

        return response()->json([
            'data' => $options->getCollection()->map(function ($item) use ($type): array {
                $label = $type === 'courses'
                    ? $item->title
                    : $item->title . ($item->course?->title ? ' — ' . $item->course->title : '');

                return ['id' => (int) $item->id, 'label' => $label];
            })->values(),
            'meta' => [
                'current_page' => $options->currentPage(),
                'last_page' => $options->lastPage(),
                'next_page' => $options->hasMorePages() ? $options->currentPage() + 1 : null,
                'total' => $options->total(),
            ],
        ], 200, ['Cache-Control' => 'private, no-store']);
    }
}
