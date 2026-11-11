<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_primary_owner_panel_sections_do_not_return_server_errors(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();

        $screens = [
            ['owner.dashboard', []],
            ['owner.courses.index', []],
            ['owner.reports.index', []],
            ['owner.finance.index', []],
            ['owner.legal-documents.index', []],
            ['owner.content-rights.index', []],
            ['owner.spotplayer.edit', []],
            ['owner.academy.edit', [$academy]],
            ['owner.protected-file-views.index', [$academy]],
            ['owner.form-options', [$academy]],
            ['owner.people.index', [$academy]],
            ['owner.attendance.index', [$academy]],
            ['owner.meetings.index', [$academy]],
            ['owner.schedules.index', [$academy]],
            ['owner.assignments.index', [$academy]],
            ['owner.classrooms.index', [$academy]],
            ['owner.communications.index', [$academy]],
            ['owner.messages.index', [$academy]],
            ['owner.counseling.index', [$academy]],
            ['owner.exams.index', [$academy]],
            ['owner.recordings.index', [$academy]],
            ['owner.teacher-payments.index', [$academy]],
            ['owner.resources.index', [$academy]],
            ['owner.products.index', [$academy]],
            ['owner.orders.index', [$academy]],
        ];

        foreach ($screens as [$routeName, $parameters]) {
            $response = $this->actingAs($owner)->get(route($routeName, $parameters));

            $this->assertNotSame(
                500,
                $response->getStatusCode(),
                "Owner panel screen [{$routeName}] returned HTTP 500."
            );
        }
    }
}
