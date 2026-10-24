<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyScheduleCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_weekly_schedule_shows_jalali_calendar_and_explains_online_link_setup(): void
    {
        $this->seed();
        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.schedule.index'))
            ->assertOk()
            ->assertSee('data-weekly-calendar', false)
            ->assertSee('تقویم هفتگی', false)
            ->assertSee('ساعت تهران (Asia/Tehran)', false)
            ->assertSee('زمستان', false)
            ->assertSee('لینک آماده', false)
            ->assertSee('لینک نیازمند بررسی', false)
            ->assertSee('Jitsi', false)
            ->assertSee('Google Meet', false)
            ->assertSee('روز و ساعت را مشخص کن', false)
            ->assertSee('سرویس و لینک ورود را وصل کن', false)
            ->assertSee('lang="fa-IR"', false);
    }
}
