<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SaveLessonNoteRequest;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\StudentPersonalNote;
use App\Http\Requests\Student\StoreStudentPersonalNoteRequest;
use App\Services\StudentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(StudentAccessService $access): View
    {
        $student = request()->user();

        $notes = LessonNote::query()
            ->where('user_id', $student->id)
            ->with('lesson:id,title,course_section_id')
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $personalNotes = $student->personalNotes()
            ->latest('updated_at')
            ->paginate(20, ['*'], 'personal_notes_page')
            ->withQueryString();

        return view('student.notes.index', [
            'notes' => $notes,
            'personalNotes' => $personalNotes,
        ]);
    }

    public function store(
        SaveLessonNoteRequest $request,
        Lesson $lesson,
        StudentAccessService $access
    ): RedirectResponse {
        abort_unless($access->lesson($request->user(), $lesson), 404);

        LessonNote::updateOrCreate(
            ['lesson_id' => $lesson->id, 'user_id' => $request->user()->id],
            ['content' => $request->validated('content')],
        );

        return redirect()->route('student.lessons.show', $lesson)->with('success', 'یادداشت شما ذخیره شد.');
    }

    public function storePersonal(StoreStudentPersonalNoteRequest $request): RedirectResponse
    {
        $request->user()->personalNotes()->create([
            'content' => trim($request->validated('content')),
        ]);

        return redirect()->route('student.notes.index')->with('success', 'یادداشت شخصی ذخیره شد.');
    }

    public function destroyPersonal(StudentPersonalNote $note): RedirectResponse
    {
        abort_unless((int) $note->student_id === (int) request()->user()->id, 404);

        $note->delete();

        return redirect()->route('student.notes.index')->with('success', 'یادداشت شخصی حذف شد.');
    }

    public function destroy(LessonNote $note): RedirectResponse
    {
        abort_unless($note->user_id === request()->user()->id, 404);

        $note->delete();

        return redirect()->route('student.notes.index')->with('success', 'یادداشت حذف شد.');
    }
}
