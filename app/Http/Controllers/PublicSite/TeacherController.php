<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\TeacherDirectoryService;
use App\Models\User;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(TeacherDirectoryService $service): View
    {
        return view('pages.teachers.index', [
            'teachers' => $service->paginate(),
        ]);
    }

    public function show(User $teacher, TeacherDirectoryService $service): View
    {
        $teacher = $service->findPublic($teacher);

        return view('pages.teachers.show', [
            'teacher' => $teacher,
        ]);
    }
}
