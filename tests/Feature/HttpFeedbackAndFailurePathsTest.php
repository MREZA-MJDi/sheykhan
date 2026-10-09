<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HttpFeedbackAndFailurePathsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_before_entering_owner_portal(): void
    {
        $this->get(route('owner.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', route('owner.dashboard'));
    }

    public function test_authenticated_user_without_owner_role_gets_forbidden_response(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertForbidden()
            ->assertSee('403')
            ->assertSee('شما اجازه دسترسی به این بخش را ندارید.');

        $this->actingAs($user)
            ->getJson(route('owner.dashboard'))
            ->assertForbidden()
            ->assertJson([
                'message' => 'شما اجازه دسترسی به این بخش را ندارید.',
            ]);
    }

    public function test_unknown_html_route_uses_branded_not_found_view(): void
    {
        $this->get('/this-route-does-not-exist')
            ->assertNotFound()
            ->assertSee('صفحه یا رکورد موردنظر پیدا نشد.')
            ->assertSee('404')
            ->assertSee(route('home'), false);
    }

    public function test_unknown_json_route_returns_a_safe_localized_not_found_message(): void
    {
        $this->getJson('/this-route-does-not-exist')
            ->assertNotFound()
            ->assertJson([
                'message' => 'صفحه یا رکورد موردنظر پیدا نشد.',
            ]);
    }

    public function test_success_flash_is_rendered_as_an_accessible_status_alert(): void
    {
        $this->withSession([
            'success' => 'عملیات با موفقیت انجام شد.',
        ])->get(route('home'))
            ->assertOk()
            ->assertSee('عملیات با موفقیت انجام شد.')
            ->assertSee('role="status"', false)
            ->assertSee('data-ui-flash-close', false);
    }

    public function test_login_validation_errors_redirect_back_and_are_announced_accessibly(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'identifier' => '',
                'password' => '',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['identifier', 'password']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ایمیل یا شماره موبایل را وارد کنید.')
            ->assertSee('رمز عبور را وارد کنید.')
            ->assertSee('role="alert"', false);
    }

    public function test_invalid_credentials_return_to_login_with_a_visible_error(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'identifier' => 'missing-user@example.test',
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('identifier');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('اطلاعات ورود صحیح نیست یا این حساب فعال نیست.')
            ->assertSee('role="alert"', false);
    }
    public function test_registration_validation_redirects_back_and_displays_accessible_errors(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('نام و نام خانوادگی را وارد کنید.')
            ->assertSee('ایمیل را وارد کنید.')
            ->assertSee('role="alert"', false);
    }

    public function test_logout_redirects_home_and_shows_success_feedback(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'با موفقیت از حساب کاربری خارج شدید.');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('با موفقیت از حساب کاربری خارج شدید.')
            ->assertSee('role="status"', false);
    }

    public function test_enrollment_migration_installs_the_effective_unique_key(): void
    {
        $this->assertTrue(Schema::hasColumn('course_enrollments', 'academic_year_key'));

        $index = collect(Schema::getIndexes('course_enrollments'))
            ->firstWhere('name', 'course_enrollments_course_student_year_effective_unique');

        $this->assertNotNull($index);
        $this->assertTrue($index['unique']);
    }

}
