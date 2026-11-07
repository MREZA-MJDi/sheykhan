<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreOwnerAssignmentRequest;
use App\Models\Academy;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\MediaService;
use App\Services\OwnerAssignmentService;
use App\Services\TeacherAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AssignmentController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerAssignmentService $assignments): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'closed'])],
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')->where('academy_id', $academy->id)],
        ]);

        return view('owner.assignments.index', $assignments->index($request->user(), $academy, $filters));
    }

    public function create(Academy $academy, Request $request, OwnerAssignmentService $assignments): View
    {
        return view('owner.assignments.form', [
            ...$assignments->formData($request->user(), $academy),
            'assignment' => new Assignment(['status' => 'draft', 'max_score' => 20]),
        ]);
    }

    public function store(
        StoreOwnerAssignmentRequest $request,
        Academy $academy,
        OwnerAssignmentService $assignments,
        MediaService $media
    ): RedirectResponse {
        $assignment = $assignments->create(
            $request->user(),
            $academy,
            $request->validated(),
            $request->file('handout'),
            $media
        );

        return redirect()
            ->route('owner.assignments.show', [$academy, $assignment])
            ->with('success', 'تکلیف برای کلاس انتخاب‌شده ثبت شد.');
    }

    public function show(
        Academy $academy,
        Assignment $assignment,
        Request $request,
        OwnerAssignmentService $assignments
    ): View {
        return view('owner.assignments.show', $assignments->show($request->user(), $academy, $assignment));
    }

    public function updateStatus(
        Academy $academy,
        Assignment $assignment,
        Request $request,
        OwnerAssignmentService $assignments
    ): RedirectResponse {
        $assignments->assertAssignmentOwned($request->user(), $academy, $assignment);
        $request->validate(['status' => ['required', 'in:closed']]);

        DB::transaction(function () use ($academy, $assignment, $request, $assignments): void {
            $locked = Assignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            $assignments->assertAssignmentOwned($request->user(), $academy, $locked);
            $locked->forceFill(['status' => 'closed'])->save();
        }, 3);

        return redirect()->route('owner.assignments.index', $academy)
            ->with('success', 'تکلیف بسته شد؛ پاسخ‌ها و بازخوردهای قبلی حفظ شدند.');
    }

    public function destroy(
        Academy $academy,
        Assignment $assignment,
        Request $request,
        OwnerAssignmentService $assignments,
        MediaService $media
    ): RedirectResponse {
        $assignments->assertAssignmentOwned($request->user(), $academy, $assignment);
        $files = collect();

        DB::transaction(function () use ($academy, $assignment, $request, $assignments, $media, &$files): void {
            $locked = Assignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            $assignments->assertAssignmentOwned($request->user(), $academy, $locked);

            if ($locked->submissions()->exists()) {
                throw ValidationException::withMessages([
                    'assignment' => 'برای این تکلیف پاسخ ثبت شده است؛ حذف مجاز نیست و سوابق باید حفظ شوند.',
                ]);
            }

            if (! in_array($locked->status, ['draft', 'closed'], true)) {
                throw ValidationException::withMessages([
                    'assignment' => 'ابتدا تکلیف منتشرشده را ببند؛ سپس در صورت نداشتن پاسخ می‌توانی آن را حذف کنی.',
                ]);
            }

            $files = $locked->media()->get();
            foreach ($files as $file) {
                $media->detach($file, $locked);
            }

            $locked->delete();
        }, 3);

        foreach ($files->unique('id') as $file) {
            if ($file->attachments()->doesntExist()) {
                $media->delete($file);
            }
        }

        return redirect()->route('owner.assignments.index', $academy)
            ->with('success', 'تکلیف بدون پاسخ و فایل‌های بلااستفاده حذف شد.');
    }

    public function grade(
        \App\Http\Requests\Teacher\GradeAssignmentSubmissionRequest $request,
        Academy $academy,
        Assignment $assignment,
        AssignmentSubmission $submission,
        OwnerAssignmentService $assignments,
        TeacherAssessmentService $assessment
    ): RedirectResponse {
        $assignments->assertAssignmentOwned($request->user(), $academy, $assignment);
        abort_unless((int) $submission->assignment_id === (int) $assignment->id, 404);

        $assessment->gradeAssignment(
            $assignment,
            $submission,
            (float) $request->validated('score'),
            $request->validated('feedback'),
            (int) $request->user()->id
        );

        return redirect()
            ->route('owner.assignments.show', [$academy, $assignment])
            ->with('success', 'نمره و بازخورد ذخیره شد.');
    }
}
