<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\AcademyMeeting;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $parent = $request->user();

        $academyIds = $parent->academies()
            ->where('academies.status', 'active')
            ->wherePivot('role', 'parent')
            ->wherePivot('status', 'active')
            ->pluck('academies.id');

        $childIds = $parent->children()
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->whereHas('academies', fn ($academies) => $academies
                ->whereIn('academies.id', $academyIds)
                ->where('academies.status', 'active')
                ->where('academy_user.role', 'student')
                ->where('academy_user.status', 'active'))
            ->pluck('users.id');

        $meetings = AcademyMeeting::query()
            ->where('parent_id', $parent->id)
            ->whereIn('academy_id', $academyIds)
            ->whereIn('student_id', $childIds)
            ->with([
                'academy:id,name',
                'classroom:id,title',
                'course:id,title',
                'student:id,name',
                'creator:id,name',
            ])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 WHEN status = 'proposed' THEN 1 ELSE 2 END")
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return view('parent.meetings.index', compact('meetings'));
    }
}
