<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
                $query->where(function ($nested) use ($term): void {
                    $nested->where('title', 'like', '%' . $term . '%')
                        ->orWhereHas('course', fn ($course) => $course->where('title', 'like', '%' . $term . '%'));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'all' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->count(),
            'published' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->where('status', 'published')->count(),
            'attempts' => Exam::query()->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->withCount('attempts')->get()->sum('attempts_count'),
        ];

        return view('owner.exams.index', compact('academy', 'exams', 'filters', 'stats'));
    }

    public function show(Academy $academy, int $exam): View
    {
        $this->authorizeAcademy(request(), $academy, 'exams.view');

        $record = Exam::query()
            ->whereKey($exam)
            ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
            ->with(['course:id,academy_id,title', 'classroom:id,title', 'teacher:id,name', 'questions:id,exam_id,type,question,score,sort_order'])
            ->withCount('attempts')
            ->firstOrFail();

        $attempts = $record->attempts()
            ->with('student:id,name')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'submitted' => $record->attempts()->whereNotNull('submitted_at')->count(),
            'graded' => $record->attempts()->whereNotNull('score')->count(),
            'average' => $record->attempts()->whereNotNull('score')->avg('score'),
        ];

        return view('owner.exams.show', [
            'academy' => $academy,
            'exam' => $record,
            'attempts' => $attempts,
            'summary' => $summary,
        ]);
    }

    public function updateStatus(Request $request, Academy $academy, int $exam): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, 'exams.manage');

        $data = $request->validate([
            'status' => ['required', 'string', 'in:draft,published,closed'],
        ]);

        $record = Exam::query()
            ->whereKey($exam)
            ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
            ->firstOrFail();

        if ($data['status'] === 'published' && ! $record->questions()->exists()) {
            return back()->withErrors(['status' => 'آزمون بدون سؤال منتشر نمی‌شود.'])->withInput();
        }

        $record->forceFill(['status' => $data['status']])->save();

        return redirect()->route('owner.exams.show', [$academy, $record->id])
            ->with('success', 'وضعیت آزمون ذخیره شد.');
    }

    private function authorizeAcademy(Request $request, Academy $academy, string $permission): void
    {
        abort_unless(
            $request->user()->hasRole('academy-owner')
            && $request->user()->hasPermission($permission)
            && (int) $academy->owner_id === (int) $request->user()->id,
            404
        );
    }
}
