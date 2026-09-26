<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OwnerClassroomService
{
    public function canManageAcademy(User $owner, Academy $academy): bool
    {
        return $owner->hasRole('academy-owner')
            && (int) $academy->owner_id === (int) $owner->id;
    }

    public function index(User $owner, Academy $academy): Collection
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        return $academy->classrooms()
            ->with([
                'course:id,title',
                'grade:id,title',
                'academicYear:id,title,is_current',
                'teachers:id,name',
                'schedules:id,classroom_id,weekday,start_time,end_time,room,meeting_url',
            ])
            ->withCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active'),
            ])
            ->latest()
            ->get()
            ->each(function (Classroom $classroom): void {
                $classroom->setAttribute(
                    'capacity_remaining',
                    $classroom->capacity === null
                        ? null
                        : max(0, (int) $classroom->capacity - (int) $classroom->active_students_count)
                );

                $classroom->setAttribute(
                    'occupancy_percent',
                    $classroom->capacity && $classroom->capacity > 0
                        ? min(100, round(($classroom->active_students_count / $classroom->capacity) * 100, 1))
                        : null
                );

                $classroom->setAttribute('execution_status', $this->executionStatus($classroom));
            });
    }

    public function formData(User $owner, Academy $academy): array
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        return [
            'courses' => $academy->courses()
                ->with([
                    'teachers:id,name',
                ])
                ->select('id', 'academy_id', 'title', 'status')
                ->orderBy('title')
                ->get(),
            'teachers' => $academy->users()
                ->wherePivot('role', 'teacher')
                ->wherePivot('status', 'active')
                ->orderBy('users.name')
                ->get(['users.id', 'users.name']),
            'grades' => DB::table('academic_grades')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(['id', 'title']),
            'academicYears' => DB::table('academic_years')
                ->orderByDesc('is_current')
                ->orderByDesc('start_date')
                ->get(['id', 'title', 'is_current']),
        ];
    }

    public function create(User $owner, Academy $academy, array $data): Classroom
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        return DB::transaction(function () use ($academy, $data): Classroom {
            $course = $academy->courses()->findOrFail((int) $data['course_id']);
            $teacherIds = $this->validatedTeacherIds($academy, $course, $data['teacher_ids']);

            $classroom = $academy->classrooms()->create([
                'course_id' => $course->id,
                'grade_id' => $data['grade_id'] ?? null,
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'title' => $data['title'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'status' => $data['status'] ?? 'active',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            $classroom->teachers()->sync($teacherIds);

            return $classroom->load(['course:id,title', 'teachers:id,name']);
        });
    }

    public function update(User $owner, Academy $academy, Classroom $classroom, array $data): Classroom
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        if ((int) $classroom->academy_id !== (int) $academy->id) {
            abort(404);
        }

        return DB::transaction(function () use ($academy, $classroom, $data): Classroom {
            $classroom = Classroom::query()
                ->whereKey($classroom->id)
                ->where('academy_id', $academy->id)
                ->lockForUpdate()
                ->firstOrFail();

            $classroom->loadCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active'),
            ]);

            $course = $academy->courses()->findOrFail((int) $data['course_id']);
            $teacherIds = $this->validatedTeacherIds($academy, $course, $data['teacher_ids']);

            if (
                $data['capacity'] !== null
                && (int) $data['capacity'] < (int) $classroom->active_students_count
            ) {
                throw ValidationException::withMessages([
                    'capacity' => 'ظرفیت جدید نمی‌تواند کمتر از تعداد دانش‌آموزان فعال کلاس باشد.',
                ]);
            }

            $classroom->update([
                'course_id' => $course->id,
                'grade_id' => $data['grade_id'] ?? null,
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'title' => $data['title'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'status' => $data['status'] ?? 'active',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            $classroom->teachers()->sync($teacherIds);

            return $classroom->fresh(['course:id,title', 'grade:id,title', 'academicYear:id,title', 'teachers:id,name']);
        });
    }

    public function show(User $owner, Academy $academy, Classroom $classroom): array
    {
        abort_unless($this->canManageAcademy($owner, $academy), 403);

        if ((int) $classroom->academy_id !== (int) $academy->id) {
            abort(404);
        }

        $classroom->load([
            'course:id,title,status',
            'grade:id,title',
            'academicYear:id,title,is_current',
            'teachers:id,name',
            'students' => fn ($query) => $query
                ->wherePivot('status', 'active')
                ->select('users.id', 'users.name', 'users.email'),
            'schedules:id,classroom_id,weekday,start_time,end_time,room,meeting_url',
        ]);

        $activeStudentCount = $classroom->students->count();

        $attendance = DB::table('attendances')
            ->where('classroom_id', $classroom->id)
            ->selectRaw("COUNT(*) AS total, SUM(CASE WHEN status IN ('present','late') THEN 1 ELSE 0 END) AS attended")
            ->first();

        $classroom->setAttribute('active_students_count', $activeStudentCount);
        $classroom->setAttribute(
            'capacity_remaining',
            $classroom->capacity === null
                ? null
                : max(0, (int) $classroom->capacity - $activeStudentCount)
        );
        $classroom->setAttribute(
            'occupancy_percent',
            $classroom->capacity && $classroom->capacity > 0
                ? min(100, round(($activeStudentCount / $classroom->capacity) * 100, 1))
                : null
        );
        $classroom->setAttribute('execution_status', $this->executionStatus($classroom));

        $now = now();

        $liveClasses = DB::table('live_classes')
            ->where('classroom_id', $classroom->id)
            ->orderByDesc('scheduled_at')
            ->limit(8)
            ->get();

        $liveCandidates = DB::table('live_classes')
            ->where('classroom_id', $classroom->id)
            ->whereIn('status', ['scheduled', 'live'])
            ->where('scheduled_at', '<=', $now)
            ->orderByDesc('scheduled_at')
            ->get();

        $assignments = DB::table('assignments')
            ->where('classroom_id', $classroom->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id','title','status','due_at','created_at']);

        $exams = DB::table('exams')
            ->where('classroom_id', $classroom->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id','title','status','starts_at','ends_at','created_at']);

        $enrollments = DB::table('course_enrollments')
            ->where('classroom_id', $classroom->id)
            ->join('users', 'users.id', '=', 'course_enrollments.student_id')
            ->orderByDesc('course_enrollments.created_at')
            ->limit(8)
            ->get([
                'course_enrollments.id',
                'users.name as student_name',
                'course_enrollments.status',
                'course_enrollments.created_at',
            ]);

        $attendanceDays = DB::table('attendances')
            ->where('classroom_id', $classroom->id)
            ->groupBy('attendance_date')
            ->orderByDesc('attendance_date')
            ->limit(6)
            ->get([
                'attendance_date',
                DB::raw('COUNT(*) AS total'),
                DB::raw("SUM(CASE WHEN status IN ('present','late') THEN 1 ELSE 0 END) AS attended"),
            ]);

        $activities = collect();

        foreach ($assignments as $item) {
            $activities->push([
                'type' => 'assignment',
                'title' => 'تکلیف جدید',
                'description' => $item->title,
                'timestamp' => Carbon::parse($item->created_at),
            ]);
        }

        foreach ($exams as $item) {
            $activities->push([
                'type' => 'exam',
                'title' => 'آزمون',
                'description' => $item->title,
                'timestamp' => Carbon::parse($item->created_at),
            ]);
        }

        foreach ($liveClasses as $item) {
            $scheduledAt = Carbon::parse($item->scheduled_at);
            $statusTitle = match ($item->status) {
                'live' => 'کلاس آنلاین در حال برگزاری',
                'scheduled' => 'کلاس آنلاین برنامه‌ریزی شد',
                'ended' => 'کلاس آنلاین پایان یافت',
                'cancelled' => 'کلاس آنلاین لغو شد',
                default => 'کلاس آنلاین',
            };

            $activities->push([
                'type' => 'live',
                'title' => $statusTitle,
                'description' => $item->title,
                'timestamp' => $scheduledAt,
            ]);
        }

        foreach ($enrollments as $item) {
            $activities->push([
                'type' => 'enrollment',
                'title' => 'ثبت‌نام دانش‌آموز',
                'description' => $item->student_name,
                'timestamp' => Carbon::parse($item->created_at),
            ]);
        }

        foreach ($attendanceDays as $item) {
            $activities->push([
                'type' => 'attendance',
                'title' => 'حضور و غیاب ثبت شد',
                'description' => $item->attended . ' از ' . $item->total . ' حضور ثبت‌شده',
                'timestamp' => Carbon::parse($item->attendance_date)->startOfDay(),
            ]);
        }

        $activities = $activities
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();

        $todayAttendance = DB::table('attendances')
            ->where('classroom_id', $classroom->id)
            ->whereDate('attendance_date', today())
            ->selectRaw("COUNT(*) AS total, SUM(CASE WHEN status IN ('present','late') THEN 1 ELSE 0 END) AS attended")
            ->first();

        return [
            'classroom' => $classroom,
            'attendance' => [
                'total' => (int) ($attendance->total ?? 0),
                'attended' => (int) ($attendance->attended ?? 0),
                'rate' => ($attendance?->total ?? 0) > 0
                    ? round(((int) $attendance->attended / (int) $attendance->total) * 100, 1)
                    : null,
            ],
            'todayAttendance' => [
                'total' => (int) ($todayAttendance->total ?? 0),
                'attended' => (int) ($todayAttendance->attended ?? 0),
                'rate' => ($todayAttendance?->total ?? 0) > 0
                    ? round(((int) $todayAttendance->attended / (int) $todayAttendance->total) * 100, 1)
                    : null,
            ],
            'activities' => $activities,
            'liveNow' => $liveCandidates->first(function ($item) use ($now) {
                $start = Carbon::parse($item->scheduled_at);
                $end = $start->copy()->addMinutes((int) ($item->duration_minutes ?? 60));

                return in_array($item->status, ['live','scheduled'], true)
                    && $start->lte($now)
                    && $end->gte($now);
            }),
            'upcomingLiveClasses' => DB::table('live_classes')
                ->where('classroom_id', $classroom->id)
                ->whereIn('status', ['scheduled', 'live'])
                ->where('scheduled_at', '>=', $now)
                ->orderBy('scheduled_at')
                ->limit(4)
                ->get(),
            'recentAssignments' => $assignments->take(5),
            'recentExams' => $exams->take(5),
        ];
    }

    private function validatedTeacherIds(Academy $academy, Course $course, array $teacherIds): array
    {
        $teacherIds = array_values(array_unique(array_map('intval', $teacherIds)));

        $academyTeacherIds = $academy->users()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->whereIn('users.id', $teacherIds)
            ->pluck('users.id');

        if ($academyTeacherIds->count() !== count($teacherIds)) {
            throw ValidationException::withMessages([
                'teacher_ids' => 'همه مدرس‌های انتخاب‌شده عضو فعال این آموزشگاه نیستند.',
            ]);
        }

        $courseTeacherIds = $course->teachers()
            ->whereIn('users.id', $teacherIds)
            ->pluck('users.id');

        if ($courseTeacherIds->count() !== count($teacherIds)) {
            throw ValidationException::withMessages([
                'teacher_ids' => 'همه مدرس‌های انتخاب‌شده به این دوره اختصاص داده نشده‌اند.',
            ]);
        }

        return $teacherIds;
    }

    private function executionStatus(Classroom $classroom): string
    {
        if ($classroom->status === 'archived') {
            return 'archived';
        }

        $now = now();

        if ($classroom->starts_at && $classroom->ends_at) {
            if ($classroom->starts_at->lte($now) && $classroom->ends_at->gte($now)) {
                return 'running';
            }

            if ($classroom->starts_at->gt($now)) {
                return 'scheduled';
            }

            if ($classroom->ends_at->lt($now)) {
                return 'finished';
            }
        }

        return 'active';
    }
}
