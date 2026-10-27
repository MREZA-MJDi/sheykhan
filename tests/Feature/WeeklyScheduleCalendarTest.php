<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Academy;
use App\Models\ClassSchedule;
use App\Support\PersianUi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyScheduleCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_weekly_schedule_shows_jalali_calendar_and_explains_online_link_setup(): void
    {
        $this->seed();
        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = $teacher->classroomsAsTeacher()
            ->where('classrooms.status', 'active')
            ->firstOrFail();

        // Include an imported/migrated slot with a blank provider URL so the red state is covered.
        ClassSchedule::query()->create([
            'classroom_id' => $classroom->id,
            'weekday' => 5,
            'start_time' => '15:00',
            'end_time' => '16:00',
            'room' => 'کلاس تست بدون لینک',
            'provider' => 'Jitsi',
            'meeting_url' => null,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.schedule.index'))
            ->assertOk()
            ->assertSee('data-weekly-calendar', false)
            ->assertSee('تقویم هفتگی', false)
            ->assertSee('ساعت تهران (Asia/Tehran)', false)
            ->assertSee(PersianUi::calendar(now('Asia/Tehran'))['month_label'], false)
            ->assertSee('لینک آماده', false)
            ->assertSee('لینک نیازمند بررسی', false)
            ->assertSee('Jitsi', false)
            ->assertSee('Google Meet', false)
            ->assertSee('روز و ساعت را مشخص کن', false)
            ->assertSee('سرویس و لینک ورود را وصل کن', false)
            ->assertSee('lang="fa-IR"', false);
    }


    public function test_owner_weekly_schedule_page_uses_the_same_jalali_calendar(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('owner.schedules.index', $academy))
            ->assertOk()
            ->assertSee('owner-online-links-page', false)
            ->assertSee('owner-page-intro', false)
            ->assertSee('owner-info-banner', false)
            ->assertSee('owner-calendar-section', false)
            ->assertSee('owner-schedule-list', false)
            ->assertSee('owner-schedule-form', false)
            ->assertSee('data-weekly-calendar', false)
            ->assertSee('برنامهٔ کلاس‌ها در یک نگاه', false)
            ->assertSee('برنامه هفتگی', false)
            ->assertSee('لینک Jitsi', false)
            ->assertSee('ساعت تهران (Asia/Tehran)', false)
            ->assertSee('Google Meet', false)
            ->assertSee('Zoom', false)
            ->assertSee('ذخیره زمان و لینک', false)
            ->assertSee('۲. سرویس و لینک ورود را وصل کن', false);
    }

}
