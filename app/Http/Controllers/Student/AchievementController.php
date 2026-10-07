<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(): View
    {
        $student = request()->user();

        $achievements = Achievement::query()
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with('media:id,disk,path,original_name,mime_type,size,status,visibility')
            ->latest('published_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('student.achievements.index', compact('achievements'));
    }
}
