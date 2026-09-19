<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\TeacherDirectoryService;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(TeacherDirectoryService $service): View
    {
        return view('pages.teachers.index', [
            'teachers' => $service->paginate(),
        ]);
    }
}
