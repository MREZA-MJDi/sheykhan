<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentResultService;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(StudentResultService $results): View
    {
        return view('student.results.index', [
            'results' => $results->paginate(request()->user()),
        ]);
    }
}
