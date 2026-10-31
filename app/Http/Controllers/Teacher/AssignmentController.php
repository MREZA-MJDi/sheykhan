<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Assignment\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Services\MediaService;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class AssignmentController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.assignments', [
            'assignments' => $workspace->assignments(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.assignments.form', [
            'courses' => $workspace->courses(request()->user()),
            'classrooms' => $workspace->classrooms(request()->user()),
            'assignment' => new Assignment(['status' => 'draft', 'max_score' => 20]),
        ]);
    }

    public function store(
        StoreAssignmentRequest $request,
        TeacherWorkspaceService $workspace,
        MediaService $media
    ): RedirectResponse {
        $courseId = (int) $request->validated('course_id');
        $course = $workspace->courseOwnedBy($request->user(), $courseId);

        $classroomId = $request->validated('classroom_id');
        if ($classroomId) {
            $workspace->classroomOwnedByCourse($request->user(), (int) $classroomId, $courseId);
        }

        $payload = $request->safe()->except(['course_id', 'handout']);
        $payload['teacher_id'] = $request->user()->id;
        $uploaded = null;

        try {
            DB::transaction(function () use ($course, $payload, $request, $media, &$uploaded): void {
                $assignment = $course->assignments()->create($payload);

                if ($request->hasFile('handout')) {
                    $uploaded = $media->upload($request->file('handout'), $assignment, [
                        'disk' => 'local',
                        'directory' => 'assignments/' . $assignment->id,
                        'collection' => 'assignment-handout',
                        'visibility' => 'private',
                    ]);
                }
            }, 3);
        } catch (Throwable $exception) {
            if ($uploaded) {
                $media->delete($uploaded);
            }

            throw $exception;
        }

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'تکلیف با موفقیت ساخته شد؛ فایل راهنما در فضای خصوصی ذخیره شد.');
    }

    public function submissions(
        Assignment $assignment,
        TeacherWorkspaceService $workspace
    ): View {
        abort_unless($assignment->teacher_id === request()->user()->id, 403);

        return view('teacher.assignments.submissions', [
            'assignment' => $assignment->load('classroom:id,title'),
            'submissions' => $assignment->submissions()
                ->with([
                    'student:id,name',
                    'media:id,original_name,mime_type,extension,size,status',
                ])
                ->latest('submitted_at')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
}
