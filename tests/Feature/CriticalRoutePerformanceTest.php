<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriticalRoutePerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_response_exposes_server_timing_for_server_side_measurement(): void
    {
        $this->seed();

        $response = $this->post(route('register.store'), [
            'name' => 'Performance Probe Student',
            'email' => 'performance-probe@example.test',
            'password' => 'TrustedPassword#2026',
            'password_confirmation' => 'TrustedPassword#2026',
            'account_type' => 'student',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $response->assertHeader('Server-Timing');

        $this->assertMatchesRegularExpression(
            '/^app;dur=\d+(?:\.\d+)?$/',
            (string) $response->headers->get('Server-Timing')
        );
    }

    public function test_noncritical_routes_do_not_receive_internal_timing_headers(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
        $response->assertHeaderMissing('Server-Timing');
    }
}
