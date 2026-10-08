<?php

namespace Tests\\Feature;

use App\\Models\\User;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class HttpFeedbackAndFailurePathsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_before_entering_owner_portal(): void
    {
        $this->get(route('owner.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_owner_role_gets_forbidden_response(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertForbidden();
    }

    public function test_unknown_html_route_uses_branded_not_found_view(): void
    {
        $this->get('/this-route-does-not-exist')
            ->assertNotFound()
            ->assertSee('صفحه یا رکورد موردنظر پیدا نشد.')
            ->assertSee('404');
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

    public function test_validation_errors_are_rendered_as_an_accessible_alert(): void
    {
        $this->withSession([
            '_errors' => (new \\Illuminate\\Support\\ViewErrorBag())->put(
                'default',
                new \\Illuminate\\Support\\MessageBag(['identifier' => ['شناسه ورود الزامی است.']])
            ),
        ])->get(route('login'))
            ->assertOk()
            ->assertSee('شناسه ورود الزامی است.')
            ->assertSee('role="alert"', false);
    }
}
