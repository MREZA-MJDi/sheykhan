<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ParentMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ParentMessageController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $messages = ParentMessage::query()
            ->whereHas('student.classroomsAsStudent', fn ($classrooms) => $classrooms
                ->where('classrooms.status', 'active')
                ->where('classroom_student.status', 'active')
                ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id)))
            ->with(['academy:id,name', 'parent:id,name,mobile', 'student:id,name'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('teacher.messages.index', compact('messages'));
    }
}
