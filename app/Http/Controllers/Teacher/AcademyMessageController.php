<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademyMessage;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AcademyMessageController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();
        abort_unless($teacher->hasPermission('parent_messages.manage'), 403);

        $messages = AcademyMessage::query()->where('sender_id', $teacher->id)->where('sender_role', 'teacher')
            ->with(['sender:id,name', 'recipient:id,name', 'student:id,name', 'classroom:id,title'])
            ->latest()->paginate(12)->withQueryString();

        return view('teacher.communications.index', [
            'pageTitle' => 'ارسال پیام و اطلاعیه',
            'pageDescription' => 'به خانواده‌های یک کلاس یا به یک ولی یا دانش‌آموز از کلاس‌های خودت پیام بده.',
            'targetOptions' => $this->targetOptions($teacher),
            'storeRoute' => route('teacher.communications.store'),
            'backRoute' => route('teacher.dashboard'),
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($teacher->hasPermission('parent_messages.manage'), 403);
        $data = $request->validate([
            'target' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'in:announcement,academic,schedule,important,other'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'body' => ['required', 'string', 'min:5', 'max:10000'],
        ], [
            'target.required' => 'مخاطب پیام را انتخاب کن.',
            'subject.required' => 'عنوان پیام را وارد کن.',
            'body.required' => 'متن پیام را وارد کن.',
        ]);

        $parts = explode(':', $data['target']);
        $classroom = null;
        $student = null;
        $recipient = null;
        $audience = null;

        if (count($parts) === 3 && $parts[0] === 'classroom' && $parts[2] === 'parents') {
            $classroom = $this->activeClassroom($teacher, (int) $parts[1]);
            $audience = 'classroom_parents';
        } elseif (count($parts) === 3 && $parts[0] === 'student') {
            $classroom = $this->activeClassroom($teacher, (int) $parts[1]);
            $student = $this->activeStudentInClassroom($classroom, (int) $parts[2]);
            $recipient = $student;
            $audience = 'student';
        } elseif (count($parts) === 4 && $parts[0] === 'parent') {
            $classroom = $this->activeClassroom($teacher, (int) $parts[1]);
            $student = $this->activeStudentInClassroom($classroom, (int) $parts[2]);
            $recipient = $this->activeParentForStudent($classroom, $student, (int) $parts[3]);
            $audience = 'parent';
        } else {
            abort(404);
        }

        AcademyMessage::query()->create([
            'academy_id' => $classroom->academy_id,
            'sender_id' => $teacher->id,
            'sender_role' => 'teacher',
            'audience' => $audience,
            'recipient_id' => $recipient?->id,
            'student_id' => $student?->id,
            'classroom_id' => $classroom->id,
            'course_id' => $classroom->course_id,
            'category' => $data['category'],
            'subject' => trim($data['subject']),
            'body' => trim($data['body']),
            'published_at' => now(),
        ]);

        return redirect()->route('teacher.communications.index')->with('success', 'پیام برای مخاطب انتخاب‌شده ارسال شد.');
    }

    /** @return array<int, array{value:string,label:string}> */
    private function targetOptions(User $teacher): array
    {
        $rooms = DB::table('classroom_teacher as assignment')
            ->join('classrooms', 'classrooms.id', '=', 'assignment.classroom_id')
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('academies', 'academies.id', '=', 'classrooms.academy_id')
            ->join('academy_user as teacher_membership', function ($join): void {
                $join->on('teacher_membership.user_id', '=', 'assignment.teacher_id')
                    ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('teacher_membership.role', 'teacher')->where('teacher_membership.status', 'active');
            })
            ->where('assignment.teacher_id', $teacher->id)->where('classrooms.status', 'active')->where('academies.status', 'active')
            ->orderBy('classrooms.title')->distinct()
            ->get(['classrooms.id as classroom_id', 'classrooms.title as classroom_title', 'classrooms.academy_id', 'courses.title as course_title']);

        $roster = DB::table('classroom_student as roster')
            ->join('classrooms', 'classrooms.id', '=', 'roster.classroom_id')
            ->join('classroom_teacher as assignment', 'assignment.classroom_id', '=', 'classrooms.id')
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('academies', 'academies.id', '=', 'classrooms.academy_id')
            ->join('users as students', 'students.id', '=', 'roster.student_id')
            ->join('academy_user as teacher_membership', function ($join): void {
                $join->on('teacher_membership.user_id', '=', 'assignment.teacher_id')
                    ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('teacher_membership.role', 'teacher')->where('teacher_membership.status', 'active');
            })
            ->join('academy_user as student_membership', function ($join): void {
                $join->on('student_membership.user_id', '=', 'students.id')
                    ->on('student_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('student_membership.role', 'student')->where('student_membership.status', 'active');
            })
            ->where('assignment.teacher_id', $teacher->id)->where('classrooms.status', 'active')
            ->where('roster.status', 'active')->where('academies.status', 'active')
            ->where('students.status', 'active')->whereNull('students.deleted_at')->distinct();

        $students = (clone $roster)->orderBy('classrooms.title')->orderBy('students.name')->get([
            'classrooms.id as classroom_id', 'classrooms.title as classroom_title',
            'classrooms.academy_id', 'classrooms.course_id', 'courses.title as course_title',
            'students.id as student_id', 'students.name as student_name',
        ]);
        $parents = (clone $roster)
            ->join('parent_student as links', 'links.student_id', '=', 'students.id')
            ->join('users as parents', 'parents.id', '=', 'links.parent_id')
            ->join('academy_user as parent_membership', function ($join): void {
                $join->on('parent_membership.user_id', '=', 'parents.id')
                    ->on('parent_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('parent_membership.role', 'parent')->where('parent_membership.status', 'active');
            })
            ->where('parents.status', 'active')->whereNull('parents.deleted_at')
            ->orderBy('classrooms.title')->orderBy('students.name')->orderBy('parents.name')
            ->get([
                'classrooms.id as classroom_id', 'classrooms.title as classroom_title',
                'classrooms.academy_id', 'classrooms.course_id', 'courses.title as course_title',
                'students.id as student_id', 'students.name as student_name',
                'parents.id as parent_id', 'parents.name as parent_name',
            ]);

        $roomsWithParents = $parents->pluck('classroom_id')->map(fn ($id) => (int) $id)->unique()->all();
        $targets = [];
        foreach ($rooms as $room) {
            if (in_array((int) $room->classroom_id, $roomsWithParents, true)) {
                $targets[] = ['value' => 'classroom:' . $room->classroom_id . ':parents', 'label' => 'همه خانواده‌های ' . $room->classroom_title . ' · ' . $room->course_title];
            }
        }
        foreach ($students as $row) {
            $targets[] = ['value' => 'student:' . $row->classroom_id . ':' . $row->student_id, 'label' => 'دانش‌آموز ' . $row->student_name . ' · ' . $row->classroom_title];
        }
        foreach ($parents as $row) {
            $targets[] = ['value' => 'parent:' . $row->classroom_id . ':' . $row->student_id . ':' . $row->parent_id, 'label' => 'ولی ' . $row->parent_name . ' · فرزند ' . $row->student_name . ' · ' . $row->classroom_title];
        }
        return $targets;
    }

    private function activeClassroom(User $teacher, int $classroomId): Classroom
    {
        $classroom = Classroom::query()->whereKey($classroomId)->where('status', 'active')
            ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
            ->whereHas('course.academy', fn ($academies) => $academies->where('academies.status', 'active'))
            ->with('course:id,academy_id,title')->firstOrFail();

        abort_unless($teacher->academies()->whereKey($classroom->academy_id)->where('academies.status', 'active')
            ->wherePivot('role', 'teacher')->wherePivot('status', 'active')->exists(), 404);
        return $classroom;
    }

    private function activeStudentInClassroom(Classroom $classroom, int $studentId): User
    {
        return $classroom->students()->whereKey($studentId)->where('users.status', 'active')->wherePivot('status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->whereHas('academies', fn ($academies) => $academies->where('academies.id', $classroom->academy_id)
                ->where('academies.status', 'active')->wherePivot('role', 'student')->wherePivot('status', 'active'))
            ->firstOrFail();
    }

    private function activeParentForStudent(Classroom $classroom, User $student, int $parentId): User
    {
        return $student->parents()->whereKey($parentId)->where('users.status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'parent'))
            ->whereHas('academies', fn ($academies) => $academies->where('academies.id', $classroom->academy_id)
                ->where('academies.status', 'active')->wherePivot('role', 'parent')->wherePivot('status', 'active'))
            ->firstOrFail();
    }
}
