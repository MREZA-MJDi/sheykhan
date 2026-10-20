<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\ClassSchedule;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OwnerScheduleService
{
    public function index(User $owner, Academy $academy): array
    {
        $this->authorizeAcademy($owner, $academy);

        $classrooms = $academy->classrooms()
            ->where('status', 'active')
            ->with([
                'course:id,title',
                'schedules' => fn ($query) => $query
                    ->orderBy('weekday')
                    ->orderBy('start_time'),
            ])
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return compact('academy', 'classrooms');
    }

    public function create(
        User $owner,
        Academy $academy,
        Classroom $classroom,
        array $data,
        MeetingLinkService $links
    ): ClassSchedule {
        $this->authorizeAcademy($owner, $academy);
        abort_unless((int) $classroom->academy_id === (int) $academy->id, 404);

        if ($classroom->status !== 'active') {
            throw ValidationException::withMessages([
                'classroom_id' => 'فقط برای کلاس فعال می‌توان زمان آنلاین تعریف کرد.',
            ]);
        }

        $conflict = $classroom->schedules()
            ->where('weekday', (int) $data['weekday'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => 'این بازه با یک زمان ثبت‌شدهٔ دیگر برای همین کلاس تداخل دارد.',
            ]);
        }

        $meetingUrl = $links->resolve(
            (string) $data['provider'],
            $data['meeting_url'] ?? null,
        );

        return DB::transaction(fn (): ClassSchedule => $classroom->schedules()->create([
            'weekday' => (int) $data['weekday'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'room' => filled($data['room'] ?? null) ? trim($data['room']) : null,
            'provider' => $data['provider'],
            'meeting_url' => $meetingUrl,
        ]), 3);
    }

    public function delete(
        User $owner,
        Academy $academy,
        Classroom $classroom,
        ClassSchedule $schedule
    ): void {
        $this->authorizeAcademy($owner, $academy);
        abort_unless(
            (int) $classroom->academy_id === (int) $academy->id
                && (int) $schedule->classroom_id === (int) $classroom->id,
            404
        );

        $schedule->delete();
    }

    private function authorizeAcademy(User $owner, Academy $academy): void
    {
        abort_unless(
            $owner->hasRole('academy-owner') && (int) $academy->owner_id === (int) $owner->id,
            403
        );
    }
}
