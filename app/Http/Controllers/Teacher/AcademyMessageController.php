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
            'pageDescription' => 'به همه خانواده‌های کلاس‌های خودت، خانواده‌های یک کلاس یا یک دانش‌آموز و ولی مشخص پیام بده.',
            'targetOptions' => $this->targetOptions($teacher),
            'storeRoute' => route('teacher.communications.store'),
            'backRoute' => route('teacher.dashboard'),
            'canManage' => $teacher->hasPermission('parent_messages.manage'),
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

        if (count($parts) === 2 && $parts[0] === 'all-class-parents') {
            $academyId = (int) $parts[1];
            $hasActiveMembership = $teacher->academies()
                ->whereKey($academyId)
                ->where('academies.status', 'active')
                ->wherePivot('role', 'teacher')
                ->wherePivot('status', 'active')
                ->exists();

            $hasAssignedClassroom = DB::table('classroom_teacher as assignment')
                ->join('classrooms', 'classrooms.id', '=', 'assignment.classroom_id')
                ->where('assignment.teacher_id', $teacher->id)
                ->where('classrooms.academy_id', $academyId)
                ->where('classrooms.status', 'active')
                ->exists();

            abort_unless($hasActiveMembership && $hasAssignedClassroom, 404);

            AcademyMessage::query()->create([
                'academy_id' => $academyId,
                'sender_id' => $teacher->id,
                'sender_role' => 'teacher',
                'audience' => 'teacher_class_parents',
                'recipient_id' => null,
                'student_id' => null,
                'classroom_id' => null,
                'course_id' => null,
                'category' => $data['category'],
                'subject' => trim($data['subject']),
                'body' => trim($data['body']),
                'published_at' => now(),
            ]);

            return redirect()->route('teacher.communications.index')
                ->with('success', 'پیام برای خانواده‌های همه کلاس‌های فعال شما ارسال شد.');
        }

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
        // The initial page only loads one option per academy. Individual people
        // and class groups are searched through the bounded recipient endpoint.
        return DB::table('classroom_teacher as assignment')
            ->join('classrooms', 'classrooms.id', '=', 'assignment.classroom_id')
            ->join('academies', 'academies.id', '=', 'classrooms.academy_id')
            ->join('academy_user as teacher_membership', function ($join): void {
                $join->on('teacher_membership.user_id', '=', 'assignment.teacher_id')
                    ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('teacher_membership.role', 'teacher')
                    ->where('teacher_membership.status', 'active');
            })
            ->where('assignment.teacher_id', $teacher->id)
            ->where('classrooms.status', 'active')
            ->where('academies.status', 'active')
            ->groupBy('academies.id', 'academies.name')
            ->orderBy('academies.name')
            ->get(['academies.id as academy_id', 'academies.name as academy_name'])
            ->map(fn ($academy): array => [
                'value' => 'all-class-parents:' . $academy->academy_id,
                'label' => 'همه خانواده‌های کلاس‌های من · ' . $academy->academy_name,
            ])
            ->all();
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
        $student = User::query()->whereKey($studentId)->where('status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->firstOrFail();

        $hasActiveRoster = DB::table('classroom_student')
            ->where('classroom_id', $classroom->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        $hasActiveMembership = DB::table('academy_user as membership')
            ->join('academies', 'academies.id', '=', 'membership.academy_id')
            ->where('membership.user_id', $student->id)
            ->where('membership.academy_id', $classroom->academy_id)
            ->where('membership.role', 'student')
            ->where('membership.status', 'active')
            ->where('academies.status', 'active')
            ->exists();

        abort_unless($hasActiveRoster && $hasActiveMembership, 404);

        return $student;
    }

    private function activeParentForStudent(Classroom $classroom, User $student, int $parentId): User
    {
        $parent = $student->parents()->whereKey($parentId)->where('users.status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'parent'))
            ->firstOrFail();

        $hasActiveMembership = DB::table('academy_user as membership')
            ->join('academies', 'academies.id', '=', 'membership.academy_id')
            ->where('membership.user_id', $parent->id)
            ->where('membership.academy_id', $classroom->academy_id)
            ->where('membership.role', 'parent')
            ->where('membership.status', 'active')
            ->where('academies.status', 'active')
            ->exists();

        abort_unless($hasActiveMembership, 404);

        return $parent;
    }

}
