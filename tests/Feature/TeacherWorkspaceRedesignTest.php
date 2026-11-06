<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherWorkspaceRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_home_uses_the_new_action_first_information_hierarchy(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('فضای کاری مدرس')
            ->assertSee('میانبر کارهای روزانه')
            ->assertSee('برنامه امروز')
            ->assertSee('کلاس‌های من')
            ->assertSee('کلاس‌های آنلاین پیش‌رو')
            ->assertSee('teacher-home__metrics', false);
    }

    public function test_teacher_sidebar_uses_clear_grouped_labels_instead_of_duplicate_message_links(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('نمای کلی')
            ->assertSee('کلاس و آموزش')
            ->assertSee('مدیریت یادگیری')
            ->assertSee('ارتباط و پیگیری')
            ->assertSee('کلاس‌های من')
            ->assertSee('کلاس آنلاین');
    }
}
