<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademyMeeting;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OwnerMeetingService
{
    public function index(User $owner, Academy $academy): array
    {
        $this->authorizeAcademy($owner, $academy);

        $targets = DB::table('classroom_student as classroom_membership')
            ->join('classrooms', 'classrooms.id', '=', 'classroom_membership.classroom_id')
            ->join('users as students', 'students.id', '=', 'classroom_membership.student_id')
            ->join('academy_user as student_membership', function ($join) use ($academy): void {
                $join->on('student_membership.user_id', '=', 'students.id')
                    ->where('student_membership.academy_id', '=', $academy->id)
                    ->where('student_membership.role', '=', 'student')
                    ->where('student_membership.status', '=', 'active');
            })
            ->join('parent_student as parent_link', 'parent_link.student_id', '=', 'students.id')
            ->join('users as parents', 'parents.id', '=', 'parent_link.parent_id')
            ->where('classrooms.academy_id', $academy->id)
            ->where('classrooms.status', 'active')
            ->where('classroom_membership.status', 'active')
            ->where('students.status', 'active')
            ->whereNull('students.deleted_at')
            ->where('parents.status', 'active')
            ->whereNull('parents.deleted_at')
            ->orderBy('classrooms.title')
            ->orderBy('students.name')
            ->orderBy('parents.name')
            ->get([
                'classrooms.id as classroom_id',
                'classrooms.title as classroom_title',
                'students.id as student_id',
                'students.name as student_name',
                'parents.id as parent_id',
                'parents.name as parent_name',
            ]);

        $meetings = AcademyMeeting::query()
            ->where('academy_id', $academy->id)
            ->with([
                'classroom:id,title',
                'course:id,title',
                'student:id,name',
                'guardian:id,name',
            ])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return compact('academy', 'targets', 'meetings');
    }

    public function create(User $owner, Academy $academy, array $data): AcademyMeeting
    {
        $this->authorizeAcademy($owner, $academy);

        $target = explode(':', (string) ($data['meeting_target'] ?? ''));
        if (count($target) !== 3 || count(array_filter($target, static fn ($part) => ctype_digit($part))) !== 3) {
            throw ValidationException::withMessages([
                'meeting_target' => 'کلاس، دانش‌آموز و ولی انتخاب‌شده معتبر نیستند.',
            ]);
        }

        [$classroomId, $studentId, $parentId] = array_map('intval', $target);

        $classroom = Classroom::query()
            ->where('academy_id', $academy->id)
            ->where('status', 'active')
            ->find($classroomId);

        if (!$classroom) {
            throw ValidationException::withMessages([
                'meeting_target' => 'کلاس انتخاب‌شده دیگر فعال نیست یا متعلق به این آموزشگاه نیست.',
            ]);
        }

        $student = $academy->students()
            ->whereKey($studentId)
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->first();

        if (!$student || !$classroom->students()
            ->whereKey($studentId)
            ->wherePivot('status', 'active')
            ->exists()) {
            throw ValidationException::withMessages([
                'meeting_target' => 'دانش‌آموز انتخاب‌شده عضو فعال این کلاس نیست.',
            ]);
        }

        $linkedParent = $student->parents()
            ->whereKey($parentId)
            ->where('users.status', 'active')
            ->exists();

        if (!$linkedParent) {
            throw ValidationException::withMessages([
                'meeting_target' => 'ولی انتخاب‌شده به حساب همین دانش‌آموز متصل نیست.',
            ]);
        }

        return AcademyMeeting::query()->create([
            'academy_id' => $academy->id,
            'classroom_id' => $classroom->id,
            'course_id' => $classroom->course_id,
            'student_id' => $student->id,
            'parent_id' => $parentId,
            'created_by' => $owner->id,
            'title' => trim($data['title']),
            'message' => filled($data['message'] ?? null) ? trim($data['message']) : null,
            'scheduled_at' => $data['scheduled_at'],
            'scheduled_end_at' => $data['scheduled_end_at'] ?? null,
            'status' => 'scheduled',
        ]);
    }

    public function changeStatus(User $owner, Academy $academy, AcademyMeeting $meeting, string $status): void
    {
        $this->authorizeAcademy($owner, $academy);
        abort_unless((int) $meeting->academy_id === (int) $academy->id, 404);

        if (!in_array($status, ['scheduled', 'completed', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'status' => 'وضعیت انتخاب‌شده معتبر نیست.',
            ]);
        }

        $meeting->update(['status' => $status]);
    }

    private function authorizeAcademy(User $owner, Academy $academy): void
    {
        abort_unless(
            $owner->hasRole('academy-owner') && (int) $academy->owner_id === (int) $owner->id,
            403
        );
    }
}
