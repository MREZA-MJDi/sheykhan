<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ParentMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ParentMessageController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $filters = $request->validate([
            'status' => ['nullable', 'string', \Illuminate\Validation\Rule::in(['open', 'in_review', 'responded', 'closed'])],
            'category' => ['nullable', 'string', \Illuminate\Validation\Rule::in(['question', 'suggestion', 'complaint', 'other'])],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $messages = ParentMessage::query()
            ->where(function ($scope) use ($teacher): void {
                $scope->whereHas('student.classroomsAsStudent', fn ($classrooms) => $classrooms
                    ->where('classrooms.status', 'active')
                    ->where('classroom_student.status', 'active')
                    ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id)))
                    ->orWhereHas('student.enrollments', fn ($enrollments) => $enrollments
                        ->where('course_enrollments.status', 'active')
                        ->whereHas('course.teachers', fn ($teachers) => $teachers->whereKey($teacher->id)));
            })
            ->with(['academy:id,name', 'parent:id,name', 'student:id,name', 'responder:id,name'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);
                $query->where(function ($nested) use ($term): void {
                    $nested->where('subject', 'like', '%' . $term . '%')
                        ->orWhere('body', 'like', '%' . $term . '%')
                        ->orWhereHas('parent', fn ($parent) => $parent->where('name', 'like', '%' . $term . '%'))
                        ->orWhereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('teacher.messages.index', compact('messages', 'filters'));
    }

    public function reply(Request $request, ParentMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'response' => ['required', 'string', 'min:2', 'max:5000'],
        ], [
            'response.required' => 'متن پاسخ را بنویسید.',
            'response.min' => 'پاسخ باید حداقل دو کاراکتر داشته باشد.',
        ]);

        abort_unless($message->status !== 'closed', 422, 'پیام بسته شده است و امکان پاسخ جدید ندارد.');

        $teacher = $request->user();
        $student = $message->student;

        $teachesClass = $student->classroomsAsStudent()
            ->where('classrooms.academy_id', $message->academy_id)
            ->where('classrooms.status', 'active')
            ->where('classroom_student.status', 'active')
            ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
            ->exists();

        $teachesCourse = $student->enrollments()
            ->where('course_enrollments.status', 'active')
            ->whereHas('course', fn ($course) => $course
                ->where('courses.academy_id', $message->academy_id)
                ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id)))
            ->exists();

        abort_unless($teachesClass || $teachesCourse, 404);

        $message->forceFill([
            'status' => 'responded',
            'response' => trim($data['response']),
            'responded_by' => $teacher->id,
            'responded_at' => now(),
        ])->save();

        return redirect()->route('teacher.messages.index')
            ->with('success', 'پاسخ شما ثبت شد و در کارتابل والد نمایش داده می‌شود.');
    }
}
